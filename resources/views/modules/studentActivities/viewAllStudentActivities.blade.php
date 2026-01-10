{{-- resources/views/public/studentActivities/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Student Activities</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Your common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --sa-accent: var(--primary-color, #9E363A);
      --sa-border: rgba(0,0,0,.08);
      --sa-shadow: 0 10px 24px rgba(0,0,0,.08);
      --sa-radius: 10px;

      /* fixed card size */
      --sa-card-h: 426.4px;
      --sa-media-h: 240px;
    }
    body{background:#f6f7fb}

    .sa-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .sa-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .sa-title{font-weight:800;letter-spacing:.2px;margin:0;}

    .sa-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .sa-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--sa-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .sa-toolbar .form-control{min-width:100%}
      .sa-head{flex-direction:column;align-items:stretch}
      :root{ --sa-media-h: 210px; }
    }

    /* ✅ GRID (3 / 2 / 1) + GAP */
    .sa-grid{
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:25px;
      align-items:stretch;
    }
    @media (max-width: 992px){
      .sa-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px){
      .sa-grid{ grid-template-columns:1fr; }
    }

    /* ✅ CARD: width must be 100% inside grid */
    .sa-card{
      width:100%;
      height:var(--sa-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--sa-border);
      border-radius:var(--sa-radius);
      background:#fff;
      box-shadow:var(--sa-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .sa-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    /* ✅ robust media fallback */
    .sa-media{
      width:100%;
      height:var(--sa-media-h);
      flex:0 0 auto;
      background:var(--sa-accent);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .sa-media .sa-fallback{
      position:absolute; inset:0;
      display:flex; align-items:center; justify-content:center;
      padding:0 18px;
      text-align:center;
      line-height:1.15;
      color:#fff;
      font-weight:800;
      font-size:28px;
      letter-spacing:.3px;
      z-index:0;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    .sa-media img{
      position:absolute; inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index:1;
    }
    @media (max-width:576px){
      .sa-media .sa-fallback{font-size:24px;}
    }

    .sa-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .sa-h{
      font-size:22px;
      line-height:1.25;
      font-weight:800;
      margin:0 0 10px 0;
      color:#0f172a;
      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;
      min-height:0;
    }

    .sa-p{
      margin:0;
      color:#475569;
      font-size:15px;
      line-height:1.7;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
      min-height:0;
    }

    .sa-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .sa-link{position:absolute;inset:0;z-index:4;border-radius:var(--sa-radius);}

    /* ✅ skeletons match grid width */
    .sa-skel{
      width:100%;
      height:var(--sa-card-h);
      border:1px solid var(--sa-border);
      border-radius:var(--sa-radius);
      background:#fff;
      box-shadow:var(--sa-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .sa-skel .m{
      height:var(--sa-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .sa-skel .b{padding:18px;flex:1}
    .sa-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .sa-skel .l.w70{width:70%}
    .sa-skel .l.w95{width:95%}
    .sa-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .sa-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-sa{
      border-radius:12px;padding:10px 16px;border:1px solid var(--sa-border);
      background:#fff;font-weight:700;
    }
    .btn-sa:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .sa-empty{text-align:center;color:#64748b;padding:40px 10px;}

        .sa-title i {
color: var(--sa-accent);
}
  </style>
</head>
<body>

  <div class="sa-wrap">
    <div class="sa-head">
      <div>
        <h2 class="sa-title"><i class="fa-solid fa-bullhorn"></i> Student Activities</h2>
        <div class="text-muted" style="font-size:13px;">Latest updates, workshops, events, and campus highlights.</div>
      </div>

      <div class="sa-toolbar">
        <input id="saSearch" class="form-control" type="search" placeholder="Search activities (title/body)..." />
      </div>
    </div>

    <div class="sa-grid" id="saGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="sa-skel">
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

    <div class="sa-footer">
      <button id="saLoadMore" class="btn btn-sa d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="saEmpty" class="sa-empty d-none">
      <div style="font-size:34px; line-height:1;">🗂️</div>
      <div class="mt-2" style="font-weight:800; font-size:18px;">No activities found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      const API_INDEX = @json(url('/api/public/student-activities'));
      const VIEW_BASE = @json(url('/student-activities/view'));

      const grid     = document.getElementById('saGrid');
      const btnMore  = document.getElementById('saLoadMore');
      const emptyBox = document.getElementById('saEmpty');
      const qInput   = document.getElementById('saSearch');

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

      const cardHtml = (item) => {
        const title = item.title || 'Untitled';
        const bodyText = stripHtml(item.body || '');
        const excerpt = bodyText.length > 220 ? (bodyText.slice(0, 220).trim() + '...') : bodyText;

        const created = fmtDate(item.created_at || null);

        const uuid = item.uuid ? String(item.uuid) : '';
        const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

        const cover = item.cover_image_url ? String(item.cover_image_url).trim() : '';

        return `
          <div class="sa-card">
            <div class="sa-media">
              <div class="sa-fallback">${escAttr(title)}</div>

              ${cover ? `
                <img class="sa-img"
                     src="${escAttr(cover)}"
                     alt="${escAttr(title)}"
                     loading="lazy" />
              ` : ``}
            </div>

            <div class="sa-body">
              <div class="sa-h">${title}</div>
              <p class="sa-p">${excerpt || ''}</p>

              <div class="sa-date">
                <i class="fa-regular fa-calendar"></i>
                <span>Created: ${created || '—'}</span>
              </div>
            </div>

            ${uuid
              ? `<a class="sa-link" href="${href}" aria-label="Open ${escAttr(title)}"></a>`
              : `<div class="sa-link" title="Missing UUID"></div>`
            }
          </div>
        `;
      };

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="sa-skel">
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
        grid.querySelectorAll('.sa-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      // ✅ image load/error handler (no broken icon)
      const bindCardImages = (rootEl) => {
        rootEl.querySelectorAll('img.sa-img').forEach(img => {
          const media = img.closest('.sa-media');
          const fallback = media ? media.querySelector('.sa-fallback') : null;

          if (img.complete && img.naturalWidth > 0) {
            if (fallback) fallback.style.display = 'none';
            return;
          }

          img.addEventListener('load', () => {
            if (fallback) fallback.style.display = 'none';
          }, { once:true });

          img.addEventListener('error', () => {
            img.remove();
            if (fallback) fallback.style.display = '-webkit-box';
          }, { once:true });
        });
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

          bindCardImages(grid);

          setEmpty(false);
          setMore();
        } catch (e) {
          console.error('Student activities load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="sa-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:800; font-size:18px;">Failed to load activities</div>
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
