{{-- resources/views/public/successStories/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Success Stories</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --ss-accent: var(--primary-color, #9E363A);
      --ss-border: rgba(0,0,0,.08);
      --ss-shadow: 0 10px 24px rgba(0,0,0,.08);
      --ss-radius: 10px;

      --ss-card-w: 381.5px;
      --ss-card-h: 426.4px;
      --ss-media-h: 240px;
    }
    body{background:#f6f7fb}

    .ss-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .ss-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .ss-title{font-weight:800;letter-spacing:.2px;margin:0;}

    .ss-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .ss-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--ss-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .ss-toolbar .form-control{min-width:100%}
      .ss-head{flex-direction:column;align-items:stretch}
    }

    /* Grid */
    .ss-grid{
    display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:25px;
  align-items:stretch;
    }
    
/* Tablet -> 2 cards */
@media (max-width: 992px){
  .ss-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
}

/* Mobile -> 1 card */
@media (max-width: 576px){
  .ss-grid{ grid-template-columns:1fr; }
}


    /* Card */
    .ss-card{
      width:100%;
      height:var(--ss-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--ss-border);
      border-radius:var(--ss-radius);
      background:#fff;
      box-shadow:var(--ss-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .ss-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    .ss-media{
      width:100%;
      height:var(--ss-media-h);
      flex:0 0 auto;
      background:var(--ss-accent);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:800;
      font-size:28px;
      letter-spacing:.3px;
      user-select:none;
    }
    .ss-media img{width:100%;height:100%;object-fit:cover;display:block;}
    @media (max-width:576px){ .ss-media{font-size:24px;} }

    .ss-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .ss-h{
      font-size:22px;line-height:1.25;font-weight:800;margin:0 0 10px 0;color:#0f172a;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    }
    .ss-sub{
      margin:0 0 8px 0;
      color:#334155;
      font-weight:700;
      font-size:14px;
      display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;
      min-height:18px;
    }
    .ss-p{
      margin:0;color:#475569;font-size:15px;line-height:1.7;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .ss-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .ss-pill{
      position:absolute;
      left:12px;
      top:12px;
      z-index:1;
      padding:6px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:800;
      background:rgba(0,0,0,.55);
      color:#fff;
      backdrop-filter: blur(6px);
    }

    .ss-link{position:absolute;inset:0;z-index:2;border-radius:var(--ss-radius);}

    /* Skeletons */
    .ss-skel{
      width:100%;
      height:var(--ss-card-h);
      border:1px solid var(--ss-border);
      border-radius:var(--ss-radius);
      background:#fff;
      box-shadow:var(--ss-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .ss-skel .m{
      height:var(--ss-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ss-skel .b{padding:18px;flex:1}
    .ss-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ss-skel .l.w70{width:70%}
    .ss-skel .l.w95{width:95%}
    .ss-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .ss-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-ss{
      border-radius:12px;padding:10px 16px;border:1px solid var(--ss-border);
      background:#fff;font-weight:700;
    }
    .btn-ss:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .ss-empty{text-align:center;color:#64748b;padding:40px 10px;}

                .ss-title i {
color: var(--ss-accent);
}
  </style>
</head>
<body>

  <div class="ss-wrap">
    <div class="ss-head">
      <div>
        <h2 class="ss-title"><i class="fa-solid fa-bullhorn"></i> Success Stories</h2>
        <div class="text-muted" style="font-size:13px;">Alumni journeys, placements, and inspiring wins.</div>
      </div>

      <div class="ss-toolbar">
        <input id="ssSearch" class="form-control" type="search" placeholder="Search stories (name/title/quote)..." />
      </div>
    </div>

    <div class="ss-grid" id="ssGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="ss-skel">
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

    <div class="ss-footer">
      <button id="ssLoadMore" class="btn btn-ss d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="ssEmpty" class="ss-empty d-none">
      <div style="font-size:34px; line-height:1;">✨</div>
      <div class="mt-2" style="font-weight:800; font-size:18px;">No success stories found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      // ✅ API route that calls SuccessStoryController@publicIndex
      const API_INDEX = @json(url('/api/public/success-stories'));

      // ✅ web route: /success-stories/view/{uuid}
      const VIEW_BASE = @json(url('/success-stories/view'));

      const grid     = document.getElementById('ssGrid');
      const btnMore  = document.getElementById('ssLoadMore');
      const emptyBox = document.getElementById('ssEmpty');
      const qInput   = document.getElementById('ssSearch');

      let page = 1;
      let lastPage = 1;
      let loading = false;
      let q = '';

      const stripHtml = (html) => {
        const div = document.createElement('div');
        div.innerHTML = (html || '');
        return (div.textContent || div.innerText || '').trim();
      };

      const escAttr = (s) => String(s || '').replace(/"/g, '&quot;');

      const fmtDate = (iso) => {
        if (!iso) return '';
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        return new Intl.DateTimeFormat('en-IN', { day:'2-digit', month:'short', year:'numeric' }).format(d);
      };

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="ss-skel">
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
        grid.querySelectorAll('.ss-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const cardHtml = (item) => {
        const name  = item.name || 'Student';
        const title = item.title || '';
        const quote = stripHtml(item.quote || item.description || '');
        const excerpt = quote.length > 220 ? (quote.slice(0, 220).trim() + '...') : quote;

        const created = fmtDate(item.created_at || null);
        const yearTag = (item.year && String(item.year).trim() !== '') ? String(item.year).trim() : '';

        const uuid = item.uuid ? String(item.uuid) : '';
        const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

        const photo = item.photo_full_url || null;

        return `
          <div class="ss-card">
            ${yearTag ? `<div class="ss-pill">${escAttr(yearTag)}</div>` : ``}

            <div class="ss-media">
              ${photo
                ? `<img src="${photo}" alt="${escAttr(name)}" loading="lazy" />`
                : `<div>${escAttr(name).slice(0,1).toUpperCase()}</div>`
              }
            </div>

            <div class="ss-body">
              <div class="ss-h">${name}</div>
              <div class="ss-sub">${title || '&nbsp;'}</div>
              <p class="ss-p">${excerpt || ''}</p>

              <div class="ss-date">
                <i class="fa-regular fa-calendar"></i>
                <span>Created: ${created || '—'}</span>
              </div>
            </div>

            ${uuid
              ? `<a class="ss-link" href="${href}" aria-label="Open ${escAttr(name)}"></a>`
              : `<div class="ss-link" title="Missing UUID"></div>`
            }
          </div>
        `;
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

          // ✅ only published + in window
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
          console.error('Success stories load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="ss-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:800; font-size:18px;">Failed to load success stories</div>
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
