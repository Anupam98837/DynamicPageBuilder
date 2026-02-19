{{-- resources/views/landing/our-recruiters.blade.php --}}

<style>
    /* =========================================================
      ✅ Recruiters MINI (Same grid tiles, auto-scroll)
      - Same API
      - No search / no dept / no pagination
      - Title + View All
      - Max 4 rows visible
      - ✅ FIXED: no sudden vertical gaps (dense packing)
      - ✅ FIXED: uniform spacing (no extra grid gap)
    ========================================================= */
  
    .orc-scope{
      --orc-brand:  var(--primary-color, #8f2f2f);
      --orc-ink:    var(--ink, #0f172a);
      --orc-muted:  var(--muted-color, #64748b);
      --orc-bg:     var(--page-bg, #ffffff);
      --orc-card:   var(--surface, #ffffff);
      --orc-line:   var(--line-soft, rgba(15, 23, 42, .10));
      --orc-shadow: var(--shadow-2, 0 10px 24px rgba(2, 6, 23, .08));
      --orc-marquee-duration: 36s;
      --orc-tile-h: 110px;
    }
  
    .orc-wrap{max-width:1320px;margin:18px auto 54px;padding:0 12px;position:relative;overflow:visible}
  
    /* Header */
    .orc-head{
      background:var(--orc-card);
      border:1px solid var(--orc-line);
      border-radius:16px;
      box-shadow:var(--orc-shadow);
      padding:14px 16px;
      margin-bottom:14px;
      display:flex;
      gap:12px;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap
    }
    .orc-title{margin:0;font-weight:950;letter-spacing:.2px;color:var(--orc-ink);font-size:26px;display:flex;align-items:center;gap:10px}
    .orc-title i{color:var(--orc-brand)}
    .orc-sub{margin:6px 0 0;color:var(--orc-muted);font-size:14px}
  
    .orc-viewall{
      border:1px solid var(--orc-line);
      background:var(--orc-card);
      color:var(--orc-ink);
      border-radius:999px;
      padding:10px 14px;
      font-size:13px;
      font-weight:900;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:8px;
      box-shadow:0 10px 18px rgba(2,6,23,.06);
      transition:all .2s ease;
      white-space:nowrap
    }
    .orc-viewall:hover{transform:translateY(-1px);background:rgba(2,6,23,.02);border-color:rgba(201,75,80,.35);color:var(--orc-brand)}
  
    /* ===== Auto-scroll rail ===== */
    .orc-rail{
      background:var(--orc-card);
      border:1px solid var(--orc-line);
      border-radius:16px;
      box-shadow:var(--orc-shadow);
      overflow:hidden;
      position:relative
    }
    .orc-rail:before,.orc-rail:after{
      content:'';
      position:absolute;
      top:0;bottom:0;
      width:52px;
      z-index:3;
      pointer-events:none
    }
    .orc-rail:before{left:0;background:linear-gradient(90deg,var(--orc-card),rgba(255,255,255,0))}
    .orc-rail:after{right:0;background:linear-gradient(270deg,var(--orc-card),rgba(255,255,255,0))}
  
    .orc-track{display:flex;width:max-content;will-change:transform}
    .orc-track[data-anim="1"]{animation:orcGridMove var(--orc-marquee-duration) linear infinite}
    .orc-rail:hover .orc-track[data-anim="1"]{animation-play-state:paused}
    @keyframes orcGridMove{from{transform:translateX(0)}to{transform:translateX(-50%)}}
  
    /* ✅ each copy (max 4 rows only) */
    .orc-grid-wrap{
      flex:0 0 auto;
      width:min(1320px, calc(100vw - 24px));
      padding:0; /* ✅ no outer padding gap */
      max-height:calc(var(--orc-tile-h) * 4); /* ✅ 4 rows */
      overflow:hidden
    }
  
    /* =========================================================
       ✅ SAME GRID + TILE LOOK (tight, uniform, no sudden gaps)
    ========================================================= */
    .orc-grid{
      display:grid;
      grid-template-columns:repeat(9, minmax(0,1fr));
      align-items:stretch;
      gap:0;                     /* ✅ same as your earlier grid */
      grid-auto-rows:var(--orc-tile-h);
      grid-auto-flow:dense;      /* ✅ packs holes so no vertical big gaps */
    }
  
    .orc-tile{
      margin:0;
      border-radius:12px;
      overflow:hidden;
      background:#fff;
      border:1px solid rgba(15,23,42,.06);
      box-shadow:0 1px 3px rgba(2,6,23,.06),0 6px 12px rgba(2,6,23,.04);
      cursor:pointer;
      transition:all .2s cubic-bezier(.4,0,.2,1);
      height:var(--orc-tile-h);
      grid-column:span 1;
      grid-row:span 1;           /* ✅ keep row uniform */
      position:relative;
      outline:none;
      text-decoration:none;
      display:block
    }
  
    /* keep your masonry-ish wider boxes */
    .orc-tile:nth-child(12n + 2),
    .orc-tile:nth-child(12n + 4),
    .orc-tile:nth-child(12n + 6),
    .orc-tile:nth-child(12n + 7),
    .orc-tile:nth-child(12n + 9),
    .orc-tile:nth-child(12n + 11){grid-column:span 2}
  
    .orc-tile:hover,.orc-tile:focus{
      transform:translateY(-3px);
      box-shadow:0 4px 6px rgba(2,6,23,.08),0 16px 28px rgba(2,6,23,.12);
      border-color:rgba(143,47,47,.20)
    }
    .orc-tile__inner{display:block;width:100%;height:100%;background:#fff}
    .orc-tile img{width:100%;height:100%;object-fit:contain;object-position:center;display:block;padding:8px}
    .orc-tile__fallback{height:100%;display:flex;align-items:center;justify-content:center;padding:14px 12px;color:#64748b;font-weight:900;font-size:14px;text-align:center}
  
    /* Skeleton */
    .orc-skeleton{display:grid;gap:14px;grid-template-columns:repeat(12, minmax(0,1fr))}
    .orc-sk-tile{--w:2;grid-column:span var(--w);background:#fff;border:1px solid var(--orc-line);box-shadow:var(--orc-shadow);border-radius:18px;overflow:hidden;position:relative;height:var(--orc-tile-h)}
    .orc-sk-tile:before{content:'';position:absolute;inset:0;transform:translateX(-60%);background:linear-gradient(90deg,transparent,rgba(148,163,184,.22),transparent);animation:orcSkMove 1.15s ease-in-out infinite}
    @keyframes orcSkMove{to{transform:translateX(60%)}}
    .orc-sk-tile:nth-child(6n + 1){--w:1}.orc-sk-tile:nth-child(6n + 2){--w:2}.orc-sk-tile:nth-child(6n + 3){--w:1}.orc-sk-tile:nth-child(6n + 4){--w:2}.orc-sk-tile:nth-child(6n + 5){--w:3}.orc-sk-tile:nth-child(6n + 6){--w:3}
  
    .orc-state{background:var(--orc-card);border:1px solid var(--orc-line);border-radius:16px;box-shadow:var(--orc-shadow);padding:18px;color:var(--orc-muted);text-align:center}
  
    /* Responsive columns (same as your original file) */
    @media (max-width: 1200px){ .orc-grid{ grid-template-columns: repeat(5, minmax(0,1fr)); } }
    @media (max-width: 992px) { .orc-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }
    @media (max-width: 768px) { .orc-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media (max-width: 520px) { .orc-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
  
    /* Touch devices: stop animation, allow swipe scroll */
    @media (hover: none){
      .orc-rail{ overflow-x:auto; -webkit-overflow-scrolling:touch; }
      .orc-track[data-anim="1"]{ animation:none; }
      .orc-rail:before,.orc-rail:after{ display:none; }
      .orc-rail::-webkit-scrollbar{ display:none; }
      .orc-rail{ scrollbar-width:none; }
    }
  
    @media (max-width: 520px){ .orc-title{ font-size: 22px; } }
  
    .dynamic-navbar .navbar-nav .dropdown-menu{position:absolute !important;inset:auto !important}
    .dynamic-navbar .dropdown-menu.is-portaled{position:fixed !important}
  </style>
  
  <div class="orc-wrap orc-scope" data-api="{{ url('/api/public/recruiters') }}" data-view-all="{{ url('/our-recruiters') }}">
    <div class="orc-head">
      <div>
        <h2 class="orc-title"><i class="fa-solid fa-building"></i>Our Recruiters</h2>
        <div class="orc-sub">Companies that recruit from our campus</div>
      </div>
      <a class="orc-viewall" href="{{ url('/our-recruiters') }}">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>
  
    <div id="recRail" class="orc-rail" style="display:none;">
      <div id="recTrack" class="orc-track" aria-label="Recruiters auto scrolling grid"></div>
    </div>
  
    <div id="recSkeleton" class="orc-skeleton"></div>
    <div id="recState" class="orc-state" style="display:none;"></div>
  </div>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  
  <script>
  (() => {
    const roots = Array.from(document.querySelectorAll('.orc-wrap.orc-scope')).filter(r => r.getAttribute('data-orc-inited') !== '1');
    if (!roots.length) return;
  
    roots.forEach((root) => {
      root.setAttribute('data-orc-inited', '1');
  
      const API = root.getAttribute('data-api') || '/api/public/recruiters';
      const VIEW_ALL = root.getAttribute('data-view-all') || '/our-recruiters';
  
      const rail = root.querySelector('#recRail');
      const track = root.querySelector('#recTrack');
      const skel = root.querySelector('#recSkeleton');
      const stateEl = root.querySelector('#recState');
  
      const esc = (str) => (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
      const escAttr = (str) => (str ?? '').toString().replace(/"/g,'&quot;');
  
      const pick = (obj, keys) => { for (const k of keys){ const v=obj?.[k]; if(v!==null&&v!==undefined&&String(v).trim()!=='') return v; } return ''; };
      const pickMeta = (it, keys) => { const meta=(it&&typeof it==='object'&&it.metadata&&typeof it.metadata==='object')?it.metadata:{}; return pick(meta, keys); };
  
      const normalizeUrl = (url) => {
        const u = (url || '').toString().trim();
        if (!u) return '';
        if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
        if (u.startsWith('//')) return 'https:' + u;
        if (u.startsWith('/')) return window.location.origin + u;
        if (u.includes('.') && !u.includes(' ')) return 'https://' + u.replace(/^\/+/, '');
        return window.location.origin + '/' + u.replace(/^\/+/, '');
      };
  
      const showSkeleton = () => {
        if (skel){ skel.style.display=''; skel.innerHTML = Array.from({length:18}).map(()=>`<div class="orc-sk-tile"></div>`).join(''); }
        if (stateEl) stateEl.style.display='none';
        if (rail) rail.style.display='none';
      };
      const hideSkeleton = () => { if (!skel) return; skel.style.display='none'; skel.innerHTML=''; };
      const showState = (msg) => {
        if (stateEl){
          stateEl.style.display='';
          stateEl.innerHTML = `<div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-face-frown"></i></div>${esc(msg||'No recruiters found.')}`;
        }
        if (rail) rail.style.display='none';
      };
  
      const fetchRecruiters = async () => {
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page','1');
        u.searchParams.set('per_page','500');
        u.searchParams.set('sort','created_at');
        u.searchParams.set('direction','desc');
        const res = await fetch(u.toString(), { headers: { 'Accept':'application/json' } });
        const js = await res.json().catch(()=>({}));
        if (!res.ok) throw new Error(js?.message || ('Request failed: ' + res.status));
        return Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
      };
  
      const bindTileImages = (scopeEl) => {
        scopeEl.querySelectorAll('img.orc-logo').forEach(img => {
          const tile = img.closest('.orc-tile');
          const fallback = tile ? tile.querySelector('.orc-tile__fallback') : null;
          const showFallback = () => { if (fallback) fallback.style.display='flex'; };
          const hideFallback = () => { if (fallback) fallback.style.display='none'; };
  
          if (img.complete && img.naturalWidth > 0){ hideFallback(); return; }
          img.addEventListener('load', () => hideFallback(), { once:true });
          img.addEventListener('error', () => { img.remove(); showFallback(); }, { once:true });
        });
      };
  
      const buildGridHtml = (items) => `
        <div class="orc-grid-wrap">
          <div class="orc-grid">
            ${items.map((it) => {
              const name = pick(it, ['name','title','company','label']) || 'Recruiter';
              const logoRaw =
                pick(it, ['logo_url_full','logo_url','image_url','image_full_url','logo','image','src','url']) ||
                pickMeta(it, ['logo_url_full','logo_url','logo','image','src','url']) ||
                (it?.attachment?.url ?? '');
              const logo = logoRaw ? normalizeUrl(logoRaw) : '';
              const fallbackStyle = logo ? 'style="display:none"' : '';
              return `
                <a class="orc-tile" href="${escAttr(VIEW_ALL)}" aria-label="View all recruiters">
                  <span class="orc-tile__inner">
                    <span class="orc-tile__fallback" ${fallbackStyle}>${esc(name)}</span>
                    ${logo ? `<img class="orc-logo" src="${escAttr(logo)}" alt="${escAttr(name)}" loading="lazy" decoding="async" referrerpolicy="no-referrer">` : ``}
                  </span>
                </a>
              `;
            }).join('')}
          </div>
        </div>
      `;
  
      const renderAutoScroll = (items) => {
        if (!track || !rail) return;
  
        /* ✅ smaller preview so 4-row clamp never looks broken */
        const PREVIEW_COUNT = 36;
        const cleaned = (items || []).slice(0, PREVIEW_COUNT);
  
        if (!cleaned.length){ showState('No recruiters found.'); return; }
  
        track.innerHTML = buildGridHtml(cleaned) + buildGridHtml(cleaned);
  
        const n = cleaned.length;
        const dur = Math.max(24, Math.min(70, Math.round(n * 1.15)));
        root.style.setProperty('--orc-marquee-duration', dur + 's');
  
        track.dataset.anim = (n < 10) ? '0' : '1';
  
        bindTileImages(root);
        if (stateEl) stateEl.style.display='none';
        rail.style.display='';
      };
  
      (async () => {
        try{
          showSkeleton();
          const items = await fetchRecruiters();
          hideSkeleton();
          renderAutoScroll(items);
        } catch (e){
          console.warn('Recruiters load failed:', e);
          hideSkeleton();
          showState('Failed to load recruiters.');
        }
      })();
    });
  })();
  </script>
  