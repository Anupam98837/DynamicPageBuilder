{{-- resources/views/public/courses/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Courses</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --cs-accent: var(--primary-color, #9E363A);
      --cs-border: rgba(0,0,0,.08);
      --cs-shadow: 0 10px 24px rgba(0,0,0,.08);
      --cs-radius: 10px;

      /* Same fixed card sizing (like your gallery / placements page style) */
      --cs-card-w: 381.5px;
      --cs-card-h: 426.4px;
      --cs-media-h: 240px;
    }
    body{background:#f6f7fb}

    .cs-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .cs-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .cs-title{font-weight:900;letter-spacing:.2px;margin:0;}

    .cs-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .cs-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--cs-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .cs-toolbar .form-control{min-width:100%}
      .cs-head{flex-direction:column;align-items:stretch}
    }

    /* Grid */
    /* Grid (3 / 2 / 1) */
.cs-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:25px;
  align-items:stretch;
}

/* Tablet -> 2 cards */
@media (max-width: 992px){
  .cs-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
}

/* Mobile -> 1 card */
@media (max-width: 576px){
  .cs-grid{ grid-template-columns:1fr; }
}

    @media (max-width:420px){
      :root{ --cs-card-w: 100%; }
      .cs-grid{grid-template-columns:1fr;}
    }
    @media (max-width:576px){
      :root{ --cs-media-h: 210px; }
    }

    /* Card */
    .cs-card{
      width: 100%;
      height:var(--cs-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--cs-border);
      border-radius:var(--cs-radius);
      background:#fff;
      box-shadow:var(--cs-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .cs-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    .cs-media{
      width:100%;
      height:var(--cs-media-h);
      flex:0 0 auto;
      background:var(--cs-accent);
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
    .cs-media img{width:100%;height:100%;object-fit:cover;display:block;}
    .cs-media .cs-fallback{
      padding:0 18px;text-align:center;line-height:1.15;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .cs-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .cs-h{
      font-size:22px;line-height:1.25;font-weight:900;margin:0 0 10px 0;color:#0f172a;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    }

    .cs-meta{
      display:flex;flex-direction:column;gap:6px;
      margin-bottom:10px;color:#334155;font-weight:800;font-size:13px;
    }
    .cs-meta .rowx{display:flex;align-items:center;gap:8px;min-height:18px;}
    .cs-meta i{width:16px;text-align:center;color:#64748b;}

    .cs-p{
      margin:0;color:#475569;font-size:15px;line-height:1.7;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .cs-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .cs-pill{
      position:absolute;left:12px;top:12px;z-index:1;
      padding:6px 10px;border-radius:999px;
      font-size:12px;font-weight:900;
      background:rgba(0,0,0,.55);color:#fff;
      backdrop-filter: blur(6px);
    }

    .cs-link{position:absolute;inset:0;z-index:2;border-radius:var(--cs-radius);}

    /* Skeletons */
    .cs-skel{
      width:100%;
      height:var(--cs-card-h);
      border:1px solid var(--cs-border);
      border-radius:var(--cs-radius);
      background:#fff;
      box-shadow:var(--cs-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .cs-skel .m{
      height:var(--cs-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .cs-skel .b{padding:18px;flex:1}
    .cs-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .cs-skel .l.w70{width:70%}
    .cs-skel .l.w95{width:95%}
    .cs-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .cs-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-cs{
      border-radius:12px;padding:10px 16px;border:1px solid var(--cs-border);
      background:#fff;font-weight:900;
    }
    .btn-cs:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .cs-empty{text-align:center;color:#64748b;padding:40px 10px;}

            .cs-title i {
color: var(--cs-accent);
}
  </style>
</head>
<body>

  <div class="cs-wrap">
    <div class="cs-head">
      <div>
        <h2 class="cs-title"><i class="fa-solid fa-bullhorn"></i> Courses</h2>
        <div class="text-muted" style="font-size:13px;">Browse all published programs & courses.</div>
      </div>

      <div class="cs-toolbar">
        <input id="csSearch" class="form-control" type="search" placeholder="Search course (title/summary/career scope)..." />
      </div>
    </div>

    <div class="cs-grid" id="csGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="cs-skel">
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

    <div class="cs-footer">
      <button id="csLoadMore" class="btn btn-cs d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="csEmpty" class="cs-empty d-none">
      <div style="font-size:34px; line-height:1;">🎓</div>
      <div class="mt-2" style="font-weight:900; font-size:18px;">No courses found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      // ✅ API -> CourseController@publicIndex
      const API_INDEX = @json(url('/api/public/courses'));

      // ✅ View route (uuid)
      const VIEW_BASE = @json(url('/courses/view'));

      const grid     = document.getElementById('csGrid');
      const btnMore  = document.getElementById('csLoadMore');
      const emptyBox = document.getElementById('csEmpty');
      const qInput   = document.getElementById('csSearch');

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
            <div class="cs-skel">
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
        grid.querySelectorAll('.cs-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const pillText = (it) => {
        if (it && it.is_featured_home) return 'Featured';
        const lvl = (it?.program_level || '').toString().toUpperCase();
        return lvl ? lvl : 'Course';
      };

      const metaLine = (it) => {
        const lvl  = (it?.program_level || '').toString();
        const type = (it?.program_type || '').toString();
        const mode = (it?.mode || '').toString();

        const parts = [];
        if (lvl) parts.push(lvl.toUpperCase());
        if (type) parts.push(type);
        if (mode) parts.push(mode);

        return parts.join(' • ');
      };

      const durationLine = (it) => {
        const v = (it?.duration_value !== null && it?.duration_value !== undefined) ? Number(it.duration_value) : null;
        const u = (it?.duration_unit || '').toString();
        if (!v || v <= 0) return '';
        return `${v} ${u || 'months'}`;
      };

      const cardHtml = (item) => {
        const title = item.title || 'Course';
        const uuid  = item.uuid ? String(item.uuid) : '';
        const href  = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

        const cover = item.cover_image_url || null;
        const summary = item.summary ? stripHtml(item.summary) : '';
        const bodyTxt  = item.body ? stripHtml(item.body) : '';
        const career   = item.career_scope ? stripHtml(item.career_scope) : '';

        const text = summary || career || bodyTxt;
        const excerpt = text.length > 220 ? (text.slice(0, 220).trim() + '...') : text;

        const created = fmtDate(item.created_at || null);

        const meta = metaLine(item);
        const dur  = durationLine(item);
        const credits = (item.credits !== null && item.credits !== undefined && item.credits !== '')
          ? String(item.credits)
          : '';

        return `
          <div class="cs-card">
            <div class="cs-pill">${escAttr(pillText(item))}</div>

            <div class="cs-media">
              ${cover
                ? `<img src="${cover}" alt="${escAttr(title)}" loading="lazy" />`
                : `<div class="cs-fallback">${escAttr(title)}</div>`
              }
            </div>

            <div class="cs-body">
              <div class="cs-h">${title}</div>

              <div class="cs-meta">
                <div class="rowx">
                  <i class="fa-solid fa-layer-group"></i>
                  <span>${meta ? escAttr(meta) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-regular fa-clock"></i>
                  <span>${dur ? escAttr(dur) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-solid fa-graduation-cap"></i>
                  <span>${credits ? ('Credits: ' + escAttr(credits)) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-solid fa-file-lines"></i>
                  <span>${item.syllabus_url_full ? 'Syllabus available' : '—'}</span>
                </div>
              </div>

              <p class="cs-p">${excerpt || ''}</p>

              <div class="cs-date">
                <i class="fa-regular fa-calendar"></i>
                <span>Created: ${created || '—'}</span>
              </div>
            </div>

            ${uuid
              ? `<a class="cs-link" href="${href}" aria-label="Open ${escAttr(title)}"></a>`
              : `<div class="cs-link" title="Missing UUID"></div>`
            }
          </div>
        `;
      };

      // ✅ Extract items from multiple possible API shapes
      const extractItems = (json) => {
        if (!json) return [];

        // common: { data: [...] }
        if (Array.isArray(json.data)) return json.data;

        // common: { data: { data: [...] } } (nested)
        if (json.data && Array.isArray(json.data.data)) return json.data.data;

        // resource-ish: { items: [...] }
        if (Array.isArray(json.items)) return json.items;

        // paginator-ish: { ... , data: [...] } already handled; but sometimes root itself is array
        if (Array.isArray(json)) return json;

        return [];
      };

      // ✅ Extract last_page from multiple possible API shapes
      const extractLastPage = (json) => {
        if (!json) return 1;

        // your custom: { pagination: { last_page } }
        if (json.pagination && json.pagination.last_page) return Number(json.pagination.last_page) || 1;

        // laravel paginator root: { last_page }
        if (json.last_page) return Number(json.last_page) || 1;

        // resource meta: { meta: { last_page } } OR { data: { meta: { last_page } } }
        if (json.meta && json.meta.last_page) return Number(json.meta.last_page) || 1;
        if (json.data && json.data.meta && json.data.meta.last_page) return Number(json.data.meta.last_page) || 1;

        // fallback
        return 1;
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

          // ❌ removed: visible_now=1 (it can filter out everything if your API doesn't implement it)

          const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error('HTTP ' + res.status);

          const json = await res.json();

          const items = extractItems(json);
          lastPage = extractLastPage(json);

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
          console.error('Courses load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="cs-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:900; font-size:18px;">Failed to load courses</div>
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
