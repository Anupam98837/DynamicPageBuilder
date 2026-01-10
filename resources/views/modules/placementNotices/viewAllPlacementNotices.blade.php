{{-- resources/views/public/placementNotices/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Placement Notices</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --pn-accent: var(--primary-color, #9E363A);
      --pn-border: rgba(0,0,0,.08);
      --pn-shadow: 0 10px 24px rgba(0,0,0,.08);
      --pn-radius: 10px;

      --pn-card-h: 426.4px;
      --pn-media-h: 240px;
    }
    body{background:#f6f7fb}

    .pn-wrap{max-width:1140px;margin:24px auto 56px;padding:0 12px;}
    .pn-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .pn-title{font-weight:800;letter-spacing:.2px;margin:0;}

    .pn-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .pn-toolbar .form-control{
      height:42px;border-radius:10px;border:1px solid var(--pn-border);
      box-shadow:none;min-width:280px;
    }
    @media (max-width:576px){
      .pn-toolbar .form-control{min-width:100%}
      .pn-head{flex-direction:column;align-items:stretch}
    }

    /* Grid (3 / 2 / 1) */
    .pn-grid{
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:25px;
      align-items:stretch;
    }
    @media (max-width: 992px){
      .pn-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px){
      .pn-grid{ grid-template-columns:1fr; }
      :root{ --pn-media-h: 210px; }
    }

    /* Card */
    .pn-card{
      width:100%;
      height:var(--pn-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border:1px solid var(--pn-border);
      border-radius:var(--pn-radius);
      background:#fff;
      box-shadow:var(--pn-shadow);
      overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .pn-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.10);}

    .pn-media{
      width:100%;
      height:var(--pn-media-h);
      flex:0 0 auto;
      background:var(--pn-accent);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .pn-media .pn-fallback{
      position:absolute; inset:0;
      display:flex; align-items:center; justify-content:center;
      padding:0 18px;
      text-align:center;
      line-height:1.15;
      color:#fff;
      font-weight:900;
      font-size:28px;
      letter-spacing:.3px;
      z-index:0;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    .pn-media img{
      position:absolute; inset:0;
      width:100%; height:100%;
      object-fit:cover;
      display:block;
      z-index:1;
    }

    .pn-body{
      padding:18px 18px 16px;
      display:flex;
      flex-direction:column;
      flex:1 1 auto;
      min-height:0;
    }

    .pn-h{
      font-size:22px;line-height:1.25;font-weight:900;margin:0 0 10px 0;color:#0f172a;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    }

    .pn-meta{
      display:flex;
      flex-direction:column;
      gap:6px;
      margin-bottom:10px;
      color:#334155;
      font-weight:700;
      font-size:13px;
    }
    .pn-meta .rowx{display:flex;align-items:center;gap:8px;min-height:18px;}
    .pn-meta i{width:16px;text-align:center;color:#64748b;}

    .pn-p{
      margin:0;color:#475569;font-size:15px;line-height:1.7;
      display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    .pn-date{
      margin-top:auto;
      color:#94a3b8;
      font-size:13px;
      padding-top:12px;
      display:flex;
      align-items:center;
      gap:6px;
    }

    .pn-pill{
      position:absolute;
      left:12px;
      top:12px;
      z-index:3;
      padding:6px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:900;
      background:rgba(0,0,0,.55);
      color:#fff;
      backdrop-filter: blur(6px);
    }

    .pn-link{position:absolute;inset:0;z-index:4;border-radius:var(--pn-radius);}

    /* Skeletons */
    .pn-skel{
      width:100%;
      height:var(--pn-card-h);
      border:1px solid var(--pn-border);
      border-radius:var(--pn-radius);
      background:#fff;
      box-shadow:var(--pn-shadow);
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .pn-skel .m{
      height:var(--pn-media-h);
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .pn-skel .b{padding:18px;flex:1}
    .pn-skel .l{
      height:16px;margin:10px 0;border-radius:8px;
      background:linear-gradient(90deg,#eee,#f6f6f6,#eee);
      background-size:200% 100%;
      animation:sk 1.1s infinite;
    }
    .pn-skel .l.w70{width:70%}
    .pn-skel .l.w95{width:95%}
    .pn-skel .l.w85{width:85%}
    @keyframes sk{0%{background-position:0 0}100%{background-position:200% 0}}

    .pn-footer{display:flex;justify-content:center;margin-top:22px;}
    .btn-pn{
      border-radius:12px;padding:10px 16px;border:1px solid var(--pn-border);
      background:#fff;font-weight:800;
    }
    .btn-pn:hover{border-color:rgba(0,0,0,.14);background:#fff;}

    .pn-empty{text-align:center;color:#64748b;padding:40px 10px;}

    .pn-title i {
color: var(--pn-accent);
}
  </style>
</head>
<body>

  <div class="pn-wrap">
    <div class="pn-head">
      <div>
        <h2 class="pn-title"><i class="fa-solid fa-bullhorn"></i> Placement Notices</h2>
        <div class="text-muted" style="font-size:13px;">Latest placement opportunities & updates.</div>
      </div>

      <div class="pn-toolbar">
        <input id="pnSearch" class="form-control" type="search" placeholder="Search notices (title/role/eligibility)..." />
      </div>
    </div>

    <div class="pn-grid" id="pnGrid">
      {{-- skeletons --}}
      @for($i=0; $i<4; $i++)
        <div class="pn-skel">
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

    <div class="pn-footer">
      <button id="pnLoadMore" class="btn btn-pn d-none">
        <i class="fa-solid fa-rotate me-2"></i>Load more
      </button>
    </div>

    <div id="pnEmpty" class="pn-empty d-none">
      <div style="font-size:34px; line-height:1;">📌</div>
      <div class="mt-2" style="font-weight:900; font-size:18px;">No placement notices found</div>
      <div class="mt-1">Try a different search term.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      const API_INDEX = @json(url('/api/public/placement-notices'));
      const VIEW_BASE = @json(url('/placement-notices/view'));

      const grid     = document.getElementById('pnGrid');
      const btnMore  = document.getElementById('pnLoadMore');
      const emptyBox = document.getElementById('pnEmpty');
      const qInput   = document.getElementById('pnSearch');

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

      const fmtMoney = (v) => {
        if (v === null || v === undefined || v === '') return '';
        const n = Number(v);
        if (Number.isNaN(n)) return String(v);
        return n.toLocaleString('en-IN');
      };

      const setSkeletons = (n=4) => {
        const s = [];
        for (let i=0;i<n;i++){
          s.push(`
            <div class="pn-skel">
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
        grid.querySelectorAll('.pn-skel').forEach(el => el.remove());
      };

      const setEmpty = (isEmpty) => emptyBox.classList.toggle('d-none', !isEmpty);

      const setMore = () => {
        const show = page < lastPage;
        btnMore.classList.toggle('d-none', !show);
        btnMore.disabled = loading;
      };

      const pillText = (it) => {
        const last = it.last_date_to_apply ? fmtDate(it.last_date_to_apply) : '';
        if (last) return 'Apply by ' + last;
        return it.is_featured_home ? 'Featured' : 'Placement';
      };

      const recruiterText = (it) => {
        const name = it.recruiter_name || it.recruiter_title || '';
        const comp = it.recruiter_company_name || '';
        if (name && comp) return `${name} • ${comp}`;
        return name || comp || '';
      };

      const cardHtml = (item) => {
        const title = item.title || 'Placement Notice';
        const role  = item.role_title || '';
        const ctc   = (item.ctc !== null && item.ctc !== undefined && item.ctc !== '') ? fmtMoney(item.ctc) : '';
        const elig  = item.eligibility ? stripHtml(item.eligibility) : '';
        const desc  = item.description ? stripHtml(item.description) : '';
        const text  = (desc || elig);
        const excerpt = text.length > 220 ? (text.slice(0, 220).trim() + '...') : text;

        const created = fmtDate(item.created_at || null);
        const applyBy = fmtDate(item.last_date_to_apply || null);

        const uuid = item.uuid ? String(item.uuid) : '';
        const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

        const banner = item.banner_image_full_url || item.banner_image_url || '';
        const recLine = recruiterText(item);

        return `
          <div class="pn-card">
            <div class="pn-pill">${escAttr(pillText(item))}</div>

            <div class="pn-media">
              <div class="pn-fallback">${escAttr(title)}</div>

              ${banner ? `
                <img class="pn-img"
                     src="${escAttr(banner)}"
                     alt="${escAttr(title)}"
                     loading="lazy" />
              ` : ``}
            </div>

            <div class="pn-body">
              <div class="pn-h">${title}</div>

              <div class="pn-meta">
                <div class="rowx">
                  <i class="fa-solid fa-briefcase"></i>
                  <span>${role ? escAttr(role) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-solid fa-building"></i>
                  <span>${recLine ? escAttr(recLine) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-solid fa-money-bill-wave"></i>
                  <span>${ctc ? ('CTC: ' + escAttr(ctc)) : '—'}</span>
                </div>

                <div class="rowx">
                  <i class="fa-regular fa-calendar"></i>
                  <span>${applyBy ? ('Last date: ' + escAttr(applyBy)) : '—'}</span>
                </div>
              </div>

              <p class="pn-p">${excerpt || ''}</p>

              <div class="pn-date">
                <i class="fa-regular fa-clock"></i>
                <span>Created: ${created || '—'}</span>
              </div>
            </div>

            ${uuid
              ? `<a class="pn-link" href="${href}" aria-label="Open ${escAttr(title)}"></a>`
              : `<div class="pn-link" title="Missing UUID"></div>`
            }
          </div>
        `;
      };

      // ✅ image load/error handler (no broken icon)
      const bindCardImages = (rootEl) => {
        rootEl.querySelectorAll('img.pn-img').forEach(img => {
          const media = img.closest('.pn-media');
          const fallback = media ? media.querySelector('.pn-fallback') : null;

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
          console.error('Placement notices load error:', e);
          removeSkeletons();
          if (!grid.children.length) {
            grid.innerHTML = `
              <div class="pn-empty">
                <div style="font-size:34px; line-height:1;">⚠️</div>
                <div class="mt-2" style="font-weight:900; font-size:18px;">Failed to load placement notices</div>
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
