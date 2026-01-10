{{-- resources/views/public/events/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Events</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --ev-accent: var(--primary-color, #9E363A);
      --ev-border: rgba(0,0,0,.08);
      --ev-shadow: 0 10px 24px rgba(0,0,0,.08);
      --ev-radius: 10px;

      --ev-card-w: 381.5px;
      --ev-card-h: 426.4px;
      --ev-media-h: 240px;
    }
    body{background:#f6f7fb}

    .ev-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .ev-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .ev-title{font-weight:800;letter-spacing:.2px;margin:0;}

    .ev-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .ev-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--ev-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .ev-toolbar .form-control{min-width:100%}
      .ev-head{flex-direction:column;align-items:stretch}
    }

    /* Grid */
    .ev-grid{
      display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:25px;
  align-items:stretch;
    }
    
/* Tablet -> 2 cards */
@media (max-width: 992px){
  .ev-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
}

/* Mobile -> 1 card */
@media (max-width: 576px){
  .ev-grid{ grid-template-columns:1fr; }
}

    /* Card */
    .ev-card{
      width:100%;
      height:var(--ev-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--ev-border);
      border-radius:var(--ev-radius);
      background:#fff;
      box-shadow:var(--ev-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .ev-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    .ev-media{
      width:100%;
      height:var(--ev-media-h);
      flex:0 0 auto;
      background:var(--ev-accent);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:900;
      font-size:28px;
      letter-spacing:.3px;
      user-select:none;
      position:relative;
    }
    .ev-media img{width:100%;height:100%;object-fit:cover;display:block;}
    .ev-media .ev-fallback{
      padding:0 18px;
      text-align:center;
      line-height:1.15;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .ev-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .ev-h{
      font-size:22px;line-height:1.25;font-weight:900;margin:0 0 10px 0;color:#0f172a;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    }

    .ev-meta{
      display:flex;
      flex-direction:column;
      gap:6px;
      margin-bottom:10px;
      color:#334155;
      font-weight:700;
      font-size:13px;
    }
    .ev-meta .rowx{display:flex;align-items:center;gap:8px;min-height:18px;}
    .ev-meta i{width:16px;text-align:center;color:#64748b;}

    .ev-p{
      margin:0;color:#475569;font-size:15px;line-height:1.7;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .ev-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .ev-pill{
      position:absolute;
      left:12px;
      top:12px;
      z-index:1;
      padding:6px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:900;
      background:rgba(0,0,0,.55);
      color:#fff;
      backdrop-filter: blur(6px);
    }

    .ev-link{position:absolute;inset:0;z-index:2;border-radius:var(--ev-radius);}

    /* Skeletons */
    .ev-skel{
      width:100%;
      height:var(--ev-card-h);
      border:1px solid var(--ev-border);
      border-radius:var(--ev-radius);
      background:#fff;
      box-shadow:var(--ev-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .ev-skel .m{
      height:var(--ev-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ev-skel .b{padding:18px;flex:1}
    .ev-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .ev-skel .l.w70{width:70%}
    .ev-skel .l.w95{width:95%}
    .ev-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .ev-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-ev{
      border-radius:12px;padding:10px 16px;border:1px solid var(--ev-border);
      background:#fff;font-weight:800;
    }
    .btn-ev:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .ev-empty{text-align:center;color:#64748b;padding:40px 10px;}

        .ev-title i {
color: var(--ev-accent);
}
  </style>
</head>
<body>

  <div class="ev-wrap">
    <div class="ev-head">
      <div>
        <h2 class="ev-title"><i class="fa-solid fa-bullhorn"></i> Events</h2>
        <div class="text-muted" style="font-size:13px;">Workshops, seminars, fests, and campus activities.</div>
      </div>

      <div class="ev-toolbar">
        <input id="evSearch" class="form-control" type="search" placeholder="Search events (title/location)..." />
      </div>
    </div>

    <div class="ev-grid" id="evGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="ev-skel">
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

    <div class="ev-footer">
      <button id="evLoadMore" class="btn btn-ev d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="evEmpty" class="ev-empty d-none">
      <div style="font-size:34px; line-height:1;">📅</div>
      <div class="mt-2" style="font-weight:900; font-size:18px;">No events found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      // ✅ API route that calls EventController@publicIndex
      const API_INDEX = @json(url('/api/public/events'));

      // ✅ web route: /events/view/{uuid}
      const VIEW_BASE = @json(url('/events/view'));

      const grid     = document.getElementById('evGrid');
      const btnMore  = document.getElementById('evLoadMore');
      const emptyBox = document.getElementById('evEmpty');
      const qInput   = document.getElementById('evSearch');

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

      const fmtTime = (t) => {
        if (!t) return '';
        // accept "HH:MM" or "HH:MM:SS"
        const parts = String(t).split(':');
        if (parts.length < 2) return '';
        const hh = parseInt(parts[0], 10);
        const mm = parts[1];
        if (isNaN(hh)) return '';
        const ampm = hh >= 12 ? 'PM' : 'AM';
        const h12 = ((hh + 11) % 12) + 1;
        return `${h12}:${mm} ${ampm}`;
      };

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="ev-skel">
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
        grid.querySelectorAll('.ev-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const buildWhen = (it) => {
        const sd = it.event_start_date ? fmtDate(it.event_start_date) : '';
        const ed = it.event_end_date ? fmtDate(it.event_end_date) : '';
        const st = it.event_start_time ? fmtTime(it.event_start_time) : '';
        const et = it.event_end_time ? fmtTime(it.event_end_time) : '';

        let datePart = '';
        if (sd && ed && sd !== ed) datePart = `${sd} – ${ed}`;
        else datePart = sd || ed || '';

        let timePart = '';
        if (st && et) timePart = `${st} – ${et}`;
        else timePart = st || et || '';

        if (datePart && timePart) return `${datePart} • ${timePart}`;
        return datePart || timePart || '';
      };

      const cardHtml = (item) => {
        const title = item.title || 'Event';
        const loc   = item.location || '';
        const when  = buildWhen(item);

        const descRaw = item.description ? stripHtml(item.description) : '';
        const excerpt = descRaw.length > 220 ? (descRaw.slice(0, 220).trim() + '...') : descRaw;

        const created = fmtDate(item.created_at || null);

        const uuid = item.uuid ? String(item.uuid) : '';
        const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

        const cover = item.cover_image_url || null;

        // pill: show "Upcoming" if start_date in future, else show start_date
        let pill = '';
        try{
          if (item.event_start_date){
            const now = new Date(); now.setHours(0,0,0,0);
            const sd = new Date(item.event_start_date);
            if (!isNaN(sd.getTime())) {
              if (sd.getTime() > now.getTime()) pill = 'Upcoming';
              else pill = fmtDate(item.event_start_date);
            }
          }
        } catch(e){}

        return `
          <div class="ev-card">
            ${pill ? `<div class="ev-pill">${escAttr(pill)}</div>` : ``}

            <div class="ev-media">
              ${cover
                ? `<img src="${cover}" alt="${escAttr(title)}" loading="lazy" />`
                : `<div class="ev-fallback">${escAttr(title)}</div>`
              }
            </div>

            <div class="ev-body">
              <div class="ev-h">${title}</div>

              <div class="ev-meta">
                <div class="rowx">
                  <i class="fa-solid fa-location-dot"></i>
                  <span>${loc ? escAttr(loc) : '—'}</span>
                </div>
                <div class="rowx">
                  <i class="fa-regular fa-clock"></i>
                  <span>${when ? escAttr(when) : '—'}</span>
                </div>
              </div>

              <p class="ev-p">${excerpt || ''}</p>

              <div class="ev-date">
                <i class="fa-regular fa-calendar"></i>
                <span>Created: ${created || '—'}</span>
              </div>
            </div>

            ${uuid
              ? `<a class="ev-link" href="${href}" aria-label="Open ${escAttr(title)}"></a>`
              : `<div class="ev-link" title="Missing UUID"></div>`
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
          console.error('Events load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="ev-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:900; font-size:18px;">Failed to load events</div>
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
