{{-- resources/views/public/achievements/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Achievements</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --ac-accent: var(--primary-color, #9E363A);
      --ac-border: rgba(0,0,0,.08);
      --ac-shadow: 0 10px 24px rgba(0,0,0,.08);
      --ac-radius: 10px;

      --ac-card-w: 381.5px;
      --ac-card-h: 426.4px;
      --ac-media-h: 240px;
    }
    body{background:#f6f7fb}

    .ac-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .ac-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .ac-title{font-weight:800;letter-spacing:.2px;margin:0;}

    .ac-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .ac-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--ac-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .ac-toolbar .form-control{min-width:100%}
      .ac-head{flex-direction:column;align-items:stretch}
    }

    /* Grid */
   /* Grid (3 / 2 / 1) */
.ac-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:25px;
  align-items:stretch;
}

/* Tablet -> 2 cards */
@media (max-width: 992px){
  .ac-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
}

/* Mobile -> 1 card */
@media (max-width: 576px){
  .ac-grid{ grid-template-columns:1fr; }
}

    /* Card */
    .ac-card{
      width: 100%;
      height:var(--ac-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--ac-border);
      border-radius:var(--ac-radius);
      background:#fff;
      box-shadow:var(--ac-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .ac-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    /* ✅ robust media fallback (prevents broken image icon + big title in media) */
    .ac-media{
      width:100%;
      height:var(--ac-media-h);
      flex:0 0 auto;
      background:var(--ac-accent);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .ac-media .ac-fallback{
      position:absolute;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:800;
      font-size:28px;
      letter-spacing:.3px;
      z-index:0;
    }
    .ac-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index:1;
    }
    @media (max-width:576px){
      .ac-media .ac-fallback{font-size:24px;}
    }

    .ac-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .ac-h{
      font-size:22px;
      line-height:1.25;
      font-weight:800;
      margin:0 0 10px 0;
      color:#0f172a;

      /* ✅ clamp big titles */
      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;

      overflow-wrap:anywhere;
      word-break:break-word;
      min-height:0;
    }

    .ac-p{
      margin:0;
      color:#475569;
      font-size:15px;
      line-height:1.7;

      /* ✅ if description is big only fitting portion show rest */
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
      hyphens:auto;
    }

    .ac-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .ac-link{position:absolute;inset:0;z-index:2;border-radius:var(--ac-radius);}

    /* Skeletons */
    .ac-skel{
      width: 100%;
      height:var(--ac-card-h);
      border:1px solid var(--ac-border);
      border-radius:var(--ac-radius);
      background:#fff;
      box-shadow:var(--ac-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .ac-skel .m{
      height:var(--ac-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ac-skel .b{padding:18px;flex:1}
    .ac-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ac-skel .l.w70{width:70%}
    .ac-skel .l.w95{width:95%}
    .ac-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .ac-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-ac{
      border-radius:12px;padding:10px 16px;border:1px solid var(--ac-border);
      background:#fff;font-weight:700;
    }
    .btn-ac:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .ac-empty{text-align:center;color:#64748b;padding:40px 10px;}
  </style>
</head>
<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <div class="ac-wrap">
    <div class="ac-head">
      <div>
        <h2 class="ac-title">Achievements</h2>
        <div class="text-muted" style="font-size:13px;">Awards, recognitions, and proud moments.</div>
      </div>

      <div class="ac-toolbar">
        <input id="acSearch" class="form-control" type="search" placeholder="Search achievements (title/body)..." />
      </div>
    </div>

    <div class="ac-grid" id="acGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="ac-skel">
          <div class="m"></div>
          <div class="b">
            <div class="l w70"></div>
            <div class="l w95"></div>
            <div class="l w85"></div>
            <div class="l w70" style="margin-top:16px;"></div>
          </div>
        </div>
      @endfor
    </div>

    <div class="ac-footer">
      <button id="acLoadMore" class="btn btn-ac d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="acEmpty" class="ac-empty d-none">
      <div style="font-size:34px; line-height:1;">🏆</div>
      <div class="mt-2" style="font-weight:800; font-size:18px;">No achievements found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      // ✅ must match your API route that calls AchievementController@publicIndex
      const API_INDEX = @json(url('/api/public/achievements'));

      // ✅ web route: /achievements/view/{uuid}
      const VIEW_BASE = @json(url('/achievements/view'));

      const grid     = document.getElementById('acGrid');
      const btnMore  = document.getElementById('acLoadMore');
      const emptyBox = document.getElementById('acEmpty');
      const qInput   = document.getElementById('acSearch');

      let page = 1;
      let lastPage = 1;
      let loading = false;
      let q = '';

      // ✅ safe HTML output
      const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[m]));

      const escAttr = (s) => String(s || '').replace(/"/g, '&quot;');

      // ✅ better HTML->text so words don't join (DeskOur issue)
      const stripHtml = (html) => {
        const raw = String(html || '')
          .replace(/<\s*br\s*\/?>/gi, ' ')
          .replace(/<\/\s*(p|div|li|h[1-6]|tr|td|th|section|article)\s*>/gi, '$& ')
          .replace(/<\s*(p|div|li|h[1-6]|tr|td|th|section|article)\b[^>]*>/gi, ' ');

        const div = document.createElement('div');
        div.innerHTML = raw;
        return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
      };

      const fmtDate = (iso) => {
        if (!iso) return '';
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        return new Intl.DateTimeFormat('en-IN', { day:'2-digit', month:'short', year:'numeric' }).format(d);
      };

      const cardHtml = (item) => {
  const titleRaw = item.title || 'Untitled';
  const title = escapeHtml(titleRaw);

  const bodyText = stripHtml(item.body || '');

  // ✅ force dots after "few characters"
  const MAX_CHARS = 90; // change to 70/100/120 as you want
  let excerptText = bodyText;

  if (bodyText.length > MAX_CHARS) {
    excerptText = bodyText
      .slice(0, MAX_CHARS)
      .trim()
      .replace(/[,\.;:\-\s]+$/g, ''); // remove ending punctuation/spaces
    excerptText += '......';
  }

  const excerpt = escapeHtml(excerptText || '');

  const created = fmtDate(item.created_at || null);

  const uuid = item.uuid ? String(item.uuid) : '';
  const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

  const cover = item.cover_image_url ? String(item.cover_image_url).trim() : '';

  return `
    <div class="ac-card">
      <div class="ac-media">
        <div class="ac-fallback">Achievement</div>

        ${cover ? `
          <img
            src="${escAttr(cover)}"
            alt=""
            loading="lazy"
            style="display:none"
            onload="this.style.display='block'"
            onerror="this.remove()"
          />
        ` : ``}
      </div>

      <div class="ac-body">
        <div class="ac-h">${title}</div>
        <p class="ac-p">${excerpt}</p>

        <div class="ac-date">
          <i class="fa-regular fa-calendar"></i>
          <span>Created: ${escapeHtml(created || '—')}</span>
        </div>
      </div>

      ${uuid
        ? `<a class="ac-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
        : `<div class="ac-link" title="Missing UUID"></div>`
      }
    </div>
  `;
};

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="ac-skel">
              <div class="m"></div>
              <div class="b">
                <div class="l w70"></div>
                <div class="l w95"></div>
                <div class="l w85"></div>
                <div class="l w70" style="margin-top:16px;"></div>
              </div>
            </div>
          `);
        }
        grid.innerHTML = s.join('');
      };

      const removeSkeletons = () => {
        grid.querySelectorAll('.ac-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const fetchPage = async (reset=false) => {
        if (loading) return;
        loading = true;

        if (reset) {
          page = 1;
          lastPage = 1;
          setSkeletons(4);
          setEmpty(false);
        }

        setMore();

        try{
          const url = new URL(API_INDEX, window.location.origin);
          url.searchParams.set('per_page', '10');
          url.searchParams.set('page', String(page));
          if (q) url.searchParams.set('q', q);

          // ✅ only published + in visible window (your controller supports this)
          url.searchParams.set('visible_now', '1');

          const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error('HTTP ' + res.status);

          const json = await res.json();
          const items = Array.isArray(json.data) ? json.data : [];
          const pg = json.pagination || {};
          lastPage = Number(pg.last_page || 1);

          removeSkeletons();
          if (reset) grid.innerHTML = '';

          if (!items.length && page === 1) {
            setEmpty(true);
            btnMore.classList.add('d-none');
            loading = false;
            return;
          }

          const frag = document.createElement('div');
          frag.innerHTML = items.map(cardHtml).join('');
          while (frag.firstChild) grid.appendChild(frag.firstChild);

          setEmpty(false);
          setMore();
        } catch (e) {
          console.error('Achievements load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="ac-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:800; font-size:18px;">Failed to load achievements</div>
                <div class="mt-1">Please try again.</div>
              </div>
            `;
          }
          btnMore.classList.add('d-none');
        } finally {
          loading = false;
          setMore();
        }
      };

      btnMore.addEventListener('click', () => {
        if (page >= lastPage) return;
        page += 1;
        fetchPage(false);
      });

      let t = null;
      qInput.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
          q = (qInput.value || '').trim();
          fetchPage(true);
        }, 350);
      });

      fetchPage(true);
    })();
  </script>
</body>
</html>
