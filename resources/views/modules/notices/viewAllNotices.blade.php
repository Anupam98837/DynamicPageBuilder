{{-- resources/views/public/notices/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Notices</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --nt-accent: var(--primary-color, #9E363A);
      --nt-border: rgba(0,0,0,.08);
      --nt-shadow: 0 10px 24px rgba(0,0,0,.08);
      --nt-radius: 10px;

      /* Same fixed card sizing (like your courses page) */
      --nt-card-w: 381.5px;
      --nt-card-h: 426.4px;
      --nt-media-h: 240px;
    }
    body{background:#f6f7fb}

    .nt-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .nt-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .nt-title{font-weight:900;letter-spacing:.2px;margin:0;display:flex;align-items:center;gap:10px;}
    .nt-title i{color:var(--nt-accent)}

    .nt-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .nt-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--nt-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .nt-toolbar .form-control{min-width:100%}
      .nt-head{flex-direction:column;align-items:stretch}
    }

    /* Grid */
    /* Grid (3 / 2 / 1) */
.nt-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:25px;
  align-items:stretch;
}

/* Tablet -> 2 cards */
@media (max-width: 992px){
  .nt-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
}

/* Mobile -> 1 card */
@media (max-width: 576px){
  .nt-grid{ grid-template-columns:1fr; }
}


    /* Card */
    .nt-card{
      width: 100%;
      height:var(--nt-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--nt-border);
      border-radius:var(--nt-radius);
      background:#fff;
      box-shadow:var(--nt-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .nt-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    /* ✅ robust media fallback (prevents broken image icon + big alt text) */
    .nt-media{
  width:100%;
  height:var(--nt-media-h);
  flex:0 0 auto;
  background:var(--nt-accent);
  position:relative;
  overflow:hidden;
  user-select:none;

  /* ✅ cover image support */
  background-size:cover;
  background-position:center;
  background-repeat:no-repeat;
}

/* ✅ when cover exists, hide fallback text */
.nt-media.has-cover .nt-fallback{ display:none; }

    .nt-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index:1;
    }
    .nt-media .nt-fallback{
      position:absolute;
      inset:0;
      z-index:0;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:0 18px;
      text-align:center;
      line-height:1.15;
      color:#fff;
      font-weight:900;
      font-size:28px;
      letter-spacing:.3px;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }

    .nt-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .nt-h{
      font-size:20px;line-height:1.25;font-weight:900;margin:0 0 10px 0;color:#0f172a;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
      overflow-wrap:anywhere;word-break:break-word;
    }

    .nt-meta{
      display:flex;flex-direction:column;gap:6px;
      margin-bottom:10px;color:#334155;font-weight:800;font-size:13px;
    }
    .nt-meta .rowx{display:flex;align-items:center;gap:8px;min-height:18px;}
    .nt-meta i{width:16px;text-align:center;color:#64748b;}

    .nt-p{
      margin:0;color:#475569;font-size:15px;line-height:1.7;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
      overflow-wrap:anywhere;word-break:break-word;hyphens:auto;
    }

    .nt-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .nt-pill{
      position:absolute;left:12px;top:12px;z-index:3; /* ✅ above image */
      padding:6px 10px;border-radius:999px;
      font-size:12px;font-weight:900;
      background:rgba(0,0,0,.55);color:#fff;
      backdrop-filter: blur(6px);
    }

    .nt-link{position:absolute;inset:0;z-index:4;border-radius:var(--nt-radius);}

    /* Skeletons */
    .nt-skel{
      width: 100%;
      height:var(--nt-card-h);
      border:1px solid var(--nt-border);
      border-radius:var(--nt-radius);
      background:#fff;
      box-shadow:var(--nt-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .nt-skel .m{
      height:var(--nt-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .nt-skel .b{padding:18px;flex:1}
    .nt-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .nt-skel .l.w70{width:70%}
    .nt-skel .l.w95{width:95%}
    .nt-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .nt-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-nt{
      border-radius:12px;padding:10px 16px;border:1px solid var(--nt-border);
      background:#fff;font-weight:900;
    }
    .btn-nt:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .nt-empty{text-align:center;color:#64748b;padding:40px 10px;}
  </style>
</head>
<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <div class="nt-wrap">
    <div class="nt-head">
      <div>
        {{-- ✅ Icon before heading --}}
        <h2 class="nt-title"><i class="fa-solid fa-bullhorn"></i> Notices</h2>
        <div class="text-muted" style="font-size:13px;">Browse all published notices.</div>
      </div>

      <div class="nt-toolbar">
        <input id="ntSearch" class="form-control" type="search" placeholder="Search notices (title/slug/body)..." />
      </div>
    </div>

    <div class="nt-grid" id="ntGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="nt-skel">
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

    <div class="nt-footer">
      <button id="ntLoadMore" class="btn btn-nt d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="ntEmpty" class="nt-empty d-none">
      <div style="font-size:34px; line-height:1;">📢</div>
      <div class="mt-2" style="font-weight:900; font-size:18px;">No notices found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      // ✅ API -> NoticeController@publicIndex
      const API_INDEX = @json(url('/api/public/notices'));

      // ✅ Your route is: /notices/view/{uuid}
      const VIEW_BASE = @json(url('/notices/view'));

      const grid     = document.getElementById('ntGrid');
      const btnMore  = document.getElementById('ntLoadMore');
      const emptyBox = document.getElementById('ntEmpty');
      const qInput   = document.getElementById('ntSearch');

      let page = 1;
      let lastPage = 1;
      let loading = false;
      let q = '';

      // ✅ safe HTML output (for text nodes)
      const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[m]));

      // for attributes
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

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="nt-skel">
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
        grid.querySelectorAll('.nt-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const pillText = (it) => {
        if (Number(it.is_featured_home || 0) === 1) return 'Featured';
        const dept = (it.department_title || '').toString().trim();
        return dept ? dept : 'Notice';
      };

      const attachmentsCount = (it) => {
        if (Array.isArray(it.attachments)) return it.attachments.length;
        if (Array.isArray(it.attachments_json)) return it.attachments_json.length;
        return 0;
      };

      // ✅ force "......" after fixed characters
      const excerptText = (it) => {
        const body = it.body ? stripHtml(it.body) : '';
        const MAX_CHARS = 95; // tune as you like

        if (!body) return '';
        if (body.length <= MAX_CHARS) return body;

        let cut = body.slice(0, MAX_CHARS).trim().replace(/[,\.;:\-\s]+$/g, '');
        return cut + '......';
      };
const escCssUrl = (u) => {
  u = String(u || '').trim();
  if (!u) return '';
  return encodeURI(u)
    .replace(/'/g, '%27')
    .replace(/"/g, '%22')
    .replace(/\(/g, '%28')
    .replace(/\)/g, '%29')
    .replace(/#/g, '%23');
};

      const cardHtml = (item) => {
  const titleRaw = item.title || 'Notice';

  // ✅ redirect to /notices/view/{uuid}
  const uuid = item.uuid ? String(item.uuid) : '';

  // fallback to slug/id if uuid missing (still goes into {uuid} param)
  const slug = item.slug ? String(item.slug) : '';
  const id   = (item.id !== null && item.id !== undefined) ? String(item.id) : '';
  const identifier = uuid || slug || id;

  const href = identifier ? (VIEW_BASE + '/' + encodeURIComponent(identifier)) : '#';

  const cover = item.cover_image_url ? String(item.cover_image_url).trim() : '';
  const coverCss = escCssUrl(cover);

  const dept = (item.department_title || '').toString().trim();
  const created = fmtDate(item.publish_at || item.created_at || null);
  const att = attachmentsCount(item);

  const excerpt = excerptText(item); // your function already adds "......" in the updated version

  const mediaClass = coverCss ? 'has-cover' : '';
  const mediaStyle = coverCss ? `style="background-image:url('${coverCss}');"` : '';

  return `
    <div class="nt-card">
      <div class="nt-pill">${escapeHtml(pillText(item))}</div>

      <div class="nt-media ${mediaClass}" ${mediaStyle}>
        <div class="nt-fallback">Notice</div>
      </div>

      <div class="nt-body">
        <div class="nt-h">${escapeHtml(titleRaw)}</div>

        <div class="nt-meta">
          <div class="rowx">
            <i class="fa-solid fa-building-columns"></i>
            <span>${dept ? escapeHtml(dept) : 'General'}</span>
          </div>

          <div class="rowx">
            <i class="fa-regular fa-calendar"></i>
            <span>${created ? escapeHtml(created) : '—'}</span>
          </div>

          <div class="rowx">
            <i class="fa-solid fa-paperclip"></i>
            <span>${att ? ('Attachments: ' + att) : 'No attachments'}</span>
          </div>

          <div class="rowx">
            <i class="fa-regular fa-eye"></i>
            <span>${(item.views_count !== null && item.views_count !== undefined) ? ('Views: ' + item.views_count) : '—'}</span>
          </div>
        </div>

        <p class="nt-p">${excerpt ? escapeHtml(excerpt) : ''}</p>

        <div class="nt-date">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
          <span>Open notice</span>
        </div>
      </div>

      ${identifier
        ? `<a class="nt-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
        : `<div class="nt-link" title="Missing identifier"></div>`
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

          // ✅ only published & visible window
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
          console.error('Notices load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="nt-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:900; font-size:18px;">Failed to load notices</div>
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
