{{-- resources/views/landing/components/mainHeader.blade.php --}}

<!-- Bootstrap 5 CSS (same pattern as your working header.blade.php) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  /* =========================================================
     Main Header (Public) - Flex layout (4 sections)
     Sections in .mh-inner:
       1) Primary logo
       2) Title + rotating text + affiliation marquee
       3) Secondary logo
       4) Partner marquee + admission badge
     ========================================================= */

  :root{
    --mh-red: var(--primary-color, #9E363A);
    --mh-red-dark: var(--secondary-color, #6B2528);
    --mh-ink: #111827;
    --mh-muted:#6B7280;
    --mh-line:#E5E7EB;
    --mh-bg:#FFFFFF;
  }

  .mh-bar, .mh-bar *{ box-sizing:border-box; }

  .mh-bar{
    width:100%;
    background:var(--mh-bg);
    overflow:visible;
  }

  /* ✅ 4-section FLEX container */
  .mh-inner{
    max-width:1400px;
    margin:0 auto;
    padding:0px 0px;
    display:flex;
    align-items:stretch;
    gap:18px;
  }

  /* ===== SECTION 1: Primary logo ===== */
  .mh-sec1{
    flex:0 0 110px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .mh-primary-logo{
    width:92px;
    height:92px;
    object-fit:contain;
    display:block;
  }

  /* ===== SECTION 2: Center block ===== */
  .mh-sec2{
    flex:1 1 auto;
    min-width:0;
    display:flex;
    flex-direction:column;
    justify-content:center;
    gap:6px;
  }

  .mh-title{
    display:inline-block;
    color:var(--mh-red);
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.8px;
    line-height:1.06;
    font-size:38px;
    padding-bottom:7px;
    border-bottom:3px solid var(--mh-red);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .mh-subrow{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    min-width:0;
  }

  .mh-rotate{
    flex:1 1 auto;
    min-width:0;
    color:var(--mh-red);
    font-size:1.1rem;
    font-weight:400;
    line-height:1.2;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    transition:opacity .18s ease, transform .18s ease;
  }
  .mh-rotate.is-fading{ opacity:0; transform:translateY(-2px); }

    /* Smooth hover color (with slight delay) */
.mh-rotate{
  transition: opacity .18s ease, transform .10s ease, color .15s ease .10s;
}

.mh-rotate:hover{
  color:#0D29AC; /* same blue as screenshot */
  cursor: pointer;
}


  /* Affiliation marquee (inside section 2) */
  .mh-affil-wrap{ flex:0 0 380px; max-width:380px; }
  .mh-affil-marquee{ height:36px; }

  /* ✅ FIX: Make ALL marquee logos same size + framed */
  .mh-affil-logo,
  .mh-partner-logo{
width: 35px;
height: 35px;
object-fit: contain;
display: block;
padding: 1px 1px;
border: 1px solid var(--mh-line);
border-radius: 5px;
background: var(--mh-bg);
  }

  /* ===== SECTION 3: Secondary logo (ONLY) ===== */
  .mh-sec3{
    display:flex;
    align-items:center;
    justify-content:flex-end;
  }
  .mh-secondary-logo{
    max-height:92px;
    width:auto;
    object-fit:contain;
    display:block;
  }

  /* ===== SECTION 4: Partner marquee + admission badge ===== */
  .mh-sec4{
    flex:0 0 270px;
    min-width:200px;
    display:flex;
    flex-direction:column;
    align-items:stretch;
    justify-content:center;
    gap:10px;
  }

  /* partner marquee row */
  .mh-partner-marquee{ height:40px; }

  /* admission row */
  .mh-admission-row{
    display:flex;
    align-items:center;
    justify-content:flex-end;
  }

  .mh-admission{
    flex:0 0 auto;
    display:flex;
    align-items:center;
    justify-content:flex-end;
    text-decoration:none;
  }
  .mh-badge{
    height:56px;
    width:auto;
    object-fit:contain;
    display:block;
    transition:transform .12s ease, filter .12s ease;
  }
  .mh-admission:hover .mh-badge{
    transform:translateY(-1px);
    filter:drop-shadow(0 6px 14px rgba(0,0,0,.12));
  }

  /* ===== Marquee base ===== */
  .mh-marquee{
    position:relative;
    overflow:hidden;
    border-radius:10px;
    background:transparent;
    width:100%;
  }
  .mh-marquee::before,
  .mh-marquee::after{
    content:"";
    position:absolute;
    top:0; bottom:0;
    width:22px;
    pointer-events:none;
    z-index:2;
  }
  .mh-marquee::before{
    left:0;
    background:linear-gradient(to right, var(--mh-bg), rgba(255,255,255,0));
  }
  .mh-marquee::after{
    right:0;
    background:linear-gradient(to left, var(--mh-bg), rgba(255,255,255,0));
  }

  .mh-track{
    --mh-shift: 0px;
    --mh-duration: 18s;
    display:flex;
    align-items:center;
    width:max-content;
    gap:14px;
    will-change:transform;
  }
  .mh-track.is-animated{ animation: mh-scroll var(--mh-duration) linear infinite; }
  .mh-track:hover{ animation-play-state:paused; }

  @keyframes mh-scroll{
    from{ transform:translateX(0); }
    to{ transform:translateX(calc(-1 * var(--mh-shift))); }
  }

  .mh-set{ display:flex; align-items:center; gap:14px; }

  /* ===== Skeleton ===== */
  .mh-skel{
    background:linear-gradient(90deg, #f3f4f6, #e5e7eb, #f3f4f6);
    background-size:200% 100%;
    animation: mh-skel 1.1s ease-in-out infinite;
    border-radius:10px;
  }
  @keyframes mh-skel{
    0%{ background-position: 200% 0; }
    100%{ background-position: -200% 0; }
  }
  .mh-title.mh-skel{ height:46px; width:92%; border-bottom:none; }
  .mh-rotate.mh-skel{ height:22px; width:55%; }
  .mh-affil-marquee.mh-skel{ height:36px; width:380px; }
  .mh-partner-marquee.mh-skel{ height:40px; width:100%; }
  .mh-secondary-logo.mh-skel{ height:86px; width:170px; }
  .mh-badge.mh-skel{ height:56px; width:230px; }

  /* Responsive */
  @media (max-width: 1100px){
    .mh-title{ font-size:34px; }
    .mh-affil-wrap{ flex-basis:320px; max-width:320px; }
    .mh-sec4{ flex-basis:300px; min-width:270px; }
  }

  @media (max-width: 920px){
    .mh-inner{ flex-wrap:wrap; }
    .mh-sec1{ flex:0 0 92px; }
    .mh-sec2{ flex:1 1 calc(100% - 110px); }
    .mh-sec3, .mh-sec4{ flex:1 1 100%; min-width:0; justify-content:flex-start; }
    .mh-title{ white-space:normal; }
    .mh-subrow{ flex-wrap:wrap; }
    .mh-affil-wrap{ flex:1 1 100%; max-width:100%; }
  }

  @media (prefers-reduced-motion: reduce){
    .mh-track.is-animated{ animation:none !important; }
    .mh-skel{ animation:none !important; }
  }

  /* Hide marquees + Section 3 & 4 at 992px and below */
@media (max-width: 992px){
  /* Hide 3rd + 4th sections */
  .mh-sec3,
  .mh-sec4{
    display:none !important;
  }

  /* Hide both marquees (affiliation + partner) */
  .mh-affil-wrap,
  #mhAffilMarquee,
  #mhPartnerMarquee{
    display:none !important;
  }

  /* Optional: since affil is gone, don't push space to the right */
  .mh-subrow{
    justify-content:flex-start;
  }
  .mh-title{font-size:2rem;}
  .mh-sec2{flex:none;}
}

/* Hide header title + rotating text at 782px and below */
@media (max-width: 782px){
  #mhHeaderText,
  #mhRotateText{
    display:none !important;
  }
}


</style>

<header class="mh-bar" id="mhBar" data-endpoint="{{ url('/api/header-components') }}">
  <div class="mh-inner">

    {{-- SECTION 1: Primary logo --}}
    <div class="mh-sec1">
      <img id="mhPrimaryLogo" class="mh-primary-logo mh-skel" alt="Primary logo" />
    </div>

    {{-- SECTION 2: Title + rotate + affiliation marquee --}}
    <div class="mh-sec2">
      <div id="mhHeaderText" class="mh-title mh-skel" aria-label="Institute name"></div>

      <div class="mh-subrow">
        <div id="mhRotateText" class="mh-rotate mh-skel" aria-label="Rotating text"></div>

        <div class="mh-affil-wrap">
          <div id="mhAffilMarquee" class="mh-marquee mh-affil-marquee mh-skel" aria-label="Affiliation logos marquee">
            <div id="mhAffilTrack" class="mh-track"></div>
          </div>
        </div>
      </div>
    </div>

    {{-- SECTION 3: Secondary logo --}}
    <div class="mh-sec3">
      <img id="mhSecondaryLogo" class="mh-secondary-logo mh-skel" alt="Secondary logo" />
    </div>

    {{-- SECTION 4: Partner marquee + Admission badge --}}
    <div class="mh-sec4">
      <div id="mhPartnerMarquee" class="mh-marquee mh-partner-marquee mh-skel" aria-label="Partner logos marquee">
        <div id="mhPartnerTrack" class="mh-track"></div>
      </div>

      <div class="mh-admission-row">
        <a id="mhAdmissionLink" class="mh-admission" href="javascript:void(0)" aria-label="Admission link">
          <img id="mhAdmissionBadge" class="mh-badge mh-skel" alt="Admission badge" />
        </a>
      </div>
    </div>

  </div>
</header>

<script>
(() => {
  if (window.__MAIN_HEADER_SINGLETON__) return;
  window.__MAIN_HEADER_SINGLETON__ = true;

  const $ = (id) => document.getElementById(id);

  const els = {
    bar: $('mhBar'),
    primary: $('mhPrimaryLogo'),
    title: $('mhHeaderText'),
    rotate: $('mhRotateText'),
    affilMarquee: $('mhAffilMarquee'),
    affilTrack: $('mhAffilTrack'),
    partnerMarquee: $('mhPartnerMarquee'),
    partnerTrack: $('mhPartnerTrack'),
    secondary: $('mhSecondaryLogo'),
    badge: $('mhAdmissionBadge'),
    admissionLink: $('mhAdmissionLink'),
  };

  const PLACEHOLDER_SVG = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" width="260" height="160" viewBox="0 0 260 160">
      <rect width="260" height="160" fill="#f3f4f6"/>
      <rect x="16" y="16" width="228" height="128" rx="16" fill="#ffffff" stroke="#e5e7eb"/>
      <path d="M80 112h100v10H80z" fill="#9ca3af"/>
      <path d="M92 88h76v8H92z" fill="#9ca3af"/>
      <path d="M118 44h24c10 0 18 8 18 18v12h-60V62c0-10 8-18 18-18z" fill="#cbd5e1"/>
      <text x="130" y="140" text-anchor="middle" font-family="Arial" font-size="12" fill="#6b7280">No Image</text>
    </svg>
  `);

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
  }

  function setImg(imgEl, src){
    if (!imgEl) return;
    const u = normalizeUrl(src);
    imgEl.src = u || PLACEHOLDER_SVG;
    imgEl.classList.remove('mh-skel');
  }

  function removeSkel(el){
    if (!el) return;
    el.classList.remove('mh-skel');
  }

  /* ===== Rotating text ===== */
  let rotateTimer = null;
  function startRotate(lines){
    if (rotateTimer) { clearInterval(rotateTimer); rotateTimer = null; }

    const el = els.rotate;
    removeSkel(el);

    const arr = Array.isArray(lines) ? lines.map(x => (x ?? '').toString().trim()).filter(Boolean) : [];
    if (!arr.length){
      el.textContent = '';
      return;
    }

    let idx = 0;
    el.textContent = arr[0];

    if (arr.length === 1) return;

    rotateTimer = setInterval(() => {
      idx = (idx + 1) % arr.length;
      el.classList.add('is-fading');
      setTimeout(() => {
        el.textContent = arr[idx];
        el.classList.remove('is-fading');
      }, 180);
    }, 2600);
  }

  /* ===== Marquee builder ===== */
  function buildMarquee(trackEl, items, getSrc, getAlt, speedFactor = 35){
    if (!trackEl) return;

    trackEl.innerHTML = '';
    trackEl.classList.remove('is-animated');
    trackEl.style.removeProperty('--mh-shift');
    trackEl.style.removeProperty('--mh-duration');

    const list = Array.isArray(items) ? items : [];
    const clean = list
      .map((it) => {
        const src = (getSrc(it) || '').toString().trim();
        if (!src) return null;
        return { src, alt: (getAlt(it) || '').toString().trim() };
      })
      .filter(Boolean);

    if (!clean.length) return;

    const set1 = document.createElement('div');
    set1.className = 'mh-set';

    clean.forEach(({src, alt}) => {
      const img = document.createElement('img');
      img.loading = 'lazy';
      img.alt = alt || 'logo';
      img.src = normalizeUrl(src) || PLACEHOLDER_SVG;
      img.className = trackEl.id === 'mhAffilTrack' ? 'mh-affil-logo' : 'mh-partner-logo';
      set1.appendChild(img);
    });

    trackEl.appendChild(set1);

    if (clean.length <= 2) return;

    const set2 = set1.cloneNode(true);
    trackEl.appendChild(set2);

    requestAnimationFrame(() => {
      const w = set1.getBoundingClientRect().width;
      if (!w || w < 10) return;

      trackEl.style.setProperty('--mh-shift', w + 'px');

      const duration = Math.max(10, Math.min(30, w / speedFactor));
      trackEl.style.setProperty('--mh-duration', duration.toFixed(2) + 's');

      trackEl.classList.add('is-animated');
    });
  }

  function pickLatestItem(js){
    const arr = Array.isArray(js?.data) ? js.data : [];
    return arr[0] || null;
  }

  async function loadHeader(){
    const endpointBase = els.bar?.getAttribute('data-endpoint') || '/api/header-components';
    const qs = new URLSearchParams({
      per_page: '1',
      page: '1',
      sort: 'updated_at',
      direction: 'desc'
    });

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(endpointBase.replace(/\/+$/,'') + '?' + qs.toString(), { headers });
    const js = await res.json().catch(() => ({}));
    const item = pickLatestItem(js);

    if (!res.ok || !item){
      removeSkel(els.title); els.title.textContent = '';
      removeSkel(els.rotate); els.rotate.textContent = '';
      setImg(els.primary, '');
      setImg(els.secondary, '');
      setImg(els.badge, '');
      removeSkel(els.affilMarquee);
      removeSkel(els.partnerMarquee);
      return;
    }

    setImg(els.primary, item.primary_logo_full_url || item.primary_logo_url || '');

    removeSkel(els.title);
    els.title.textContent = (item.header_text || '').toString().trim();

    startRotate(item.rotating_text_json || []);

    removeSkel(els.affilMarquee);
    const aff = Array.isArray(item.affiliation_logos) ? item.affiliation_logos : [];
    buildMarquee(
      els.affilTrack,
      aff,
      (x) => x?.url_full || x?.url || x?.path || '',
      (x) => x?.caption || 'Affiliation logo',
      35
    );

    removeSkel(els.partnerMarquee);
    const partners = Array.isArray(item.partner_recruiters) ? item.partner_recruiters : [];
    buildMarquee(
      els.partnerTrack,
      partners,
      (x) => x?.logo_full_url || x?.logo_url || '',
      (x) => x?.title || 'Partner logo',
      35
    );

    setImg(els.secondary, item.secondary_logo_full_url || item.secondary_logo_url || '');

    setImg(els.badge, item.admission_badge_full_url || item.admission_badge_url || '');
    const link = (item.admission_badge_link || item.admission_link_full_url || item.admission_link_url || '').toString().trim();

    if (link){
      els.admissionLink.href = normalizeUrl(link);
      els.admissionLink.target = '_blank';
      els.admissionLink.rel = 'noopener';
      els.admissionLink.style.pointerEvents = '';
      els.admissionLink.style.opacity = '';
    } else {
      els.admissionLink.href = 'javascript:void(0)';
      els.admissionLink.removeAttribute('target');
      els.admissionLink.removeAttribute('rel');
      els.admissionLink.style.pointerEvents = 'none';
      els.admissionLink.style.opacity = '.85';
    }
  }

  let resizeT = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeT);
    resizeT = setTimeout(() => { loadHeader().catch(()=>{}); }, 250);
  });

  document.addEventListener('DOMContentLoaded', () => {
    loadHeader().catch(() => {});
  });
})();
</script>
