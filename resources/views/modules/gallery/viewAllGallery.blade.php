{{-- resources/views/landing/gallery-all.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Gallery</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    .gxa-wrap{
      --gxa-brand: #9E363A;               /* Deep wine red */
      --gxa-brand-rgb: 158, 54, 58;
      --gxa-accent: #C94B50;              /* Warm accent red */
      --gxa-accent-rgb: 201, 75, 80;
      --gxa-ink: #0f172a;
      --gxa-muted: #64748b;
      --gxa-bg: var(--page-bg, #ffffff);
      --gxa-card: var(--surface, #ffffff);
      --gxa-line: var(--line-soft, rgba(15,23,42,.10));
      --gxa-shadow: 0 10px 24px rgba(2,6,23,.08);
      --gxa-footer-safe: 96px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px var(--gxa-footer-safe);
      background: transparent;
      position: relative;
      overflow: visible;
      isolation: isolate;
    }

    /* Header */
    .gxa-head{
      background: var(--gxa-card);
      border: 1px solid var(--gxa-line);
      border-radius: 16px;
      box-shadow: var(--gxa-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .gxa-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--gxa-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .gxa-title i{ color: var(--gxa-brand); }

    .gxa-sub{
      margin: 6px 0 0;
      color: var(--gxa-muted);
      font-size: 14px;
    }

    .gxa-tools{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: wrap;
    }

    .gxa-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .gxa-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--gxa-muted);
      pointer-events:none;
    }
    .gxa-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      outline: none;
    }
    .gxa-search input:focus{
      border-color: rgba(var(--gxa-brand-rgb), .55);
      box-shadow: 0 0 0 4px rgba(var(--gxa-brand-rgb), .18);
    }

    .gxa-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .gxa-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--gxa-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .gxa-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--gxa-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .gxa-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      outline: none;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .gxa-select select:focus{
      border-color: rgba(var(--gxa-brand-rgb), .55);
      box-shadow: 0 0 0 4px rgba(var(--gxa-brand-rgb), .18);
    }

    .gxa-btn{
      height: 42px;
      border-radius: 999px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      padding: 0 16px;
      font-weight: 900;
      display:inline-flex;
      align-items:center;
      gap: 8px;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor: pointer;
      transition: .18s ease;
    }
    .gxa-btn:hover{ background: rgba(2,6,23,.03); }
    .gxa-btn--brand{
      border-color: rgba(var(--gxa-brand-rgb), .28);
      color: var(--gxa-brand);
      background: rgba(var(--gxa-brand-rgb), .06);
    }
    .gxa-btn--brand:hover{
      background: rgba(var(--gxa-brand-rgb), .10);
      border-color: rgba(var(--gxa-brand-rgb), .40);
    }

    @media (min-width: 992px){
      .gxa-head{ flex-wrap: nowrap; align-items: center; }
      .gxa-tools{ flex-wrap: nowrap; justify-content: flex-end; }
      .gxa-search{ min-width: 0; flex: 1 1 520px; }
      .gxa-select{ min-width: 0; flex: 0 1 320px; }
    }

    /* Album cards */
    .gxa-albums{
  display:grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
  align-items: stretch;
}

    .gxa-album{
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      background: #fff;
      border: 1px solid rgba(2,6,23,.06);
      box-shadow: 0 4px 12px rgba(2,6,23,.04);
      cursor: pointer;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
      will-change: transform;
      display:flex;
      flex-direction: column;
      min-height: 100%;
    }
    .gxa-album:hover{
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      border-color: rgba(var(--gxa-brand-rgb), .28);
    }

    .gxa-album__media{
  position: relative;
  height: clamp(170px, 19vw, 210px);
  overflow: hidden;
  background:
    linear-gradient(135deg, rgba(var(--gxa-brand-rgb), .14), rgba(var(--gxa-accent-rgb), .10)),
    #f8fafc;
}

    .gxa-album__slides{
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
      background:
        linear-gradient(135deg, rgba(var(--gxa-brand-rgb), .12), rgba(var(--gxa-accent-rgb), .08)),
        #f8fafc;
    }

    .gxa-album__slide{
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
      opacity: 0;
      transform: scale(1.05);
      transition: opacity .85s ease, transform 1.05s ease;
      will-change: opacity, transform;
      pointer-events: none;
    }
    .gxa-album__slide.is-active{
      opacity: 1;
      transform: scale(1);
      z-index: 1;
    }

    .gxa-album__media img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
      transition: transform .4s ease;
    }

    .gxa-album__media::after{
      content:"";
      position:absolute;
      inset:auto 0 0 0;
      height: 38%;
      background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(2,6,23,.22) 62%, rgba(2,6,23,.48) 100%);
      pointer-events:none;
      z-index: 2;
    }

    /* Standalone overrides */
    .gxa-album--standalone .gxa-album__media {
      height: clamp(220px, 25vw, 360px);
    }
    .gxa-album--standalone .gxa-album__media::after {
      display: none;
    }
    .gxa-album--standalone:hover .gxa-album__media img {
      transform: scale(1.06);
    }

    .gxa-album__fallback{
      width: 100%;
      height: 100%;
      display:flex;
      align-items:center;
      justify-content:center;
      color: var(--gxa-brand);
      font-size: 38px;
      opacity: .8;
    }

    .gxa-album__count{
      position:absolute;
      right: 12px;
      top: 12px;
      background: linear-gradient(135deg, rgba(var(--gxa-brand-rgb), .96), rgba(var(--gxa-accent-rgb), .92));
      color: #fff;
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 11px;
      font-weight: 950;
      display:inline-flex;
      align-items:center;
      gap: 6px;
      backdrop-filter: blur(8px);
      z-index: 3;
    }

    .gxa-album__dots{
      position:absolute;
      left: 12px;
      bottom: 12px;
      display:flex;
      align-items:center;
      gap: 6px;
      z-index: 3;
    }
    .gxa-album__dot{
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: rgba(255,255,255,.45);
      border: 1px solid rgba(255,255,255,.24);
      transition: transform .22s ease, background .22s ease;
      box-shadow: 0 2px 10px rgba(0,0,0,.22);
    }
    .gxa-album__dot.is-active{
      background: #fff;
      transform: scale(1.15);
    }

    .gxa-album__body{
      padding: 14px 14px 15px;
      display:flex;
      flex-direction: column;
      gap: 10px;
      flex: 1 1 auto;
    }

    .gxa-album__row{
      display:flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items:center;
    }

    .gxa-pill{
      display:inline-flex;
      align-items:center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid var(--gxa-line);
      background: rgba(2,6,23,.03);
      color: var(--gxa-ink);
      font-size: 11.5px;
      font-weight: 900;
      max-width: 100%;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .gxa-pill--brand{
      color: var(--gxa-brand);
      border-color: rgba(var(--gxa-brand-rgb), .24);
      background: rgba(var(--gxa-brand-rgb), .08);
    }

    .gxa-album__title{
      margin: 0;
      color: var(--gxa-ink);
      font-size: 18px;
      line-height: 1.2;
      font-weight: 950;
      letter-spacing: .1px;
    }

    .gxa-album__desc{
      color: var(--gxa-muted);
      font-size: 13px;
      line-height: 1.45;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 58px;
    }

    .gxa-album__cta{
      margin-top: auto;
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 10px;
      padding-top: 4px;
    }

    .gxa-album__link{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      color: var(--gxa-brand);
      font-size: 13px;
      font-weight: 950;
    }

    /* Album header */
    .gxa-album-head{
      background: var(--gxa-card);
      border: 1px solid var(--gxa-line);
      border-radius: 12px;
      box-shadow: var(--gxa-shadow);
      padding: 16px 18px;
      margin-bottom: 16px;
      display:grid;
      grid-template-columns: minmax(0,1fr);
      gap: 10px;
    }

    .gxa-album-head__top{
      display:flex;
      flex-wrap: wrap;
      align-items:center;
      justify-content: space-between;
      gap: 10px;
    }

    .gxa-album-head__title{
      margin: 0;
      font-size: 26px;
      line-height: 1.15;
      color: var(--gxa-ink);
      font-weight: 950;
      letter-spacing: .1px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .gxa-album-head__title i{ color: var(--gxa-brand); }

    .gxa-album-head__desc{
      margin: 0;
      color: var(--gxa-muted);
      font-size: 14px;
      line-height: 1.5;
      white-space: pre-wrap;
    }

    /* Photos masonry */
    .gxa-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      grid-auto-rows: 10px;
      gap: 18px;
      align-items: start;
    }

    .gxa-item{
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      background: #fff;
      border: 1px solid rgba(2,6,23,.06);
      box-shadow: 0 4px 12px rgba(2,6,23,.04);
      cursor: pointer;
      user-select: none;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
      will-change: transform;
    }
    .gxa-item:hover{
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      border-color: rgba(var(--gxa-brand-rgb), .28);
    }

    .gxa-item img{
      width: 100%;
      height: auto;
      display:block;
    }

    .gxa-meta{
      position:absolute;
      left:0; right:0; bottom:0;
      padding: 10px 10px 9px;
      color: #fff;
      background: linear-gradient(180deg, rgba(2,6,23,0) 0%, rgba(2,6,23,.55) 28%, rgba(2,6,23,.82) 100%);
      pointer-events: none;
    }

    .gxa-meta__title{
      font-weight: 950;
      font-size: 13px;
      letter-spacing: .2px;
      line-height: 1.18;
      text-shadow: 0 2px 10px rgba(0,0,0,.35);
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .gxa-meta__desc{
      margin-top: 4px;
      font-size: 12px;
      opacity: .92;
      line-height: 1.25;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-shadow: 0 2px 10px rgba(0,0,0,.35);
    }

    .gxa-meta__tags{
      margin-top: 6px;
      display:flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .gxa-tag{
      font-size: 11px;
      font-weight: 950;
      padding: 5px 8px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.18);
      backdrop-filter: blur(6px);
      max-width: 100%;
      overflow:hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .gxa-tag.more{
      background: rgba(var(--gxa-accent-rgb), .28);
      border-color: rgba(var(--gxa-accent-rgb), .42);
    }

    .gxa-state{
      background: var(--gxa-card);
      border: 1px solid var(--gxa-line);
      border-radius: 16px;
      box-shadow: var(--gxa-shadow);
      padding: 18px;
      color: var(--gxa-muted);
      text-align:center;
      position: relative;
      z-index: 0;
      margin-bottom: 18px;
    }

    .gxa-state .gxa-spin{
      width: 42px;
      height: 42px;
      margin: 0 auto 10px;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius: 999px;
      border: 1px solid var(--gxa-line);
      background: rgba(var(--gxa-brand-rgb), .05);
      box-shadow: 0 10px 22px rgba(2,6,23,.08);
      color: var(--gxa-brand);
      font-size: 18px;
    }

    .gxa-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }

    .gxa-pagination .gxa-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }

    .gxa-pagebtn{
      border:1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
      transition: .18s ease;
    }
    .gxa-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .gxa-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .gxa-pagebtn.active{
      background: rgba(var(--gxa-brand-rgb), .12);
      border-color: rgba(var(--gxa-brand-rgb), .35);
      color: var(--gxa-brand);
    }

    /* Lightbox */
    .gxa-lb{
      position: fixed;
      inset: 0;
      background: rgba(2,6,23,.72);
      display:none;
      align-items:center;
      justify-content:center;
      z-index: 9999;
      padding: 18px;
    }
    .gxa-lb.show{ display:flex; }

    .gxa-lb__inner{
      max-width: min(1100px, 96vw);
      max-height: min(86vh, 900px);
      background: #0b1220;
      border: 1px solid rgba(255,255,255,.12);
      box-shadow: 0 22px 60px rgba(0,0,0,.45);
      position: relative;
      display:flex;
      flex-direction: column;
      overflow:hidden;
      border-radius: 14px;
    }

    .gxa-lb__img{
      max-width: min(1100px, 96vw);
      max-height: min(72vh, 820px);
      display:block;
      object-fit: contain;
    }

    .gxa-lb__meta{
      border-top: 1px solid rgba(255,255,255,.10);
      padding: 12px 14px 14px;
      color: rgba(255,255,255,.92);
      background: rgba(255,255,255,.02);
    }

    .gxa-lb__title{
      font-weight: 950;
      font-size: 15px;
      letter-spacing: .2px;
      color:#fff;
      margin: 0 0 6px;
    }

    .gxa-lb__desc{
      margin: 0 0 10px;
      font-size: 13px;
      line-height: 1.35;
      color: rgba(255,255,255,.86);
      white-space: pre-wrap;
    }

    .gxa-lb__tags{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .gxa-lb__tag{
      font-size: 12px;
      font-weight: 900;
      padding: 7px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.14);
    }

    .gxa-lb__close{
      position:absolute;
      top: 10px;
      right: 10px;
      width: 40px;
      height: 40px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.18);
      background: rgba(0,0,0,.35);
      color:#fff;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      z-index: 5;
      transition: .18s ease;
    }
    .gxa-lb__close:hover{
      background: rgba(var(--gxa-brand-rgb), .38);
      border-color: rgba(255,255,255,.24);
    }

    @media (max-width: 640px){
      .gxa-title{ font-size: 24px; }
      .gxa-search{ min-width: 220px; flex: 1 1 240px; }
      .gxa-select{ min-width: 220px; flex: 1 1 240px; }
      .gxa-lb__img{ max-height: min(66vh, 760px); }
      .gxa-wrap{ --gxa-footer-safe: 84px; }
      .gxa-album-head__title{ font-size: 22px; }
      .gxa-album__desc{ min-height: auto; }
    }
  </style>
</head>
<body>

  <div
    class="gxa-wrap"
    data-events-api="{{ url('/api/public/gallery-events') }}"
    data-event-show-api="{{ url('/api/public/gallery-events/__SHORTCODE__') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="gxa-head">
      <div>
        <h1 class="gxa-title"><i class="fa-regular fa-images"></i>Gallery</h1>
        <div class="gxa-sub" id="gxaSub">Browse event albums and open each album to view its photos.</div>
      </div>

      <div class="gxa-tools">
        <button id="gxaBack" class="gxa-btn gxa-btn--brand" type="button" style="display:none;">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back to Albums</span>
        </button>

        <div class="gxa-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="gxaSearch" type="search" placeholder="Search event title / description / shortcode…">
        </div>

        <div class="gxa-select" title="Filter by department">
          <i class="fa-solid fa-building-columns gxa-select__icon"></i>
          <select id="gxaDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down gxa-select__caret"></i>
        </div>
      </div>
    </div>

    {{-- Album cards --}}
    <section id="gxaAlbumSection">
      <div id="gxaAlbumGrid" class="gxa-albums" style="display:none;"></div>
    </section>

    {{-- Selected event photos --}}
    <section id="gxaPhotoSection" style="display:none;">
      <div class="gxa-album-head">
        <div class="gxa-album-head__top">
          <h2 class="gxa-album-head__title" id="gxaAlbumTitle">
            <i class="fa-solid fa-folder-open"></i>
            <span>Album</span>
          </h2>

          <div class="d-flex flex-wrap gap-2" id="gxaAlbumPills">
            <span class="gxa-pill gxa-pill--brand" id="gxaAlbumDate" style="display:none;"></span>
            <span class="gxa-pill" id="gxaAlbumCode" style="display:none;"></span>
            <span class="gxa-pill" id="gxaAlbumCount" style="display:none;"></span>
          </div>
        </div>

        <p class="gxa-album-head__desc" id="gxaAlbumDesc" style="display:none;"></p>
      </div>

      <div id="gxaGrid" class="gxa-grid" style="display:none;"></div>
    </section>

    <div id="gxaState" class="gxa-state" style="display:none;"></div>

    <div class="gxa-pagination">
      <div id="gxaPager" class="gxa-pager" style="display:none;"></div>
    </div>
  </div>

  {{-- Lightbox --}}
  <div id="gxaLb" class="gxa-lb" aria-hidden="true">
    <div class="gxa-lb__inner">
      <button class="gxa-lb__close" id="gxaLbClose" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <img id="gxaLbImg" class="gxa-lb__img" alt="Gallery image">

      <div class="gxa-lb__meta" id="gxaLbMeta" style="display:none;">
        <div class="gxa-lb__title" id="gxaLbTitle"></div>
        <div class="gxa-lb__desc" id="gxaLbDesc"></div>
        <div class="gxa-lb__tags" id="gxaLbTags"></div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__LANDING_GALLERY_ALL__) return;
    window.__LANDING_GALLERY_ALL__ = true;

    const root = document.querySelector('.gxa-wrap');
    if (!root) return;

    const EVENTS_API = root.getAttribute('data-events-api') || '/api/public/gallery-events';
    const EVENT_SHOW_API = root.getAttribute('data-event-show-api') || '/api/public/gallery-events/__SHORTCODE__';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      albumSection: $('gxaAlbumSection'),
      albumGrid: $('gxaAlbumGrid'),
      photoSection: $('gxaPhotoSection'),
      photoGrid: $('gxaGrid'),
      state: $('gxaState'),
      pager: $('gxaPager'),
      search: $('gxaSearch'),
      dept: $('gxaDept'),
      sub: $('gxaSub'),
      back: $('gxaBack'),

      albumTitle: $('gxaAlbumTitle'),
      albumDate: $('gxaAlbumDate'),
      albumCode: $('gxaAlbumCode'),
      albumCount: $('gxaAlbumCount'),
      albumDesc: $('gxaAlbumDesc'),

      lb: $('gxaLb'),
      lbImg: $('gxaLbImg'),
      lbClose: $('gxaLbClose'),
    };

    const state = {
      mode: 'albums', // albums | photos
      page: 1,
      lastPage: 1,
      total: 0,
      q: '',
      deptUuid: '',
      deptName: '',
      selectedEvent: null,
      perPageAlbums: 12,
      perPagePhotos: 18,
    };

    let deptByUuid = new Map();
    let deptByShortcode = new Map();
    let activeController = null;
    let albumSlideTimers = [];

    function esc(str){
      return (str ?? '').toString().replace(/[&<>"']/g, s => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[s]));
    }

    function escAttr(str){
      return (str ?? '').toString().replace(/"/g, '&quot;');
    }

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    function pick(obj, keys){
      for (const k of keys){
        const v = obj?.[k];
        if (v !== null && v !== undefined && String(v).trim() !== '') return v;
      }
      return '';
    }

    function normalizeTags(raw){
      let arr = [];

      if (Array.isArray(raw)){
        arr = raw.map(x => (x ?? '').toString().trim()).filter(Boolean);
      } else {
        const s = (raw ?? '').toString().trim();
        if (s){
          if (s.includes('|')) arr = s.split('|');
          else if (s.includes(',')) arr = s.split(',');
          else arr = s.split(/\s+/);
          arr = arr.map(x => x.replace(/^#+/,'').trim()).filter(Boolean);
        }
      }

      const seen = new Set();
      const out = [];
      for (const t of arr){
        const key = t.toLowerCase();
        if (seen.has(key)) continue;
        seen.add(key);
        out.push(t);
      }
      return out;
    }

    function tagsFromItem(it){
      const raw =
        it?.tags ??
        it?.tags_json ??
        it?.tag_list ??
        it?.keywords ??
        it?.categories ??
        it?.tag ??
        it?.meta?.tags ??
        it?.attachment?.tags;

      return normalizeTags(raw);
    }

    function renderTagChips(tags, max=3){
      const t = Array.isArray(tags) ? tags.filter(Boolean) : [];
      if (!t.length) return '';
      const shown = t.slice(0, max);
      const more = t.length - shown.length;

      let html = shown.map(x => `<span class="gxa-tag">${esc(x)}</span>`).join('');
      if (more > 0) html += `<span class="gxa-tag more">+${more}</span>`;
      return html;
    }

    function applyMasonry(){
      const grid = els.photoGrid;
      if (!grid || grid.style.display === 'none') return;

      const style = window.getComputedStyle(grid);
      const rowH = parseInt(style.getPropertyValue('grid-auto-rows'), 10) || 10;
      const gap  = parseInt(style.getPropertyValue('grid-row-gap'), 10) || 18;

      const items = grid.querySelectorAll('.gxa-item');
      items.forEach((item) => {
        item.style.gridRowEnd = 'auto';
        const h = item.getBoundingClientRect().height;
        const span = Math.ceil((h + gap) / (rowH + gap));
        item.style.gridRowEnd = `span ${Math.max(1, span)}`;
      });
    }

    function showLoading(message='Loading…'){
      hideAllContent();
      if (!els.state) return;
      els.state.style.display = '';
      els.state.innerHTML = `
        <div class="gxa-spin"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
        <div style="font-weight:900;color:var(--gxa-ink);">${esc(message)}</div>
        <div style="margin-top:6px;font-size:12.5px;opacity:.95;">Please wait…</div>
      `;
    }

    function showEmpty(title='Nothing found', desc=''){
      hideAllContent();
      if (!els.state) return;
      els.state.style.display = '';
      els.state.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-regular fa-folder-open"></i>
        </div>
        <div style="font-weight:900;color:var(--gxa-ink);">${esc(title)}</div>
        ${desc ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">${esc(desc)}</div>` : ''}
      `;
    }

    function hideState(){
      if (!els.state) return;
      els.state.style.display = 'none';
      els.state.innerHTML = '';
    }

    function clearAlbumSlideTimers(){
      albumSlideTimers.forEach(t => clearInterval(t));
      albumSlideTimers = [];
    }

    function hideAllContent(){
      clearAlbumSlideTimers();

      if (els.albumGrid) els.albumGrid.style.display = 'none';
      if (els.photoGrid) els.photoGrid.style.display = 'none';
      if (els.albumSection) els.albumSection.style.display = 'none';
      if (els.photoSection) els.photoSection.style.display = 'none';
      if (els.pager){
        els.pager.style.display = 'none';
        els.pager.innerHTML = '';
      }
    }

    async function fetchJson(url){
      if (activeController) activeController.abort();
      activeController = new AbortController();

      const res = await fetch(url, {
        headers: { 'Accept':'application/json' },
        signal: activeController.signal
      });

      const js = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(js?.message || ('Request failed: ' + res.status));
      return js;
    }

    function getUrlObj(){
      return new URL(window.location.href);
    }

    function extractDeptUuidFromUrl(){
      const url = getUrlObj();
      const direct = (url.searchParams.get('department') || url.searchParams.get('dept') || '').trim();
      if (direct) {
        // If it's a shortcode, resolve to uuid
        const lower = direct.toLowerCase();
        if (typeof deptByShortcode !== 'undefined' && deptByShortcode.has(lower)) {
          return deptByShortcode.get(lower).uuid;
        }
        return direct; // maybe it's already a uuid
      }

      const hay = url.search + ' ' + url.href;
      const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
      return m ? m[1] : '';
    }

    function extractEventFromUrl(){
      const url = getUrlObj();
      return (url.searchParams.get('event') || url.searchParams.get('album') || '').trim();
    }

    function syncUrl(){
      const url = getUrlObj();

      if (state.deptUuid) {
        let sc = '';
        if (typeof deptByUuid !== 'undefined' && deptByUuid.has(state.deptUuid)) {
          sc = deptByUuid.get(state.deptUuid).shortcode;
        }
        if (sc) {
          url.searchParams.set('dept', sc);
          url.searchParams.delete('department');
        } else {
          url.searchParams.set('department', state.deptUuid);
          url.searchParams.delete('dept');
        }
      } else {
        url.searchParams.delete('department');
        url.searchParams.delete('dept');
      }

      if (state.mode === 'photos' && state.selectedEvent?.shortcode){
        url.searchParams.set('event', state.selectedEvent.shortcode);
      } else {
        url.searchParams.delete('event');
        url.searchParams.delete('album');
      }

      history.replaceState({}, '', url.pathname + url.search + url.hash);
    }

    function setDeptSelection(uuid){
      const sel = els.dept;
      uuid = (uuid || '').toString().trim();

      if (!sel) return;

      if (!uuid){
        sel.value = '';
        state.deptUuid = '';
        state.deptName = '';
        return;
      }

      const meta = deptByUuid.get(uuid);
      if (!meta) return;

      sel.value = uuid;
      state.deptUuid = uuid;
      state.deptName = meta.title ?? '';
    }

    function updateUiContext(){
      if (!els.sub || !els.search || !els.back) return;

      if (state.mode === 'photos' && state.selectedEvent){
        const title = state.selectedEvent.title || 'Album';
        els.sub.textContent = state.deptName
          ? `${title} — ${state.deptName}`
          : `Viewing album: ${title}`;
        els.search.placeholder = 'Search within this album…';
        els.back.style.display = '';
      } else {
        els.sub.textContent = state.deptName
          ? `Browse event albums for ${state.deptName}`
          : 'Browse event albums and open each album to view its photos.';
        els.search.placeholder = 'Search event title / description / shortcode…';
        els.back.style.display = 'none';
      }
    }

    async function loadDepartments(){
      const sel = els.dept;
      if (!sel) return;

      sel.innerHTML = `
        <option value="">All Departments</option>
        <option value="__loading" disabled>Loading departments…</option>
      `;
      sel.value = '__loading';

      try{
        const res = await fetch(DEPT_API, { headers: { 'Accept':'application/json' } });
        const js = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(js?.message || ('HTTP ' + res.status));

        const items = Array.isArray(js?.data) ? js.data : [];
        const depts = items
          .map(d => ({
            uuid: (d?.uuid ?? '').toString().trim(),
            shortcode: (d?.short_name ?? d?.slug ?? '').toString().trim().toLowerCase(),
            title: (d?.title ?? d?.name ?? '').toString().trim(),
            active: (d?.active ?? 1),
          }))
          .filter(x => x.uuid && x.title && String(x.active) === '1');

        depts.sort((a,b) => a.title.localeCompare(b.title));
        deptByUuid = new Map(depts.map(d => [d.uuid, d]));
        deptByShortcode = new Map(depts.map(d => [d.shortcode, d]));

        sel.innerHTML = `<option value="">All Departments</option>` + depts
          .map(d => `<option value="${escAttr(d.uuid)}">${esc(d.title)}</option>`)
          .join('');

        sel.value = '';
      } catch (e){
        console.warn('Departments load failed:', e);
        sel.innerHTML = `<option value="">All Departments</option>`;
        sel.value = '';
      }
    }

    function buildAlbumsUrl(){
      const u = new URL(EVENTS_API, window.location.origin);
      u.searchParams.set('page', String(state.page));
      u.searchParams.set('per_page', String(state.perPageAlbums));

      if (state.q) u.searchParams.set('q', state.q);
      if (state.deptUuid) u.searchParams.set('department', state.deptUuid);

      return u.toString();
    }

    function buildEventPhotosUrl(shortcode){
      const endpoint = EVENT_SHOW_API.replace('__SHORTCODE__', encodeURIComponent(shortcode));
      const u = new URL(endpoint, window.location.origin);
      u.searchParams.set('page', String(state.page));
      u.searchParams.set('per_page', String(state.perPagePhotos));

      if (state.q) u.searchParams.set('q', state.q);
      if (state.deptUuid) u.searchParams.set('department', state.deptUuid);

      return u.toString();
    }

    function toArray(raw){
      if (Array.isArray(raw)) return raw;

      if (typeof raw === 'string'){
        const str = raw.trim();
        if (!str) return [];

        if ((str.startsWith('[') && str.endsWith(']')) || (str.startsWith('{') && str.endsWith('}'))){
          try{
            const parsed = JSON.parse(str);
            return Array.isArray(parsed) ? parsed : [parsed];
          }catch(_e){}
        }

        if (str.includes('|')) return str.split('|').map(x => x.trim()).filter(Boolean);
        if (str.includes(',')) return str.split(',').map(x => x.trim()).filter(Boolean);
        return [str];
      }

      if (raw && typeof raw === 'object') return [raw];
      return [];
    }

    function uniqueUrls(items){
      const seen = new Set();
      const out = [];

      for (const raw of items){
        const url = normalizeUrl(raw);
        if (!url) continue;
        const key = url.toLowerCase();
        if (seen.has(key)) continue;
        seen.add(key);
        out.push(url);
      }

      return out;
    }

    function extractAlbumImages(item){
      const collected = [];

      const pushMaybe = (value) => {
        if (!value) return;

        if (typeof value === 'string'){
          const u = normalizeUrl(value);
          if (u) collected.push(u);
          return;
        }

        if (Array.isArray(value)){
          value.forEach(pushMaybe);
          return;
        }

        if (typeof value === 'object'){
          const direct = pick(value, [
            'image_url','image_full_url','cover_image_url','cover_image',
            'url','src','image','full_url','file_url','path','thumbnail_url'
          ]);
          if (direct){
            const u = normalizeUrl(direct);
            if (u) collected.push(u);
          }

          const nestedArrays = [
            value.images, value.photos, value.media, value.attachments,
            value.gallery_images, value.preview_images, value.files
          ];

          nestedArrays.forEach(pushMaybe);
        }
      };

      pushMaybe(item?.cover_image_url);
      pushMaybe(item?.cover_image);
      pushMaybe(item?.event?.cover_image_url);
      pushMaybe(item?.event?.cover_image);

      [
        item?.preview_images,
        item?.images,
        item?.photos,
        item?.gallery_images,
        item?.media,
        item?.attachments,
        item?.event?.preview_images,
        item?.event?.images,
        item?.event?.photos,
        item?.event?.gallery_images,
        item?.event?.media,
        item?.event?.attachments,
      ].forEach(pushMaybe);

      return uniqueUrls(collected).slice(0, 8);
    }

    function initAlbumSlides(){
      clearAlbumSlideTimers();

      if (!els.albumGrid) return;

      const slideWraps = els.albumGrid.querySelectorAll('.gxa-album__slides[data-slide-count]');
      slideWraps.forEach((wrap, idx) => {
        const slides = Array.from(wrap.querySelectorAll('.gxa-album__slide'));
        const dots = Array.from(wrap.parentElement.querySelectorAll('.gxa-album__dot'));
        if (slides.length <= 1) return;

        let current = 0;
        let timer = null;

        const setActive = (nextIndex) => {
          slides.forEach((slide, i) => slide.classList.toggle('is-active', i === nextIndex));
          dots.forEach((dot, i) => dot.classList.toggle('is-active', i === nextIndex));
          current = nextIndex;
        };

        const start = () => {
          stop();
          timer = window.setInterval(() => {
            const next = (current + 1) % slides.length;
            setActive(next);
          }, 2200 + (idx % 4) * 250);
          albumSlideTimers.push(timer);
        };

        const stop = () => {
          if (timer){
            clearInterval(timer);
            timer = null;
          }
        };

        wrap.addEventListener('mouseenter', stop);
        wrap.addEventListener('mouseleave', start);
        start();
      });
    }

    function renderAlbums(items){
      clearAlbumSlideTimers();
      hideState();

      if (els.albumSection) els.albumSection.style.display = '';
      if (els.photoSection) els.photoSection.style.display = 'none';

      if (!els.albumGrid) return;

      if (!items.length){
        showEmpty(
          'No album cards found.',
          state.deptName ? `Try another search or department filter.` : 'Try another search.'
        );
        return;
      }

      els.albumGrid.style.display = '';
      els.albumGrid.innerHTML = items.map(item => {
        const ev = item?.event || {};
        const title = ev.title || 'Untitled Event';
        const desc = ev.description || 'No description available for this event.';
        const date = ev.date || '';
        const shortcode = ev.shortcode || '';
        const count = Number(item?.images_count || 0);

        const slideshowImages = extractAlbumImages(item);
        const cover = slideshowImages[0] || normalizeUrl(item?.cover_image_url || item?.cover_image || '');

        let mediaHtml = '';
        if (slideshowImages.length){
          mediaHtml = `
            <div class="gxa-album__slides" data-slide-count="${slideshowImages.length}">
              ${slideshowImages.map((src, idx) => `
                <img
                  class="gxa-album__slide ${idx === 0 ? 'is-active' : ''}"
                  src="${esc(src)}"
                  alt="${esc(title)}"
                  loading="lazy"
                >
              `).join('')}
            </div>
          `;
        } else if (cover) {
          mediaHtml = `<img src="${esc(cover)}" alt="${esc(title)}" loading="lazy">`;
        } else {
          mediaHtml = `<div class="gxa-album__fallback"><i class="fa-regular fa-images"></i></div>`;
        }

        const dotsHtml = slideshowImages.length > 1
          ? `<div class="gxa-album__dots">${slideshowImages.map((_, idx) => `<span class="gxa-album__dot ${idx === 0 ? 'is-active' : ''}"></span>`).join('')}</div>`
          : '';

        return `
          <article class="gxa-album ${!shortcode ? 'gxa-album--standalone' : ''}"
            data-shortcode="${escAttr(shortcode)}"
            data-title="${escAttr(title)}"
            data-description="${escAttr(desc)}"
            data-date="${escAttr(date)}"
            ${!shortcode ? `data-full="${escAttr(cover)}"` : ''}
            role="button"
            tabindex="0"
            aria-label="${esc(title)}">
            <div class="gxa-album__media">
              ${mediaHtml}
              ${shortcode ? `
                <div class="gxa-album__count">
                  <i class="fa-regular fa-image"></i>
                  <span>${esc(String(count))} Photo${count === 1 ? '' : 's'}</span>
                </div>
              ` : ''}
              ${dotsHtml}
            </div>

            ${shortcode ? `
              <div class="gxa-album__body">
                <div class="gxa-album__row">
                  ${date ? `<span class="gxa-pill gxa-pill--brand"><i class="fa-regular fa-calendar"></i>${esc(date)}</span>` : ''}
                </div>

                <h2 class="gxa-album__title">${esc(title)}</h2>
                <div class="gxa-album__desc">${esc(desc)}</div>

                <div class="gxa-album__cta">
                  <span class="gxa-album__link">
                    <i class="fa-solid fa-arrow-right"></i>
                    <span>Open Album</span>
                  </span>
                </div>
              </div>
            ` : ''}
          </article>
        `;
      }).join('');

      initAlbumSlides();
    }

    function renderPhotos(items, eventMeta){
      clearAlbumSlideTimers();
      hideState();

      if (els.albumSection) els.albumSection.style.display = 'none';
      if (els.photoSection) els.photoSection.style.display = '';

      const meta = eventMeta || {};
      const title = meta.title || 'Album';
      const desc = meta.description || '';
      const date = meta.date || '';
      const shortcode = meta.shortcode || '';

      if (els.albumTitle) {
        els.albumTitle.innerHTML = `<i class="fa-solid fa-folder-open"></i><span>${esc(title)}</span>`;
      }

      if (els.albumDate){
        if (date){
          els.albumDate.style.display = '';
          els.albumDate.innerHTML = `<i class="fa-regular fa-calendar"></i>${esc(date)}`;
        } else {
          els.albumDate.style.display = 'none';
          els.albumDate.textContent = '';
        }
      }

      if (els.albumCode){
        if (shortcode){
          els.albumCode.style.display = '';
          els.albumCode.innerHTML = `<i class="fa-solid fa-link"></i>${esc(shortcode)}`;
        } else {
          els.albumCode.style.display = 'none';
          els.albumCode.textContent = '';
        }
      }

      if (els.albumCount){
        els.albumCount.style.display = '';
        els.albumCount.innerHTML = `<i class="fa-regular fa-image"></i>${esc(String(state.total))} Photo${state.total === 1 ? '' : 's'}`;
      }

      if (els.albumDesc){
        if (desc){
          els.albumDesc.style.display = '';
          els.albumDesc.textContent = desc;
        } else {
          els.albumDesc.style.display = 'none';
          els.albumDesc.textContent = '';
        }
      }

      if (!els.photoGrid) return;

      if (!items.length){
        if (els.photoGrid) els.photoGrid.style.display = 'none';
        showEmpty('No photos found in this album.', 'Try another search within this album.');
        return;
      }

      els.photoGrid.style.display = '';
      els.photoGrid.innerHTML = items.map(it => {
        const img = normalizeUrl(pick(it, ['image_url','image_full_url','url','src','image']));
        const title = pick(it, ['title','name','alt','caption']) || 'Gallery Image';
        const description = pick(it, ['description','desc','summary','details']) || '';
        const tags = tagsFromItem(it);
        const tagsStr = tags.join('|');

        const descHtml = description
          ? `<div class="gxa-meta__desc">${esc(description)}</div>`
          : `<div class="gxa-meta__desc" style="opacity:0;"></div>`;

        const tagsHtml = tags.length
          ? `<div class="gxa-meta__tags">${renderTagChips(tags, 3)}</div>`
          : `<div class="gxa-meta__tags" style="display:none;"></div>`;

        return `
          <div class="gxa-item"
               data-full="${escAttr(img)}"
               data-title="${escAttr(title)}"
               data-desc="${escAttr(description)}"
               data-tags="${escAttr(tagsStr)}"
               role="button"
               tabindex="0"
               aria-label="${esc(title)}">
            <img src="${esc(img)}" alt="${esc(title)}" loading="lazy">
            <div class="gxa-meta">
              <div class="gxa-meta__title">${esc(title)}</div>
              ${descHtml}
              ${tagsHtml}
            </div>
          </div>
        `;
      }).join('');

      requestAnimationFrame(() => applyMasonry());

      const imgs = els.photoGrid.querySelectorAll('img');
      imgs.forEach(img => {
        if (img.complete) return;
        img.addEventListener('load', () => applyMasonry(), { once: true });
        img.addEventListener('error', () => applyMasonry(), { once: true });
      });
    }

    function renderPager(){
      const pager = els.pager;
      if (!pager) return;

      const last = state.lastPage || 1;
      const cur  = state.page || 1;

      if (last <= 1){
        pager.style.display = 'none';
        pager.innerHTML = '';
        return;
      }

      const btn = (label, page, {disabled=false, active=false}={}) => {
        const dis = disabled ? 'disabled' : '';
        const cls = active ? 'gxa-pagebtn active' : 'gxa-pagebtn';
        return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
      };

      let html = '';
      html += btn('Previous', Math.max(1, cur-1), { disabled: cur<=1 });

      const win = 2;
      const start = Math.max(1, cur - win);
      const end   = Math.min(last, cur + win);

      if (start > 1){
        html += btn('1', 1, { active: cur===1 });
        if (start > 2) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
      }

      for (let p=start; p<=end; p++){
        html += btn(String(p), p, { active: p===cur });
      }

      if (end < last){
        if (end < last - 1) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
        html += btn(String(last), last, { active: cur===last });
      }

      html += btn('Next', Math.min(last, cur+1), { disabled: cur>=last });

      pager.innerHTML = html;
      pager.style.display = 'flex';
    }

    async function loadAlbums(){
      state.mode = 'albums';
      updateUiContext();
      syncUrl();
      showLoading('Loading event albums…');

      try{
        const js = await fetchJson(buildAlbumsUrl());
        const items = Array.isArray(js?.data) ? js.data : [];
        const p = js?.pagination || {};

        state.total = parseInt(p.total || items.length || 0, 10) || 0;
        state.lastPage = parseInt(p.last_page || 1, 10) || 1;

        renderAlbums(items);
        renderPager();
      }catch(e){
        console.error(e);
        if (e.name === 'AbortError') return;
        showEmpty('Failed to load album cards.', 'Please refresh and try again.');
      }
    }

    async function loadPhotos(){
      if (!state.selectedEvent?.shortcode){
        state.selectedEvent = null;
        return loadAlbums();
      }

      state.mode = 'photos';
      updateUiContext();
      syncUrl();
      showLoading('Loading album photos…');

      try{
        const js = await fetchJson(buildEventPhotosUrl(state.selectedEvent.shortcode));
        const items = Array.isArray(js?.data) ? js.data : [];
        const p = js?.pagination || {};
        const meta = js?.event || state.selectedEvent || {};

        state.selectedEvent = {
          shortcode: meta.shortcode || state.selectedEvent.shortcode || '',
          title: meta.title || state.selectedEvent.title || 'Album',
          description: meta.description || state.selectedEvent.description || '',
          date: meta.date || state.selectedEvent.date || '',
        };

        state.total = parseInt(p.total || items.length || 0, 10) || 0;
        state.lastPage = parseInt(p.last_page || 1, 10) || 1;

        updateUiContext();
        renderPhotos(items, state.selectedEvent);
        renderPager();
      }catch(e){
        console.error(e);
        if (e.name === 'AbortError') return;

        state.selectedEvent = null;
        state.page = 1;
        state.mode = 'albums';
        updateUiContext();
        syncUrl();
        showEmpty('This album could not be opened.', 'It may not exist or may not be visible right now.');
      }
    }

    async function reloadCurrent(){
      if (state.mode === 'photos') return loadPhotos();
      return loadAlbums();
    }

    function openAlbumFromCard(card){
      const shortcode = (card.getAttribute('data-shortcode') || '').trim();
      if (!shortcode) return;

      state.selectedEvent = {
        shortcode,
        title: card.getAttribute('data-title') || '',
        description: card.getAttribute('data-description') || '',
        date: card.getAttribute('data-date') || '',
      };

      state.mode = 'photos';
      state.page = 1;
      state.q = '';
      if (els.search) els.search.value = '';
      updateUiContext();
      loadPhotos();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function backToAlbums(){
      state.selectedEvent = null;
      state.mode = 'albums';
      state.page = 1;
      state.q = '';
      if (els.search) els.search.value = '';
      updateUiContext();
      loadAlbums();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setLightboxMeta({title='', desc='', tags=[]}){
      const meta = $('gxaLbMeta');
      const t = $('gxaLbTitle');
      const d = $('gxaLbDesc');
      const tg = $('gxaLbTags');

      if (!meta || !t || !d || !tg) return;

      const hasTitle = (title || '').trim().length > 0;
      const hasDesc  = (desc || '').trim().length > 0;
      const hasTags  = Array.isArray(tags) && tags.length > 0;

      if (!hasTitle && !hasDesc && !hasTags){
        meta.style.display = 'none';
        t.textContent = '';
        d.textContent = '';
        tg.innerHTML = '';
        return;
      }

      meta.style.display = '';
      t.textContent = (title || '').trim();
      d.textContent = (desc || '').trim();

      if (hasTags){
        tg.innerHTML = tags.map(x => `<span class="gxa-lb__tag">${esc(x)}</span>`).join('');
        tg.style.display = 'flex';
      } else {
        tg.innerHTML = '';
        tg.style.display = 'none';
      }

      d.style.display = hasDesc ? '' : 'none';
    }

    function parseTagsStr(s){
      const raw = (s || '').toString().trim();
      if (!raw) return [];
      return raw.split('|').map(x => (x || '').trim()).filter(Boolean);
    }

    function openLB(src, meta){
      if (!els.lb || !els.lbImg) return;
      els.lbImg.src = src;
      setLightboxMeta(meta || {});
      els.lb.classList.add('show');
      els.lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLB(){
      if (!els.lb || !els.lbImg) return;
      els.lb.classList.remove('show');
      els.lb.setAttribute('aria-hidden', 'true');
      els.lbImg.src = '';
      setLightboxMeta({ title:'', desc:'', tags:[] });
      document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', async () => {
      await loadDepartments();

      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)) {
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      updateUiContext();

      const initialEvent = extractEventFromUrl();
      if (initialEvent) {
        state.selectedEvent = { shortcode: initialEvent, title: '', description: '', date: '' };
        state.mode = 'photos';
        await loadPhotos();
      } else {
        await loadAlbums();
      }

      // Search
      let searchTimer = null;
      els.search && els.search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
          state.q = (els.search.value || '').trim();
          state.page = 1;
          reloadCurrent();
        }, 260);
      });

      // Department change
      els.dept && els.dept.addEventListener('change', () => {
        const v = (els.dept.value || '').toString();
        if (v === '__loading') return;

        if (!v) setDeptSelection('');
        else setDeptSelection(v);

        state.page = 1;
        syncUrl();
        reloadCurrent();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      // Back button
      els.back && els.back.addEventListener('click', backToAlbums);

      // Pagination
      document.addEventListener('click', (e) => {
        const b = e.target.closest('button.gxa-pagebtn[data-page]');
        if (!b) return;
        const p = parseInt(b.dataset.page, 10);
        if (!p || Number.isNaN(p) || p === state.page) return;
        state.page = p;
        reloadCurrent();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      // Open album card / image
      document.addEventListener('click', (e) => {
        const card = e.target.closest('.gxa-album[data-shortcode]');
        if (card) {
          const sc = (card.getAttribute('data-shortcode') || '').trim();
          if (sc) {
            openAlbumFromCard(card);
          } else {
            const src   = card.getAttribute('data-full') || '';
            // Hide details for standalone images in Lightbox
            if (src) openLB(src, { title: '', desc: '', tags: [] });
          }
          return;
        }

        const tile = e.target.closest('.gxa-item[data-full]');
        if (!tile) return;

        const src   = tile.getAttribute('data-full') || '';
        const title = tile.getAttribute('data-title') || '';
        const desc  = tile.getAttribute('data-desc') || '';
        const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

        if (src) openLB(src, { title, desc, tags });
      });

      // Keyboard interactions
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          if (els.lb?.classList.contains('show')) closeLB();
          return;
        }

        const albumCard = e.target.closest?.('.gxa-album[data-shortcode]');
        if (albumCard && (e.key === 'Enter' || e.key === ' ')) {
          e.preventDefault();
          const sc = (albumCard.getAttribute('data-shortcode') || '').trim();
          if (sc) {
            openAlbumFromCard(albumCard);
          } else {
            const src   = albumCard.getAttribute('data-full') || '';
            // Hide details for standalone images in Lightbox
            if (src) openLB(src, { title: '', desc: '', tags: [] });
          }
          return;
        }

        const tile = e.target.closest?.('.gxa-item[data-full]');
        if (!tile) return;

        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const src   = tile.getAttribute('data-full') || '';
          const title = tile.getAttribute('data-title') || '';
          const desc  = tile.getAttribute('data-desc') || '';
          const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');
          if (src) openLB(src, { title, desc, tags });
        }
      });

      // Lightbox close
      els.lb && els.lb.addEventListener('click', (e) => {
        if (e.target === els.lb) closeLB();
      });
      els.lbClose && els.lbClose.addEventListener('click', closeLB);

      // Masonry responsiveness
      window.addEventListener('resize', () => {
        clearTimeout(window.__gxaResizeT);
        window.__gxaResizeT = setTimeout(() => applyMasonry(), 80);
      });
    });
  })();
  </script>
</body>
</html>