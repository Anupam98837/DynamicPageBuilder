{{-- resources/views/modules/home/viewStickyButtons.blade.php --}}
{{-- ✅ FRONTEND WIDGET (partial): floating sticky contact buttons (right-middle)
     - Reads from PUBLIC API: /api/public/sticky-buttons
     - Your API returns: { success:true, data:[ { buttons_json:[...], ... } ], pagination:{} }
     - We render the LATEST record's buttons_json
     - Ensures Font Awesome loads (auto-injects CDN link if missing)
     - Safe to @include() inside any page (no <html>, no <body>, no global overrides)
--}}

<style>
.sbx-wrap{
    --sbx-brand: var(--primary-color, #9E363A);
    --sbx-brand-2: var(--secondary-color, #6B2528);
    --sbx-ink: #ffffff;
    --sbx-shadow: 0 8px 24px rgba(2,6,23,.18);
    --sbx-w: 52px;
    --sbx-h: 52px;
    --sbx-gap: 6px;

    position: fixed;
    right: 0px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 9999;
    pointer-events: none;
  }

  .sbx-stack{
    display:flex;
    flex-direction:column;
    gap: var(--sbx-gap);
    pointer-events: auto;
    padding: 6px 0;
  }

  .sbx-item{
    position: relative;
    width: var(--sbx-w);
    height: var(--sbx-h);
    background: linear-gradient(135deg, var(--sbx-brand) 0%, var(--sbx-brand-2) 100%);
    border-radius: 50%;
    box-shadow: var(--sbx-shadow);
    transition: all .25s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: visible;
  }

  .sbx-link{
    width: 100%;
    height: 100%;
    display:flex;
    align-items:center;
    justify-content:center;
    color: var(--sbx-ink);
    text-decoration:none;
    outline: none;
    border-radius: 50%;
    position: relative;
    transition: transform .25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .sbx-link::before{
    content: '';
    position: absolute;
    inset: -2px;
    background: rgba(255,255,255,.15);
    border-radius: 50%;
    opacity: 0;
    transition: opacity .25s ease;
  }

  .sbx-link i{
    font-size: 22px;
    line-height: 1;
    position: relative;
    z-index: 1;
    transition: transform .25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  /* Label bubble (shows on hover) */
  .sbx-label{
    position:absolute;
    left: -12px;
    top: 50%;
    transform: translate(-100%, -50%) translateX(10px);
    opacity: 0;
    pointer-events: none;

    background: rgba(15,23,42,.95);
    color: #fff;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 8px 20px rgba(0,0,0,.2);
    transition: all .25s cubic-bezier(0.34, 1.56, 0.64, 1);
    backdrop-filter: blur(10px);
  }

  .sbx-label::after{
    content:'';
    position:absolute;
    right: -5px;
    top: 50%;
    transform: translateY(-50%);
    width: 0; height: 0;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
    border-left: 5px solid rgba(15,23,42,.95);
  }

  .sbx-item:hover .sbx-label,
  .sbx-item:focus-within .sbx-label{
    opacity: 1;
    transform: translate(-100%, -50%) translateX(0);
  }

  /* Enhanced hover feedback */
  .sbx-item:hover{
    transform: translateX(-8px) scale(1.08);
    box-shadow: 0 12px 32px rgba(2,6,23,.25), 0 0 0 3px rgba(255,255,255,.2);
  }

  .sbx-item:hover .sbx-link::before{
    opacity: 1;
  }

  .sbx-item:hover .sbx-link i{
    transform: scale(1.15);
    color: white;
  }

  .sbx-item:active{
    transform: translateX(-6px) scale(1.02);
  }

  .sbx-item:active .sbx-link i{
    transform: scale(0.9);
  }

  /* In case bootstrap isn't present, define a scoped visually-hidden */
  .sbx-visually-hidden{
    position:absolute!important;
    width:1px!important;
    height:1px!important;
    padding:0!important;
    margin:-1px!important;
    overflow:hidden!important;
    clip:rect(0,0,0,0)!important;
    white-space:nowrap!important;
    border:0!important;
  }

  /* Mobile: slightly smaller */
  @media (max-width: 576px){
    .sbx-wrap{ --sbx-w: 46px; --sbx-h: 46px; --sbx-gap: 5px; }
    .sbx-link i{ font-size: 20px; }
    .sbx-label{ font-size: 12px; padding: 7px 12px; }
    .sbx-item:hover{ transform: translateX(-6px) scale(1.05); }
  }
</style>

<div class="sbx-wrap" aria-label="Sticky contact buttons">
  <div class="sbx-stack" id="sbxStack"></div>
</div>

<script>
(() => {
  if (window.__STICKY_BUTTONS_VIEW_INIT__) return;
  window.__STICKY_BUTTONS_VIEW_INIT__ = true;

  const esc = (s) => (s ?? '').toString().replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));

  const stack = document.getElementById('sbxStack');
  if (!stack) return;

  // ✅ Optional: pass buttons directly from parent page
  // window.__STICKY_BUTTONS_DATA__ = [{ icon_class:'fa-solid fa-phone', action_url:'tel:...', name:'Call' }, ...]
  const inlineData = Array.isArray(window.__STICKY_BUTTONS_DATA__) ? window.__STICKY_BUTTONS_DATA__ : null;

  // ✅ Your public API
  const API_URL = window.__STICKY_BUTTONS_API_URL || '/api/public/sticky-buttons';

  function hasFontAwesome(){
    // check links
    const links = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));
    for (const l of links){
      const href = (l.getAttribute('href') || '').toLowerCase();
      if (href.includes('font-awesome') || href.includes('fontawesome') || href.includes('cdnjs.cloudflare.com/ajax/libs/font-awesome')) return true;
    }
    // check inline usage
    const ss = Array.from(document.styleSheets || []);
    for (const s of ss){
      const href = (s.href || '').toLowerCase();
      if (href.includes('font-awesome') || href.includes('fontawesome')) return true;
    }
    return false;
  }

  function ensureFontAwesome(){
    if (hasFontAwesome()) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css';
    document.head.appendChild(link);
  }

  function safeArray(v){
    if (Array.isArray(v)) return v;
    if (typeof v === 'string'){
      try{
        const j = JSON.parse(v);
        return Array.isArray(j) ? j : [];
      }catch(_){ return []; }
    }
    return [];
  }

  function normalizeButton(x){
    const obj = (x && typeof x === 'object') ? x : {};

    // label: show "name" first (your API stores name as the value you want on hover)
    const label =
      (obj.name ?? obj.label ?? obj.title ?? obj.key ?? 'Link').toString().trim();

    // icon: use stored icon_class
    const iconClass =
      (obj.icon_class ?? obj.iconClass ?? obj.icon ?? '').toString().trim() || 'fa-solid fa-link';

    // url: use action_url first (your API already provides it)
    const url =
      (obj.action_url ?? obj.url ?? obj.href ?? obj.link ?? '').toString().trim();

    // if still empty, fallback to value (last resort)
    const fallbackValue = (obj.value ?? '').toString().trim();
    const href = url || fallbackValue || '#';

    // target: only blank for http(s)
    const isHttp = /^https?:\/\//i.test(href);
    const target = isHttp ? '_blank' : '_self';
    const rel = isHttp ? 'noopener noreferrer' : '';

    // enabled: allow both 1/0 and active/inactive if ever sent
    const enabledRaw = (obj.is_active ?? obj.active ?? obj.status ?? 1);
    const enabledStr = String(enabledRaw).toLowerCase().trim();
    const isEnabled = (enabledRaw === true) || enabledStr === '1' || enabledStr === 'yes' || enabledStr === 'active';

    return { label, iconClass, href, target, rel, isEnabled };
  }

  function renderButtons(list){
    const items = safeArray(list)
      .map(normalizeButton)
      .filter(it => it.isEnabled);

    if (!items.length){
      stack.innerHTML = '';
      return;
    }

    stack.innerHTML = items.map((it) => {
      const isVoid = !it.href || it.href === '#';
      return `
        <div class="sbx-item">
          <a class="sbx-link"
             href="${esc(it.href)}"
             ${it.target ? `target="${esc(it.target)}"` : ''}
             ${it.rel ? `rel="${esc(it.rel)}"` : ''}
             ${isVoid ? 'aria-disabled="true" tabindex="-1"' : ''}
             title="${esc(it.label)}">
            <i class="${esc(it.iconClass)}" aria-hidden="true"></i>
            <span class="sbx-visually-hidden">${esc(it.label)}</span>
          </a>
          <div class="sbx-label">${esc(it.label)}</div>
        </div>
      `;
    }).join('');
  }

  async function loadFromApi(){
    try{
      const res = await fetch(API_URL, { headers: { 'Accept':'application/json' } });
      const js = await res.json().catch(() => ({}));

      // ✅ Handle both:
      // - publicIndex: { success:true, data:[ {buttons_json:[...]} ], pagination:{} }
      // - publicCurrent: { success:true, item:{buttons_json:[...]} }
      const record =
        (js && typeof js === 'object' && js.item) ? js.item :
        (Array.isArray(js?.data) && js.data.length) ? js.data[0] :
        null;

      const buttons = record ? safeArray(record.buttons_json) : [];

      renderButtons(buttons);
    }catch(_){
      stack.innerHTML = '';
    }
  }

  async function init(){
    ensureFontAwesome();

    if (inlineData){
      renderButtons(inlineData);
      return;
    }

    await loadFromApi();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
