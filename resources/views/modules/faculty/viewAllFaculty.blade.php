{{-- resources/views/landing/faculty-members.blade.php --}}

<style>

  .fmx-wrap{
    --fmx-brand: var(--primary-color, #9E363A);
    --fmx-ink: #0f172a;
    --fmx-muted: #64748b;
    --fmx-card: var(--surface, #ffffff);
    --fmx-line: var(--line-soft, rgba(15,23,42,.10));
    --fmx-shadow: 0 10px 24px rgba(2,6,23,.08);

    /* used for skeleton only */
    --fmx-card-h: 250px;max-width: 1320px;margin: 18px auto 54px;padding: 0 12px;background: transparent;position: relative;overflow: visible;
  }

  .fmx-head{background: var(--fmx-card);border: 1px solid var(--fmx-line);border-radius: 16px;box-shadow: var(--fmx-shadow);padding: 14px 16px;margin-bottom: 16px;display:flex;gap: 12px;align-items: center;justify-content: space-between;}
  .fmx-title{margin: 0;font-weight: 950;letter-spacing: .2px;color: var(--fmx-ink);font-size: 28px;display:flex;align-items:center;gap: 10px;white-space: nowrap;}
  .fmx-title i{ color: var(--fmx-brand); }
  .fmx-sub{margin: 6px 0 0;color: var(--fmx-muted);font-size: 14px;}

  .fmx-tools{display:flex;gap: 10px;align-items:center;flex-wrap: nowrap;}

  .fmx-search{position: relative;min-width: 260px;max-width: 520px;flex: 1 1 320px;}
  .fmx-search i{position:absolute;left: 14px;top: 50%;transform: translateY(-50%);opacity: .65;color: var(--fmx-muted);pointer-events:none;}
  .fmx-search input{width:100%;height: 42px;border-radius: 999px;padding: 11px 12px 11px 42px;border: 1px solid var(--fmx-line);background: var(--fmx-card);color: var(--fmx-ink);outline: none;}
  .fmx-search input:focus{border-color: rgba(201,75,80,.55);box-shadow: 0 0 0 4px rgba(201,75,80,.18);}

  .fmx-select{position: relative;min-width: 260px;max-width: 360px;flex: 0 1 320px;}
  .fmx-select__icon{position:absolute;left: 14px;top: 50%;transform: translateY(-50%);opacity: .70;color: var(--fmx-muted);pointer-events:none;font-size: 14px;}
  .fmx-select__caret{position:absolute;right: 14px;top: 50%;transform: translateY(-50%);opacity: .70;color: var(--fmx-muted);pointer-events:none;font-size: 12px;}
  .fmx-select select{width: 100%;height: 42px;border-radius: 999px;padding: 10px 38px 10px 42px;border: 1px solid var(--fmx-line);background: var(--fmx-card);color: var(--fmx-ink);outline: none;appearance: none;-webkit-appearance: none;-moz-appearance: none;}
  .fmx-select select:focus{border-color: rgba(201,75,80,.55);box-shadow: 0 0 0 4px rgba(201,75,80,.18);}

  /* ✅ Screenshot UI: single-column rows */
  .fmx-grid,
  .fmx-skeleton{max-width: 1040px;margin: 0 auto;}
  .fmx-grid{display:flex;flex-direction:column;gap: 18px;align-items: stretch;}

  .fmx-card{width:100%;position:relative;display:flex;flex-direction:column;border: 1px solid rgba(2,6,23,.08);border-radius: 16px;background: #fff;box-shadow: var(--fmx-shadow);overflow:hidden;transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;will-change: transform;cursor: pointer;outline: none;}
  .fmx-card:hover{transform: translateY(-2px);box-shadow: 0 16px 34px rgba(2,6,23,.12);border-color: rgba(158,54,58,.22);}
  .fmx-card:focus-visible{box-shadow: 0 0 0 4px rgba(201,75,80,.18), 0 16px 34px rgba(2,6,23,.12);border-color: rgba(201,75,80,.55);}

  .fmx-body{padding: 16px 16px 14px;display:flex;flex-direction:column;}

  .fmx-top{ display:flex; gap: 12px; align-items:flex-start; }

  .fmx-avatar{width: 64px;height: 64px;border-radius: 999px;flex: 0 0 64px;overflow:hidden;border: 3px solid #fff;box-shadow: 0 10px 22px rgba(2,6,23,.12);background: radial-gradient(140px 140px at 30% 20%, rgba(201,75,80,.16), transparent 60%), linear-gradient(180deg, rgba(0,0,0,.03), rgba(0,0,0,.06));position: relative;display:grid;place-items:center;}
  .fmx-avatar img{ width:100%; height:100%; object-fit: cover; display:block; }
  .fmx-initial{position:absolute; inset:0;display:grid; place-items:center;font-weight: 950;color: rgba(158,54,58,.95);font-size: 18px;letter-spacing:.5px;}
  .fmx-avatar.has-img .fmx-initial{ opacity:0; pointer-events:none; }

  .fmx-name{margin: 0;font-weight: 950;color: var(--fmx-ink);font-size: 18px;line-height: 1.25;text-transform: uppercase;display:-webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow:hidden;overflow-wrap:anywhere;word-break:break-word;}
  .fmx-desig{margin-top: 6px;color: #334155;font-size: 14px;font-weight: 800;display:-webkit-box;-webkit-line-clamp: 1;-webkit-box-orient: vertical;overflow:hidden;}

  .fmx-meta{ margin-top: 12px; display:grid; gap: 6px; }
  .fmx-line{ font-size: 14px; color: #334155; line-height: 1.55; overflow-wrap:anywhere; }
  .fmx-line b{ font-weight: 950; color: var(--fmx-ink); }

  .fmx-links{margin-top: 12px;display:flex;flex-direction:column;gap: 6px;font-size: 14px;}
  .fmx-links a{color: #1d4ed8;text-decoration: none;font-weight: 900;word-break: break-word;}
  .fmx-links a:hover{ text-decoration: underline; }

  .fmx-social{margin-top: 12px;display:flex;gap: 10px;flex-wrap: wrap;}
  .fmx-social a{width: 42px;height: 42px;border-radius: 999px;display:grid;place-items:center;background: var(--fmx-brand);color:#fff;border: 1px solid rgba(255,255,255,.18);box-shadow: 0 12px 22px rgba(143,47,47,.18);transition: transform .14s ease, filter .14s ease;text-decoration:none;}
  .fmx-social a:hover{ transform: translateY(-1px); filter: brightness(1.06); }
  .fmx-social a i{ color:#fff; font-size: 16px; line-height: 1; }

  .fmx-state{max-width: 1040px;margin: 0 auto;background: var(--fmx-card);border: 1px solid var(--fmx-line);border-radius: 16px;box-shadow: var(--fmx-shadow);padding: 18px;color: var(--fmx-muted);text-align:center;}

  /* ✅ Professional empty-state illustration */
  .fmx-empty-ill{
    width: 170px;
    max-width: 100%;
    margin: 0 auto 10px;
    display: block;
    color: var(--fmx-brand);
  }
  .fmx-empty-ill svg{
    display:block;
    width:100%;
    height:auto;
  }

  .fmx-skeleton{display:flex;flex-direction:column;gap: 18px;}
  .fmx-sk{border-radius: 16px;border: 1px solid var(--fmx-line);background: #fff;overflow:hidden;position:relative;box-shadow: 0 10px 24px rgba(2,6,23,.08);height: var(--fmx-card-h);}
  .fmx-sk:before{content:'';position:absolute; inset:0;transform: translateX(-60%);background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);animation: fmxSkMove 1.15s ease-in-out infinite;}
  @keyframes fmxSkMove{ to{ transform: translateX(60%);} }

  .fmx-pagination{display:flex;justify-content:center;margin-top: 18px;}
  .fmx-pagination .fmx-pager{display:flex;gap: 8px;flex-wrap: wrap;align-items:center;justify-content:center;padding: 10px;}
  .fmx-pagebtn{border:1px solid var(--fmx-line);background: var(--fmx-card);color: var(--fmx-ink);border-radius: 12px;padding: 9px 12px;font-size: 13px;font-weight: 950;box-shadow: 0 8px 18px rgba(2,6,23,.06);cursor:pointer;user-select:none;}
  .fmx-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .fmx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .fmx-pagebtn.active{background: rgba(201,75,80,.12);border-color: rgba(201,75,80,.35);color: var(--fmx-brand);}

  @media (max-width: 640px){
    .fmx-head{ flex-wrap: wrap; align-items: flex-end; }
    .fmx-tools{ flex-wrap: wrap; }
    .fmx-title{ font-size: 24px; white-space: normal; }
    .fmx-search{ min-width: 220px; flex: 1 1 240px; }
    .fmx-select{ min-width: 220px; flex: 1 1 240px; }
    .fmx-grid, .fmx-skeleton, .fmx-state{ max-width: 100%; }
    .fmx-empty-ill{ width: 146px; }
  }

  .dynamic-navbar .navbar-nav .dropdown-menu{position: absolute !important;inset: auto !important;}
  .dynamic-navbar .dropdown-menu.is-portaled{position: fixed !important;}
</style>

<div
  class="fmx-wrap"
  data-profile-base="{{ url('/user/profile') }}/"
  data-preview-index="{{ url('/api/public/faculty-preview-order') }}"
  data-preview-show-base="{{ url('/api/public/faculty-preview-order') }}/"
>
  <div class="fmx-head">
    <div>
      <h1 class="fmx-title"><i class="fa-solid fa-users"></i>Faculty Members</h1>
      <div class="fmx-sub" id="fmxSub">Showing faculty members from all departments.</div>
    </div>

    <div class="fmx-tools">
      <div class="fmx-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="fmxSearch" type="search" placeholder="Search faculty (name/designation/qualification)…">
      </div>

      <div class="fmx-select" title="Filter by department">
        <i class="fa-solid fa-building-columns fmx-select__icon"></i>
        <select id="fmxDept" aria-label="Filter by department">
          <option value="__all">All Departments</option>
        </select>
        <i class="fa-solid fa-chevron-down fmx-select__caret"></i>
      </div>
    </div>
  </div>

  <div id="fmxGrid" class="fmx-grid" style="display:none;"></div>
  <div id="fmxSkeleton" class="fmx-skeleton" style="display:none;"></div>
  <div id="fmxState" class="fmx-state"></div>

  <div class="fmx-pagination">
    <div id="fmxPager" class="fmx-pager" style="display:none;"></div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<script>
(() => {
  if (window.__PUBLIC_FACULTY_MEMBERS_DEPT__) return;
  window.__PUBLIC_FACULTY_MEMBERS_DEPT__ = true;

  const root = document.querySelector('.fmx-wrap');
  if (!root) return;

  const PROFILE_BASE = root.getAttribute('data-profile-base') || (window.location.origin + '/user/profile/');
  const PREVIEW_INDEX = root.getAttribute('data-preview-index') || (window.location.origin + '/api/public/faculty-preview-order');
  const PREVIEW_SHOW_BASE = root.getAttribute('data-preview-show-base') || (window.location.origin + '/api/public/faculty-preview-order/');
  const ALL_DEPTS = '__all';

  const $ = (id) => document.getElementById(id);

  const els = {
    grid: $('fmxGrid'),
    skel: $('fmxSkeleton'),
    state: $('fmxState'),
    pager: $('fmxPager'),
    search: $('fmxSearch'),
    dept: $('fmxDept'),
    sub: $('fmxSub'),
  };

  const state = {
    page: 1,
    perPage: 9,
    lastPage: 1,
    q: '',
    deptUuid: ALL_DEPTS,
    deptName: 'All Departments',
  };

  let activeController = null;

  // deptUuid -> {id,uuid,slug,title}
  let deptByUuid = new Map();

  // current assigned list (ordered, must match DB saved order)
  let assignedAll = [];

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function escAttr(str){
    return (str ?? '').toString().replace(/"/g, '&quot;');
  }
  function decodeMaybeJson(v){
    if (v == null) return null;
    if (Array.isArray(v) || typeof v === 'object') return v;
    try { return JSON.parse(String(v)); } catch(e){ return null; }
  }
  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }
  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  }
  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'FM';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
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
    return `<div class="fmx-line"><b>${esc(label)}:</b> <span>${esc(v)}</span></div>`;
  }

  // ✅ Professional empty-state illustration (SVG)
  function emptyStateIllustration(){
    return `
      <div class="fmx-empty-ill" aria-hidden="true">
        <svg viewBox="0 0 220 140" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="10" y="18" width="200" height="112" rx="16" fill="white" stroke="rgba(15,23,42,0.10)"/>
          <rect x="24" y="32" width="172" height="84" rx="12" fill="rgba(148,163,184,0.08)" stroke="rgba(148,163,184,0.18)"/>
          <circle cx="70" cy="66" r="16" fill="rgba(158,54,58,0.14)" stroke="currentColor" stroke-width="2"/>
          <path d="M49 97c5-11 16-16 21-16s16 5 21 16" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
          <rect x="100" y="52" width="72" height="8" rx="4" fill="rgba(100,116,139,0.20)"/>
          <rect x="100" y="68" width="54" height="8" rx="4" fill="rgba(100,116,139,0.16)"/>
          <rect x="100" y="84" width="64" height="8" rx="4" fill="rgba(100,116,139,0.12)"/>
          <circle cx="182" cy="26" r="12" fill="rgba(158,54,58,0.10)" stroke="currentColor" stroke-width="1.8"/>
          <path d="M177.5 26h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M182 21.5v9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
    `;
  }

  function iconForPlatform(platform){
    const p = (platform || '').toLowerCase().trim();
    if (p.includes('linkedin')) return 'fa-brands fa-linkedin-in';
    if (p.includes('google') || p.includes('scholar')) return 'fa-solid fa-graduation-cap';
    if (p.includes('university') || p.includes('profile') || p.includes('college')) return 'fa-solid fa-building-columns';
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
    if (i.startsWith('fa-') && !i.includes('fa-solid') && !i.includes('fa-brands') && !i.includes('fa-regular')) {
      return 'fa-brands ' + i;
    }
    return i;
  }

  // ✅ Social links: prefer socials[], else fallback to metadata keys (LinkedIn/Scholar/College/Facebook/Instagram/etc.)
  function buildSocialFromItem(it){
    const socials = Array.isArray(it?.socials) ? it.socials : [];
    const meta = decodeMaybeJson(it?.metadata) || {};

    let items = [];

    if (socials.length){
      items = socials.map(s => ({
        url: (s?.url || '').toString().trim(),
        icon: normalizeFaIcon(s?.icon) || iconForPlatform(s?.platform),
        title: (s?.platform || 'Link').toString(),
      }));
    } else {
      // fallback (common keys you might store in metadata)
      const pickUrl = (...keys) => {
        for (const k of keys){
          const v = meta?.[k] ?? it?.[k];
          const s = (v || '').toString().trim();
          if (s) return s;
        }
        return '';
      };

      const add = (url, title, icon) => {
        const u = (url || '').toString().trim();
        if (!u) return;
        items.push({ url: u, title, icon });
      };

      // screenshot-like set (only if present)
      add(pickUrl('linkedin','linkedin_url','linkedIn','linkedinLink'), 'LinkedIn', 'fa-brands fa-linkedin-in');
      add(pickUrl('google_scholar','scholar','scholar_url','google_scholar_url'), 'Google Scholar', 'fa-solid fa-graduation-cap');
      add(pickUrl('college_profile','university_profile','profile_url','msit_profile','institute_profile'), 'Profile', 'fa-solid fa-building-columns');
      add(pickUrl('facebook','facebook_url','fb','fb_url'), 'Facebook', 'fa-brands fa-facebook-f');
      add(pickUrl('instagram','instagram_url','insta','insta_url'), 'Instagram', 'fa-brands fa-instagram');

      // extra (only if present)
      add(pickUrl('twitter','x','twitter_url','x_url'), 'X', 'fa-brands fa-x-twitter');
      add(pickUrl('github','github_url'), 'GitHub', 'fa-brands fa-github');
      add(pickUrl('youtube','youtube_url'), 'YouTube', 'fa-brands fa-youtube');
      add(pickUrl('researchgate','researchgate_url'), 'ResearchGate', 'fa-brands fa-researchgate');
    }

    items = items
      .map(x => ({...x, url: normalizeUrl(x.url)}))
      .filter(x => x.url);

    if (!items.length) return '';

    const html = items.map(s => `
      <a href="${escAttr(s.url)}" target="_blank" rel="noopener"
         title="${escAttr(s.title)}" data-stop-card="1">
        <i class="${escAttr(s.icon)}"></i>
      </a>
    `).join('');

    return `<div class="fmx-social">${html}</div>`;
  }

  function bindAvatarImages(rootEl){
    rootEl.querySelectorAll('img.fmx-img').forEach(img => {
      const avatar = img.closest('.fmx-avatar');
      if (!avatar) return;

      if (img.complete && img.naturalWidth > 0) {
        avatar.classList.add('has-img');
        return;
      }

      img.addEventListener('load', () => avatar.classList.add('has-img'), { once:true });
      img.addEventListener('error', () => { img.remove(); avatar.classList.remove('has-img'); }, { once:true });
    });
  }

  function showLoadingState(){
    if (els.grid) els.grid.style.display = 'none';
    if (els.pager) els.pager.style.display = 'none';

    if (els.state){
      els.state.style.display = '';
      els.state.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        Loading faculty members…
      `;
    }
  }

  function showSkeleton(){
    if (els.grid) els.grid.style.display = 'none';
    if (els.pager) els.pager.style.display = 'none';
    if (els.state) els.state.style.display = 'none';

    if (!els.skel) return;
    els.skel.style.display = '';
    els.skel.innerHTML = Array.from({length: 6}).map(() => `<div class="fmx-sk"></div>`).join('');
  }

  function hideSkeleton(){
    if (!els.skel) return;
    els.skel.style.display = 'none';
    els.skel.innerHTML = '';
  }

  async function fetchJson(url){
    if (activeController) activeController.abort();
    activeController = new AbortController();

    const res = await fetch(url, {
      headers: { 'Accept':'application/json' },
      signal: activeController.signal
    });

    const js = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(js?.message || js?.error || ('Request failed: ' + res.status));
    return js;
  }

  function extractDeptUuidFromUrl(){
    const hay = (window.location.search || '') + ' ' + (window.location.href || '');
    const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
    return m ? m[1] : '';
  }

  // ✅ load ONLY ordered departments (active + has count)
  async function loadOrderedDepartments(){
    const sel = els.dept;
    if (!sel) return;

    sel.innerHTML = `
      <option value="${ALL_DEPTS}">All Departments</option>
      <option value="__loading" disabled>Loading departments…</option>
    `;
    sel.value = ALL_DEPTS;

    try{
      const js = await fetchJson(PREVIEW_INDEX);
      const rows = Array.isArray(js?.data) ? js.data : [];

      // rows:
      // { department:{id,uuid,slug,title}, order:{active,faculty_count} }
      const depts = rows
        .map(r => ({
          id: r?.department?.id ?? null,
          uuid: (r?.department?.uuid ?? '').toString().trim(),
          slug: (r?.department?.slug ?? '').toString().trim(),
          title:(r?.department?.title ?? '').toString().trim(),
          count: parseInt(r?.order?.faculty_count ?? 0, 10) || 0,
        }))
        .filter(d => d.uuid && d.title && d.count > 0);

      deptByUuid = new Map(depts.map(d => [d.uuid, d]));

      // keep dropdown clean (no brackets / no extra text)
      depts.sort((a,b) => a.title.localeCompare(b.title));

      sel.innerHTML = `<option value="${ALL_DEPTS}">All Departments</option>` + depts
        .map(d => `<option value="${escAttr(d.uuid)}">${esc(d.title)}</option>`)
        .join('');

      sel.value = ALL_DEPTS;

      if (!depts.length){
        if (els.state){
          els.state.style.display = '';
          els.state.innerHTML = `
            <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
              <i class="fa-solid fa-users"></i>
            </div>
            Faculty list is not available right now.
          `;
        }
      }

    } catch (e){
      console.warn('Ordered departments load failed:', e);
      sel.innerHTML = `<option value="${ALL_DEPTS}">All Departments</option>`;
      sel.value = ALL_DEPTS;
      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Faculty list is not available right now.
        `;
      }
    }
  }

  function cardHtml(it){
    const userUuid = pick(it, ['user_uuid','uuid']);
    const name = pick(it, ['name','user_name']) || 'Faculty';

    const desig =
      pick(it, ['designation']) ||
      (decodeMaybeJson(it?.metadata)?.designation || '') ||
      (decodeMaybeJson(it?.metadata)?.role_title || '') ||
      'Faculty Member';

    const qualification = formatQualification(it?.qualification);
    const specification = (pick(it, ['specification']) || '').toString().trim();
    const experience    = (pick(it, ['experience']) || '').toString().trim();
    const interest      = (pick(it, ['interest']) || '').toString().trim();
    const administration= (pick(it, ['administration']) || '').toString().trim();
    const research      = (pick(it, ['research_project']) || '').toString().trim();
    const deptLineTitle = (it?.__department_title || '').toString().trim();

    const meta = decodeMaybeJson(it?.metadata) || {};
    const email = (pick(it, ['email']) || meta.email || '').toString().trim();
    const website = (pick(it, ['website']) || meta.website || '').toString().trim();

    const imgRaw = pick(it, ['image_full_url','image']);
    const img = normalizeUrl(imgRaw);

    const href = getProfileUrl(userUuid);
    const ini = initials(name);

    return `
      <article class="fmx-card" tabindex="0" role="link"
               data-href="${escAttr(href)}"
               aria-label="${escAttr(name)} profile">
        <div class="fmx-body">
          <div class="fmx-top">
            <div class="fmx-avatar">
              <div class="fmx-initial">${esc(ini)}</div>
              ${img ? `<img class="fmx-img" src="${escAttr(img)}" alt="${escAttr(name)}" loading="lazy">` : ``}
            </div>

            <div style="min-width:0;flex:1;">
              <h3 class="fmx-name">${esc(name)}</h3>
              <div class="fmx-desig">${esc(desig)}</div>
            </div>
          </div>

          <div class="fmx-meta">
            ${state.deptUuid === ALL_DEPTS ? metaLine('Department', deptLineTitle) : ''}
            ${metaLine('Qualification', qualification)}
            ${metaLine('Specification', specification)}
            ${metaLine('Experience', experience)}
            ${metaLine('Interest', interest)}
            ${metaLine('Administration', administration)}
            ${metaLine('Research Project', research)}
          </div>

          <div class="fmx-links">
            ${email ? `<div><b>Email:</b> <a data-stop-card="1" href="mailto:${escAttr(email)}">${esc(email)}</a></div>` : ``}
            ${website ? `<div><b>Website:</b> <a data-stop-card="1" href="${escAttr(normalizeUrl(website))}" target="_blank" rel="noopener">${esc(website)}</a></div>` : ``}
          </div>

          ${buildSocialFromItem(it)}
        </div>
      </article>
    `;
  }

  function applySearch(items){
    const q = (state.q || '').trim().toLowerCase();
    if (!q) return items;

    // ✅ filter only; DO NOT reorder (keeps DB order)
    return items.filter(it => {
      const name = (pick(it, ['name','user_name']) || '').toLowerCase();
      const desig =
        (pick(it, ['designation']) ||
          (decodeMaybeJson(it?.metadata)?.designation || '') ||
          (decodeMaybeJson(it?.metadata)?.role_title || '')
        ).toString().toLowerCase();

      const qual = formatQualification(it?.qualification).toLowerCase();
      const dept = (it?.__department_title || '').toString().toLowerCase();
      return name.includes(q) || desig.includes(q) || qual.includes(q) || dept.includes(q);
    });
  }

  function paginate(items){
    const per = state.perPage || 9;
    const total = items.length;
    const last = Math.max(1, Math.ceil(total / per));
    state.lastPage = last;

    const page = Math.min(Math.max(1, state.page || 1), last);
    state.page = page;

    const start = (page - 1) * per;
    const slice = items.slice(start, start + per);
    return { slice, total, last };
  }

  function render(items){
    if (!els.grid || !els.state) return;

    if (!items.length){
      els.grid.style.display = 'none';
      els.state.style.display = '';
      els.state.innerHTML = `
        ${emptyStateIllustration()}
        ${state.deptUuid === ALL_DEPTS ? 'No faculty found.' : 'No faculty found for this department.'}
      `;
      return;
    }

    els.state.style.display = 'none';
    els.grid.style.display = '';
    els.grid.innerHTML = items.map(cardHtml).join('');
    bindAvatarImages(els.grid);
  }

  function renderPager(){
    const pager = els.pager;
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
      const cls = active ? 'fmx-pagebtn active' : 'fmx-pagebtn';
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

  // ✅ Force DB-order even if API returns unordered
  function toInt(v){
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : null;
  }
  function getUserNumericId(it){
    // your order is "faculty json array = id of faculties" -> typically user.id
    return (
      toInt(it?.user_id) ??
      toInt(it?.faculty_id) ??
      toInt(it?.id) ??
      toInt(it?.user?.id) ??
      null
    );
  }
  function extractOrderIds(js){
    // try common shapes:
    // js.order.faculty_ids OR js.order.faculty_ids_json OR js.faculty_ids OR js.order_ids
    const raw =
      js?.order?.faculty_ids ??
      js?.order?.faculty_ids_json ??
      js?.faculty_ids ??
      js?.order_ids ??
      js?.faculty_order ??
      null;

    const arr = Array.isArray(raw) ? raw : (decodeMaybeJson(raw) || null);
    if (!Array.isArray(arr)) return [];

    return arr.map(x => toInt(x)).filter(x => x !== null);
  }
  function orderByDb(assigned, orderIds){
    if (!Array.isArray(assigned) || !assigned.length) return [];
    if (!Array.isArray(orderIds) || !orderIds.length) return assigned;

    const idx = new Map(orderIds.map((id, i) => [String(id), i]));

    return assigned
      .map((it, originalIndex) => {
        const id = getUserNumericId(it);
        const key = id === null ? null : String(id);
        const orderIndex = (key && idx.has(key)) ? idx.get(key) : 1e9;
        return { it, originalIndex, orderIndex };
      })
      .sort((a,b) => (a.orderIndex - b.orderIndex) || (a.originalIndex - b.originalIndex))
      .map(x => x.it);
  }

  function uniqueFacultyKey(it){
    return (
      (pick(it, ['user_uuid','uuid']) || '').toString().trim() ||
      String(getUserNumericId(it) ?? '')
    );
  }

  async function loadAssignedForDept(deptUuid){
    showSkeleton();

    try{
      const url = PREVIEW_SHOW_BASE + encodeURIComponent(deptUuid) + '?status=active';
      const js = await fetchJson(url);

      const dept = js?.department || {};
      const assigned = Array.isArray(js?.assigned) ? js.assigned : [];

      // ✅ enforce DB saved order
      const orderIds = extractOrderIds(js);
      assignedAll = orderByDb(assigned, orderIds).map(it => ({
        ...it,
        __department_title: (dept?.title || deptByUuid.get(deptUuid)?.title || '').toString().trim(),
        __department_uuid: deptUuid
      }));

      // header/subtitle
      state.deptName = (dept?.title || deptByUuid.get(deptUuid)?.title || '').toString().trim();
      if (els.sub) {
        els.sub.textContent = state.deptName ? ('Faculty members of ' + state.deptName) : 'Faculty members';
      }

      state.page = 1;
      hideSkeleton();
      applyAndRender();

    } catch (e){
      hideSkeleton();
      if (els.grid) els.grid.style.display = 'none';
      if (els.pager) els.pager.style.display = 'none';

      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Faculty list is not available right now.
        `;
      }
    }
  }

  async function loadAssignedForAllDepartments(){
    showSkeleton();

    try{
      const depts = Array.from(deptByUuid.values()).sort((a,b) => a.title.localeCompare(b.title));
      const merged = [];
      const seen = new Set();

      for (const d of depts){
        try{
          const url = PREVIEW_SHOW_BASE + encodeURIComponent(d.uuid) + '?status=active';
          const js = await fetchJson(url);

          const assigned = Array.isArray(js?.assigned) ? js.assigned : [];
          const orderIds = extractOrderIds(js);
          const ordered = orderByDb(assigned, orderIds);

          for (const it of ordered){
            const key = uniqueFacultyKey(it);
            if (!key) continue;
            if (seen.has(key)) continue;
            seen.add(key);

            merged.push({
              ...it,
              __department_title: d.title,
              __department_uuid: d.uuid
            });
          }
        } catch (err){
          console.warn('Faculty load failed for dept:', d?.title || d?.uuid, err);
        }
      }

      assignedAll = merged;
      state.deptName = 'All Departments';
      if (els.sub) els.sub.textContent = 'Faculty members of all departments';

      state.page = 1;
      hideSkeleton();
      applyAndRender();

    } catch (e){
      hideSkeleton();
      if (els.grid) els.grid.style.display = 'none';
      if (els.pager) els.pager.style.display = 'none';

      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Faculty list is not available right now.
        `;
      }
    }
  }

  function applyAndRender(){
    const filtered = applySearch(assignedAll);
    const { slice } = paginate(filtered);
    render(slice);
    renderPager();
  }

  document.addEventListener('DOMContentLoaded', async () => {
    // ✅ Attach all listeners FIRST (so deep-link load doesn't skip them)

    // dept change
    els.dept && els.dept.addEventListener('change', async () => {
      const v = (els.dept.value || '').toString().trim();

      state.page = 1;
      state.q = '';
      assignedAll = [];
      if (els.search) els.search.value = '';

      if (!v || v === ALL_DEPTS){
        state.deptUuid = ALL_DEPTS;
        await loadAssignedForAllDepartments();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      state.deptUuid = v;
      await loadAssignedForDept(v);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // search (debounced, client-side on assigned list; keeps DB order)
    let t = null;
    els.search && els.search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        state.q = (els.search.value || '').trim();
        state.page = 1;
        applyAndRender();
      }, 220);
    });

    // pagination
    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.fmx-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      applyAndRender();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ✅ card click -> profile
    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-stop-card="1"]')) return;
      const card = e.target.closest('.fmx-card[data-href]');
      if (!card) return;
      const href = card.getAttribute('data-href') || '#';
      if (!href || href === '#') return;
      window.location.href = href;
    });

    // ✅ keyboard open
    document.addEventListener('keydown', (e) => {
      const card = e.target.closest?.('.fmx-card[data-href]');
      if (!card) return;
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const href = card.getAttribute('data-href') || '#';
        if (href && href !== '#') window.location.href = href;
      }
    });

    // ✅ Initial load
    showLoadingState();

    // dropdown from preview-order public index (only ordered depts)
    await loadOrderedDepartments();

    // deep-link ?d-{uuid}
    const deep = extractDeptUuidFromUrl();
    if (deep && deptByUuid.has(deep)){
      els.dept.value = deep;
      state.deptUuid = deep;
      state.page = 1;
      state.q = '';
      if (els.search) els.search.value = '';
      await loadAssignedForDept(deep);
    } else {
      // default = all departments
      state.deptUuid = ALL_DEPTS;
      if (els.dept) els.dept.value = ALL_DEPTS;
      await loadAssignedForAllDepartments();
    }
  });

})();
</script>