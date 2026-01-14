{{-- resources/views/home.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ config('app.name','College Portal') }} — Home</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  @php
    /**
     * ✅ IMPORTANT:
     * Only use THESE APIs (as per your routes). No /full usage.
     * Page will load fast: above-fold loads sequentially; below-fold loads on scroll.
     *
     * ✅ NOTE:
     * Recruiters dynamic API removed from this page because the full recruiters module is included below.
     */
    $homeApis = $homeApis ?? [
      // Above-fold (loads immediately one-by-one)
      'hero'          => url('/api/public/grand-homepage/hero-carousel'),
      'noticeMarquee' => url('/api/public/grand-homepage/notice-marquee'),
      'infoBoxes'     => url('/api/public/grand-homepage/quick-links'),
      'nvaRow'        => url('/api/public/grand-homepage/notice-board'),

      // Lazy (loads on scroll)
      'stats'           => url('/api/public/grand-homepage/stats'),
      'achvRow'         => url('/api/public/grand-homepage/activities'),
      'placementNotices'=> url('/api/public/grand-homepage/placement-notices'),

      'testimonials'   => url('/api/public/grand-homepage/successful-entrepreneurs'),
      'alumni'         => url('/api/public/grand-homepage/alumni-speak'),
      'success'        => url('/api/public/grand-homepage/success-stories'),
      'courses'        => url('/api/public/grand-homepage/courses'),
    ];
  @endphp

  <style>
    :root{
      --brand: #9E363A;
      --brand2: #6B2528;
      --accent: #C94B50;
      --line: #e6c8ca;
      --surface: #fff;
      --ink: #111;
      --muted: #6b7280;
      --shadow: 0 10px 28px rgba(0,0,0,.10);
      --r-xl: 18px;
    }

    body{ background: #f6f7fb; color: var(--ink); }

    /* =========================
      ✅ Page Loader (better perceived performance)
    ========================= */
    .page-loader{position: fixed;inset: 0;z-index: 99999;display:flex;align-items:center;justify-content:center;padding: 18px;background: rgba(246,247,251,.75);backdrop-filter: blur(8px);-webkit-backdrop-filter: blur(8px);transition: opacity .35s ease, visibility .35s ease;}
    .page-loader.is-done{ opacity:0; visibility:hidden; pointer-events:none; }
    .loader-card{width: min(520px, 92vw);background: rgba(255,255,255,.92);border: 1px solid rgba(158,54,58,.18);border-radius: 20px;box-shadow: 0 18px 44px rgba(2,6,23,.16);padding: 18px 18px 16px;overflow:hidden;position: relative;}
    .loader-card::before{content:"";position:absolute; inset:-120px -120px auto auto;width: 260px; height: 260px;background: radial-gradient(circle at 30% 30%, rgba(201,75,80,.22), rgba(201,75,80,0));transform: rotate(10deg);pointer-events:none;}
    .loader-top{display:flex;align-items:center;gap: 12px;position: relative;}
    .loader-logo{width: 42px; height: 42px;border-radius: 14px;display:inline-flex; align-items:center; justify-content:center;background: linear-gradient(135deg, rgba(158,54,58,.16), rgba(201,75,80,.10));border: 1px solid rgba(158,54,58,.18);color: var(--brand);flex: 0 0 auto;}
    .loader-title{font-weight: 950;margin: 0;font-size: 16px;color:#0f172a;line-height: 1.15;}
    .loader-sub{margin: 2px 0 0;color: var(--muted);font-weight: 800;font-size: 13px;}
    .loader-bar{margin-top: 14px;height: 10px;border-radius: 999px;background: rgba(2,6,23,.06);overflow:hidden;border: 1px solid rgba(2,6,23,.06);position: relative;}
    .loader-bar > span{display:block;height:100%;width: 10%;border-radius: 999px;background: linear-gradient(90deg, var(--brand), var(--accent), var(--brand2));transition: width .35s ease;position: relative;}
    .loader-bar > span::after{content:"";position:absolute; inset:0;background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.35), rgba(255,255,255,0));transform: translateX(-60%);animation: loaderShine 1.1s linear infinite;mix-blend-mode: overlay;}
    @keyframes loaderShine{ to{ transform: translateX(160%); } }
    .loader-row{margin-top: 12px;display:flex;align-items:center;justify-content:space-between;gap: 12px;position: relative;}
    .loader-step{font-weight: 900;color: #7a2626;font-size: 13px;white-space: nowrap;overflow:hidden;text-overflow: ellipsis;max-width: 70%;}
    .loader-spinner{width: 28px; height: 28px;border-radius: 50%;border: 3px solid rgba(158,54,58,.22);border-top-color: var(--brand);animation: spin .75s linear infinite;flex: 0 0 auto;}
    @keyframes spin{ to{ transform: rotate(360deg); } }

    /* =========================
      ✅ NEW: Home Contact Popup (shows every refresh)
      ✅ FIXED: popup content can scroll + starts at top
    ========================= */
    .home-popup{
      position: fixed;
      inset: 0;
      z-index: 100000;
      display: none;
      align-items: flex-start;
      justify-content: center;
      padding: 18px;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
    }
    .home-popup.is-open{ display:flex; }

    .home-popup-backdrop{
      position: fixed;
      inset:0;
      background: rgba(2,6,23,.55);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
    }

    .home-popup-card{
      position: relative;
      background: rgba(255,255,255,.98);
      border: 1px solid rgba(158,54,58,.22);
      border-radius: 20px;
      box-shadow: 0 22px 60px rgba(2,6,23,.30);
      width: min(980px, 96vw);
      max-height: calc(100vh - 36px);
      margin: 18px auto;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .home-popup-card::before{
      content:"";
      position:absolute;
      inset:-140px -140px auto auto;
      width: 280px;
      height: 280px;
      background: radial-gradient(circle at 30% 30%, rgba(201,75,80,.22), rgba(201,75,80,0));
      transform: rotate(12deg);
      pointer-events:none;
    }
    .home-popup-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      padding: 18px 18px 10px;
      position: relative;
      flex: 0 0 auto;
    }
    .home-popup-title{
      margin:0;
      font-weight: 950;
      color:#0f172a;
      font-size: 18px;
      line-height: 1.1;
    }
    .home-popup-sub{
      margin: 6px 0 0;
      color: var(--muted);
      font-weight: 800;
      font-size: 13px;
    }
    .home-popup-close{
      width: 38px;
      height: 38px;
      border-radius: 12px;
      border: 1px solid rgba(2,6,23,.12);
      background: rgba(255,255,255,.9);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
    }
    .home-popup-close:hover{ background:#f1f5f9; }

    .home-popup-body{
      padding: 0 18px 18px;
      position: relative;
      flex: 1 1 auto;
      min-height: 0;
      overflow: auto;
      -webkit-overflow-scrolling: touch;
    }

    .home-popup-placeholder{
      border: 1px dashed rgba(158,54,58,.32);
      background: linear-gradient(135deg, rgba(158,54,58,.06), rgba(201,75,80,.03));
      border-radius: 16px;
      padding: 18px;
      text-align: center;
    }
    .home-popup-placeholder h4{
      margin: 0 0 8px;
      font-weight: 950;
      color: var(--brand);
      font-size: 16px;
    }
    .home-popup-placeholder p{
      margin: 0;
      color: #334155;
      font-weight: 800;
      font-size: 13.5px;
      line-height: 1.5;
    }
    .home-popup-placeholder code{
      background: rgba(255,255,255,.65);
      padding: 2px 6px;
      border-radius: 8px;
      font-weight: 950;
      color: #7a2626;
    }
    @media (max-width: 576px){
      .home-popup{ padding: 12px; }
      .home-popup-card{
        border-radius: 18px;
        width: min(980px, 98vw);
        max-height: calc(100vh - 24px);
        margin: 12px auto;
      }
      .home-popup-head{ padding: 16px 14px 8px; }
      .home-popup-body{ padding: 0 14px 14px; }
    }

    /* =========================
      Motion / Reveal (loads one-by-one + on scroll)
    ========================= */
    .reveal{opacity: 0;transform: translateY(22px);transition: opacity .7s ease, transform .85s cubic-bezier(.2,.8,.2,1);will-change: opacity, transform;}
    .reveal.reveal-left{ transform: translateX(-22px); }
    .reveal.reveal-right{ transform: translateX(22px); }
    .reveal.is-in{ opacity: 1; transform: translate3d(0,0,0); }
    @media (prefers-reduced-motion: reduce){
      .reveal, .reveal.reveal-left, .reveal.reveal-right{opacity: 1 !important;transform: none !important;transition: none !important;}
      .loader-spinner{ animation: none !important; }
      .loader-bar > span::after{ animation: none !important; }
    }

    /* =========================
      ✅ NEW: Home Sections Container
    ========================= */
    .home-sections-container {
      display: flex;
      flex-direction: column;
      gap: 2.5rem;
      margin-top: 1.5rem;
    }

    /* =========================
      ✅ NEW: FULL-WIDTH TOP STACK (Notice strip + Hero)
      - These two sections are OUTSIDE the .container now
      - Rest of the homepage remains inside the normal container width
    ========================= */
    .home-topstack{
      width: 100%;
      display:flex;
      flex-direction:column;
      margin-bottom: 10px;
    }

    /* ===== hero carousel ===== */
    .hero-wrap{ position:relative; overflow:hidden; }
    .hero-card{background: var(--surface);overflow:hidden;}
    .hero-slide{min-height: 500px;background-size: cover;background-position: center;position: relative;}
    .hero-slide::before{content:"";position:absolute; inset:0;background: linear-gradient(90deg, rgba(0,0,0,.65), rgba(0,0,0,.20));}
    .hero-inner{position:relative;padding: 60px 40px;max-width: 980px;color:#fff;}
    .hero-kicker{display:inline-flex; gap:10px; align-items:center;padding: 8px 16px;border-radius: 999px;background: rgba(255,255,255,.16);border: 1px solid rgba(255,255,255,.25);font-weight: 700;font-size: 13px;letter-spacing:.4px;margin-bottom: 20px;}
    .hero-title{font-weight: 900;line-height: 1.1;margin: 0 0 16px;font-size: clamp(28px, 4vw, 52px);}
    .hero-actions{ display:flex; gap:12px; flex-wrap:wrap; margin-top: 20px; }
    .btn-hero{background: var(--accent);border: 0;color:#fff;border-radius: 12px;padding: 12px 24px;font-weight: 800;font-size: 15px;}
    .btn-hero:hover{ background: var(--brand); color:#fff; }

    /* =========================
      Hero carousel transition control (duration + fade)
      JS will set --hero-transition-ms using API transition_ms
    ========================= */
    #homeHero{ --hero-transition-ms: 600ms; }
    #homeHero .carousel-item{transition: transform var(--hero-transition-ms) ease-in-out;}
    #homeHero.carousel-fade .carousel-item{transition-property: opacity;transition-duration: var(--hero-transition-ms);}
    #homeHero.carousel-fade .active.carousel-item-start,
    #homeHero.carousel-fade .active.carousel-item-end{transition: opacity 0s var(--hero-transition-ms);}

    /* =========================
      Top strip (NOTICE MARQUEE ONLY)
      ✅ FIXED: marquee settings applied via API settings now
    ========================= */
    .notice-strip{background: #ffd600;padding: 5px 14px;overflow:hidden;}
    .notice-strip .strip-ico{width: 34px; height: 34px;display:inline-flex; align-items:center; justify-content:center;border-radius: 999px;background: rgba(158,54,58,.12);color: var(--brand);border: 1px solid rgba(158,54,58,.18);flex: 0 0 auto;}
    .notice-strip marquee{font-weight: 900;color: #7a2626;font-size: 14.5px;}
    .notice-strip marquee .nm-link{color: #7a2626;text-decoration: none;font-weight: 950;cursor: pointer;}
    .notice-strip marquee .nm-link:hover{color: #0D29AC;}
    .notice-strip marquee .nm-text{color: #7a2626;font-weight: 900;cursor: default;}
    .notice-strip marquee .nm-sep{opacity: .75;padding: 0 10px;user-select:none;}

    /* =========================
      ✅ Smooth Notice Marquee (settings-driven)
    ========================= */
    .nm-viewport{ overflow:hidden; width:100%; }
    .nm-track{
      display:flex;
      align-items:center;
      gap: 10px;
      white-space: nowrap;
      will-change: transform;
    }
    .nm-run{ display:inline-flex; align-items:center; gap: 10px; }
    .nm-text{font-weight: 900;color:#7a2626;font-size:14.5px;}
    .nm-link{color:#7a2626;text-decoration:none;font-weight:950;}
    .nm-link:hover{color: #0D29AC;}
    .nm-sep{opacity:.75;padding:0 10px;user-select:none;}

    /* ✅ NEW: Notice Marquee GIF (prefix for every item + suffix for last) */
    .nm-gif{
      width: 18px;
      height: 18px;
      object-fit: contain;
      display: inline-block;
      vertical-align: middle;
      transform: translateY(-1px);
    }
    .nm-gif-start{ margin: 0 8px 0 0; }
    .nm-gif-end{ margin: 0 0 0 8px; }

    /* ===== three info boxes ===== */
    .info-boxes{ }
    .info-box{background: var(--brand);color: #fff;border-radius: 16px;padding: 24px;height: 100%;box-shadow: var(--shadow);position:relative;overflow:hidden;}
    .info-box::after{content:"";position:absolute; inset:-40px -40px auto auto;width: 160px; height: 160px;background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), rgba(255,255,255,0));transform: rotate(18deg);pointer-events:none;}
    .info-box h5{font-weight: 900;margin-bottom: 12px;font-size: 18px;display:flex; align-items:center; gap:10px;}
    .info-box ul{list-style: none;padding: 0;margin: 0;}
    .info-box ul li{padding: 8px 0;border-bottom: 1px dashed rgba(255,255,255,.3);font-size: 14px;display:flex;align-items:flex-start;gap:10px;}
    .info-box ul li:last-child{ border-bottom: 0; }
    .info-box i{ margin-top: 2px; opacity: .92; }
    .info-box a{ color:#fff; text-decoration:none; font-weight:800; }
    .info-box a:hover{ text-decoration:underline; }

    /* ✅ FIXED: Smooth upward scroller styling - IMPROVED */
    .info-box ul.autoscroll{
      overflow: hidden;
      scroll-behavior: auto;
      position: relative;
      height: 260px;
    }

    /* ✅ FIXED: Create smooth scrolling animation */
    .info-box ul.scrolling-upwards {
      animation: scrollUp 20s linear infinite;
      animation-play-state: running;
    }

    .info-box ul.scrolling-upwards:hover {
      animation-play-state: paused;
    }

    @keyframes scrollUp {
      0% { transform: translateY(0); }
      100% { transform: translateY(-50%); }
    }

    /* =========================
      Notice + Center Iframe + Announcements
    ========================= */
    .nva-card{background: var(--surface);border-radius: 18px;box-shadow: var(--shadow);padding: 14px;height: 100%;border: 1px solid var(--line);overflow: hidden;}
    .nva-head{display:flex; align-items:center; justify-content:center;gap: 10px;color:#fff;font-weight: 950;letter-spacing:.3px;padding: 10px 12px;font-size: 18px;user-select:none;border-radius: 14px;margin: 0 0 10px;background: linear-gradient(135deg, var(--brand), var(--brand2));}
    .nva-head i{opacity:.95;filter: drop-shadow(0 6px 10px rgba(0,0,0,.12));}
    .nva-body{background: #fff;border-radius: 14px;border: 1px solid rgba(17,17,17,.06);padding: 12px;color: var(--ink);position: relative;overflow: hidden;}
    .nva-list{list-style:none;padding: 0;margin: 0;position: relative;}
    .nva-list li{display:flex;align-items:flex-start;gap: 10px;padding: 9px 6px;border-bottom: 1px dashed rgba(2,6,23,.12);font-weight: 700;color: #0f172a;}
    .nva-list li:last-child{ border-bottom:0; }
    .nva-list li i{ margin-top: 3px; color: rgba(15,23,42,.55); }
    .nva-list a{color: #0f172a;text-decoration:none;font-weight: 800;line-height: 1.25;}
    .nva-list a:hover{ color: var(--brand); text-decoration: underline; }

    /* ✅ FIXED: Auto-scroll list styling - IMPROVED */
    .nva-list.autoscroll{
      overflow: hidden;
      height: 260px;
      position: relative;
    }

    /* ✅ FIXED: Create smooth scrolling animation for NVA cards */
    .nva-list.scrolling-upwards {
      animation: scrollUpList 25s linear infinite;
      animation-play-state: running;
    }

    .nva-list.scrolling-upwards:hover {
      animation-play-state: paused;
    }

    @keyframes scrollUpList {
      0% { transform: translateY(0); }
      100% { transform: translateY(-50%); }
    }

    .center-video-card{background: var(--surface);border-radius: 18px;border: 1px solid var(--line);box-shadow: var(--shadow);padding: 14px;height: 100%;overflow:hidden;}
    .center-video-title{font-weight: 950;color: #0f172a;margin: 2px 0 12px;text-align:center;font-size: 22px;}
    .video-embed{position: relative;width: 100%;padding-bottom: 56.25%;border-radius: 16px;overflow: hidden;box-shadow: 0 10px 24px rgba(2,6,23,.12);background: #111;}
    .video-embed iframe{position:absolute; inset:0;width:100%; height:100%;border:0;}

    /* =========================
      ✅ CTA Buttons
    ========================= */
    .cta-section{padding-top: 13px;display:flex;flex-direction: column;gap: 10px;align-items: stretch;justify-content: center;max-width: 360px;margin-left: auto;margin-right: auto;}
    .cta-btn{display: inline-flex;align-items: center;justify-content: center;gap: 10px;width: 100%;min-width: 0;background: #f59e0b;color: #fff;border: 0;border-radius: 14px;padding: 10px 14px;font-weight: 950;font-size: 14px;box-shadow: 0 6px 14px rgba(245,158,11,.28);transition: transform .15s ease, filter .15s ease, background .15s ease;text-decoration:none;}
    .cta-btn:hover{ background:#d97706; transform: translateY(-1px); color:#fff; }
    .cta-btn.btn-secondary{ background:#991b1b; box-shadow: 0 6px 14px rgba(153,27,27,.22); }
    .cta-btn.btn-secondary:hover{ background:#7f1d1d; color:#fff; }

    /* ===== stats counter ===== */
    .stats-section{background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(201,75,80,.04));border-radius: var(--r-xl);padding: 50px 30px;border: 1px solid rgba(158,54,58,.12);position:relative;overflow:hidden;}
    .stats-section.has-bg{background-size: cover;background-position: center;}
    .stats-section .stats-head{ text-align:center; margin-bottom: 26px; }
    .stats-section .stats-head h2{margin:0;font-weight: 950;color: var(--brand);font-size: clamp(22px, 3vw, 34px);}
    .stat-item{ text-align: center; }
    .stat-num{font-size: clamp(40px, 5vw, 64px);font-weight: 950;color: var(--brand);line-height: 1;margin-bottom: 8px;}
    .stat-label{ font-size: 16px; color: var(--muted); font-weight: 800; }
    .stat-icon{display:inline-flex;width: 42px; height: 42px;align-items:center; justify-content:center;border-radius: 999px;background: rgba(158,54,58,.10);color: var(--brand);margin-bottom: 10px;border: 1px solid rgba(158,54,58,.18);}

    /* =========================
      ✅ Carousel controls OUTSIDE content
    ========================= */
    .carousel.controls-out{ position: relative; }
    .carousel.controls-out .carousel-inner{padding-left: 56px;padding-right: 56px;}
    .carousel.controls-out .carousel-control-prev,
    .carousel.controls-out .carousel-control-next{width: 46px;height: 46px;top: 50%;bottom: auto;transform: translateY(-50%);opacity: 1;background: rgba(255,255,255,.92);border: 1px solid rgba(158,54,58,.22);border-radius: 999px;box-shadow: 0 12px 24px rgba(2,6,23,.12);}
    .carousel.controls-out .carousel-control-prev{ left: 0; }
    .carousel.controls-out .carousel-control-next{ right: 0; }
    .carousel.controls-out .carousel-control-prev-icon,
    .carousel.controls-out .carousel-control-next-icon{filter: invert(1);width: 1.15rem;height: 1.15rem;}
    .carousel.indicators-out .carousel-indicators{position: static;margin: 14px 0 0;justify-content: center;gap: 6px;}
    .carousel.indicators-out .carousel-indicators [data-bs-target]{width: 8px;height: 8px;border-radius: 999px;}
    @media (max-width: 576px){
      .carousel.controls-out .carousel-inner{ padding-left: 44px; padding-right: 44px; }
      .carousel.controls-out .carousel-control-prev,
      .carousel.controls-out .carousel-control-next{ width: 40px; height: 40px; }
    }

    /* ===== carousels controls on light surfaces ===== */
    .stats-carousel .carousel-control-prev-icon,
    .stats-carousel .carousel-control-next-icon,
    .testimonial-carousel .carousel-control-prev-icon,
    .testimonial-carousel .carousel-control-next-icon,
    .alumni-carousel .carousel-control-prev-icon,
    .alumni-carousel .carousel-control-next-icon{filter: invert(1);opacity: .9;}
    .stats-carousel .carousel-indicators [data-bs-target],
    .testimonial-carousel .carousel-indicators [data-bs-target]{background-color: rgba(158,54,58,.55);}
    .stats-carousel .carousel-indicators .active,
    .testimonial-carousel .carousel-indicators .active{background-color: var(--brand);}

    /* ===== testimonials ===== */
    .testimonial-section{background: var(--surface);border-radius: var(--r-xl);border: 1px solid var(--line);padding: 40px;box-shadow: var(--shadow);}
    .testimonial-section h2{text-align: center;font-weight: 950;color: var(--brand);margin-bottom: 30px;font-size: clamp(22px, 3vw, 36px);}
    .testimonial-card{background: linear-gradient(135deg, rgba(158,54,58,.06), rgba(201,75,80,.03));border-radius: 16px;padding: 30px;height: 100%;border: 1px solid var(--line);transition: transform .18s ease, box-shadow .18s ease;}
    .testimonial-card:hover{box-shadow: 0 14px 26px rgba(2,6,23,.10);}
    .testimonial-avatar{width: 80px; height: 80px;border-radius: 50%;object-fit: cover;border: 4px solid var(--brand);margin-bottom: 16px;background: #fff;}
    .testimonial-text{font-style: italic;color: var(--ink);margin-bottom: 16px;line-height: 1.6;font-family: inherit;background: transparent;padding: 0;border-radius: 0;white-space: normal;word-break: break-word;}
    .testimonial-text p{ margin: 0 0 10px; }
    .testimonial-text p:last-child{ margin-bottom: 0; }
    .testimonial-text ul, .testimonial-text ol{ margin: 8px 0 0 18px; }
    .testimonial-name{ font-weight: 950; color: var(--brand); margin-bottom: 4px; }
    .testimonial-role{ font-size: 13px; color: var(--muted); font-weight: 800; }

    /* ===== alumni videos ===== */
    .alumni-section{background: var(--surface);border-radius: var(--r-xl);border: 1px solid var(--line);padding: 40px;box-shadow: var(--shadow);}
    .alumni-section h2{text-align: center;font-weight: 950;color: var(--brand);margin-bottom: 30px;font-size: clamp(22px, 3vw, 36px);}
    .alumni-video-card{border-radius: 16px;overflow: hidden;box-shadow: 0 10px 22px rgba(2,6,23,.10);height: 100%;background:#111;}
    .alumni-video-card iframe{width: 100%;height: 240px;display:block;border:0;}

    /* ===== success stories ===== */
    .success-section{background: #f9fafb;border-radius: var(--r-xl);padding: 40px;border: 1px solid rgba(17,17,17,.06);}
    .success-section h2{text-align: center;font-weight: 950;color: var(--brand);margin-bottom: 30px;font-size: clamp(22px, 3vw, 36px);}

    .success-scroller{--success-gap: 16px;--success-gap-2: 32px;--success-gap-3: 48px;display:flex;gap: var(--success-gap);overflow-x: auto;padding: 0;margin: 0;scroll-snap-type: x mandatory;-webkit-overflow-scrolling: touch;scrollbar-width: none;-ms-overflow-style: none;}
    .success-scroller::-webkit-scrollbar{ height: 0; width: 0; display:none; }

    .success-scroller-item{flex: 0 0 82%;max-width: 82%;scroll-snap-align: start;}
    @media (min-width: 768px){
      .success-scroller-item{flex: 0 0 calc((100% - var(--success-gap)) / 2);max-width: calc((100% - var(--success-gap)) / 2);}
    }
    @media (min-width: 992px){
      .success-scroller-item{flex: 0 0 calc((100% - var(--success-gap-2)) / 3);max-width: calc((100% - var(--success-gap-2)) / 3);}
    }
    @media (min-width: 1200px){
      .success-scroller-item{flex: 0 0 calc((100% - var(--success-gap-3)) / 4);max-width: calc((100% - var(--success-gap-3)) / 4);}
    }

    .success-card{
      display:block;
      background: var(--surface);
      border-radius: 16px;
      padding: 20px;
      height: 100%;
      border: 1px solid var(--line);
      box-shadow: none;
      transition: transform .18s ease, border-color .18s ease;
      text-decoration:none;
      color: inherit;
    }
    .success-card:hover{ transform: translateY(-2px); border-color: rgba(158,54,58,.35); }
    .success-img{width: 100%;height: 200px;object-fit: cover;border-radius: 12px;margin-bottom: 16px;background:#eee;}
    .success-desc{font-size: 14px;color: var(--muted);margin-bottom: 12px;line-height: 1.5;font-family: inherit;background: transparent;padding: 0;border-radius: 0;white-space: normal;word-break: break-word;}
    .success-desc p{ margin: 0 0 10px; }
    .success-desc p:last-child{ margin-bottom: 0; }
    .success-desc ul, .success-desc ol{ margin: 8px 0 0 18px; }
    .success-name{ font-weight: 950; color: var(--brand); font-size: 16px; margin-bottom: 4px; }
    .success-role{ font-size: 13px; color: var(--muted); font-weight: 800; }

    /* ===== courses section ===== */
    .courses-section{background: var(--surface);border-radius: var(--r-xl);border: 1px solid var(--line);padding: 40px;box-shadow: var(--shadow);}
    .courses-section h2{text-align: center;font-weight: 950;color: var(--brand);margin-bottom: 30px;font-size: clamp(22px, 3vw, 36px);}
    .course-card{background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(201,75,80,.04));border-radius: 16px;padding: 24px;height: 100%;border: 1px solid var(--line);transition: transform .18s ease, box-shadow .18s ease;}
    .course-card:hover{transform: translateY(-3px);box-shadow: 0 16px 30px rgba(2,6,23,.12);}
    .course-img{width: 100%;height: 180px;object-fit: cover;border-radius: 12px;margin-bottom: 16px;background:#eee;}
    .course-title{font-weight: 950;color: var(--brand);font-size: 20px;margin-bottom: 10px;}
    .course-desc{ font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 14px; }
    .course-links{ display:flex; gap: 8px; flex-wrap: wrap; }
    .course-link{font-size: 12px;padding: 6px 12px;background: rgba(158,54,58,.15);color: var(--brand);border-radius: 999px;text-decoration: none;font-weight: 900;transition: background .15s ease, color .15s ease, transform .15s ease;}
    .course-link:hover{ background: var(--brand); color: #fff; transform: translateY(-1px); }

    .muted-note{color: var(--muted);font-weight: 800;text-align:center;margin: 0;padding: 10px 0 0;}

    .home-alert{margin-top: 18px;border-radius: 14px;border: 1px solid rgba(245,158,11,.35);background: linear-gradient(135deg, rgba(254,243,199,.85), rgba(254,215,170,.65));padding: 14px 16px;color: #92400e;font-weight: 900;display:none;}
    .home-alert code{font-weight: 950;color:#7c2d12;background: rgba(255,255,255,.55);padding: 2px 6px;border-radius: 8px;}
    .home-alert pre{margin: 8px 0 0;white-space: pre-wrap;background: rgba(255,255,255,.55);padding: 10px 12px;border-radius: 12px;font-size: 12.5px;line-height: 1.4;color: #7c2d12;}

    /* ✅ Recruiters wrapper like other sections */
    .recruiters-wrap{
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow);
      padding: 22px 18px;
      overflow: hidden;
    }

    @media (max-width: 768px){
      .hero-inner{ padding: 40px 24px; }
      .info-boxes{ margin-top: 0; }
      .stat-num{ font-size: 36px; }
      .testimonial-section, .alumni-section, .courses-section{ padding: 26px; }
      .success-section{ padding: 26px; }
      .center-video-title{ font-size: 18px; }
      .cta-section{ max-width: 100%; }
      .cta-btn{ font-size: 14px; padding: 10px 12px; }
      .loader-card{ padding: 16px; border-radius: 18px; }

      .home-sections-container{
        gap: 1.5rem;
        margin-top: 1rem;
      }

      /* ✅ MOBILE SPECIFIC: Show only 1 card for testimonials (Successful Entrepreneurs) */
      #entrepreneursCarousel .carousel-item .row .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      /* ✅ MOBILE SPECIFIC: Show only 1 card for alumni videos */
      #alumniCarousel .carousel-item .row .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }
  </style>
</head>

<body>
  {{-- ✅ Page Loader --}}
  <div class="page-loader" id="pageLoader" aria-hidden="false">
    <div class="loader-card">
      <div class="loader-top">
        <div class="loader-logo"><i class="fa-solid fa-bolt"></i></div>
        <div class="flex-grow-1">
          <p class="loader-title mb-0">{{ config('app.name','College Portal') }}</p>
          <p class="loader-sub mb-0">Loading homepage sections…</p>
        </div>
      </div>

      <div class="loader-bar" aria-hidden="true">
        <span id="pageLoaderBar" style="width:10%"></span>
      </div>

      <div class="loader-row">
        <div class="loader-step" id="pageLoaderText">Preparing…</div>
        <div class="loader-spinner" aria-hidden="true"></div>
      </div>
    </div>
  </div>

  {{-- ✅ NEW: Home Popup (every refresh) --}}
  <div class="home-popup" id="homePopup" role="dialog" aria-modal="true" aria-labelledby="homePopupTitle" aria-hidden="true">
    <div class="home-popup-backdrop" data-home-popup-close="1"></div>

    <div class="home-popup-card" role="document">
      <div class="home-popup-head">
        <div>
          <h3 class="home-popup-title" id="homePopupTitle">
            <i class="fa-solid fa-envelope-open-text me-2"></i>Contact Us
          </h3>
        </div>

        <button type="button" class="home-popup-close" aria-label="Close" data-home-popup-close="1">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="home-popup-body">
        {{-- Later: replace placeholder with your include --}}
        @include('modules.contactUs.viewContactUs')
      </div>
    </div>
  </div>

  {{-- Top Header --}}
  @include('landing.components.topHeaderMenu')

  {{-- Main Header --}}
  @include('landing.components.header')

  {{-- Header Menu --}}
  @include('landing.components.headerMenu')

  {{-- Sticky Buttons --}}
  @include('landing.components.stickyButtons')

  <main class="pb-5">

    {{-- ✅ FULL-WIDTH: Notice Strip + Hero (outside container, 100% width) --}}
    <div class="home-topstack">
      {{-- ================= TOP NOTICE MARQUEE (NOTICE ONLY) ================= --}}
      <section class="notice-strip reveal is-in" data-anim="up">
        <div class="d-flex align-items-center gap-3">
          <div class="strip-ico"><i class="fa-solid fa-bullhorn"></i></div>
          <div class="flex-grow-1 nm-viewport" id="noticeMarqueeViewport">
            <div class="nm-track" id="noticeMarqueeTrack">
              <span class="nm-text">Loading notices…</span>
            </div>
          </div>
        </div>
      </section>

      {{-- ================= HERO CAROUSEL ================= --}}
      <section class="hero-wrap reveal is-in" data-anim="up">
        <div class="hero-card">
          <div id="homeHero" class="carousel slide">
            <div class="carousel-indicators" id="heroIndicators">
              {{-- Dynamic indicators --}}
            </div>

            <div class="carousel-inner" id="heroSlides">
              {{-- Fallback slide (NO external image, so no 404) --}}
              <div class="carousel-item active">
                <div class="hero-slide" style="background-image:linear-gradient(135deg, rgba(158,54,58,.95), rgba(107,37,40,.92));">
                  <div class="hero-inner">
                    <div class="hero-kicker">
                      <i class="fa-solid fa-graduation-cap"></i>
                      <span>Loading…</span>
                    </div>
                    <h1 class="hero-title">{{ config('app.name','College Portal') }}</h1>
                    <div class="hero-actions">
                      <a href="{{ url('/admissions') }}" class="btn btn-hero">Apply Now</a>
                      <a href="{{ url('/courses') }}" class="btn btn-hero">Explore Programs</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#homeHero" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeHero" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          </div>
        </div>
      </section>
    </div>

    {{-- ✅ Everything else remains normal width (container) --}}
    <div class="container">
      <div class="home-sections-container">

        <div class="home-alert" id="homeApiAlert">
          Home API error. Please verify section endpoints in <code>$homeApis</code>.
        </div>

        {{-- ================= THREE INFO BOXES (Career / Why / Scholarship) ================= --}}
        <section class="info-boxes reveal is-in" data-anim="up" data-immediate="1">
          <div class="row g-3">
            <div class="col-lg-4 col-md-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-trophy"></i> Career At MSIT</h5>
                <ul id="careerList">
                  <li><i class="fa-solid fa-chevron-right"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-4 col-md-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-star"></i> Why MSIT</h5>
                <ul id="whyMsitList">
                  <li><i class="fa-solid fa-check"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-4 col-md-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-award"></i> Scholarship</h5>
                <ul id="scholarshipList">
                  <li><i class="fa-solid fa-gift"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= NOTICE (LEFT) + CENTER IFRAME (MIDDLE) + ANNOUNCEMENTS (RIGHT) ================= --}}
        <section class="info-boxes">
          <div class="row g-3 align-items-stretch">
            <div class="col-lg-4">
              <div class="nva-card reveal reveal-left" data-immediate="1" data-section="notice-left">
                <div class="nva-head"><i class="fa-solid fa-bullhorn"></i> <span>Notice</span></div>
                <div class="nva-body">
                  <ul class="nva-list" id="noticeList">
                    <li><i class="fa-solid fa-file"></i> <span>Loading…</span></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="center-video-card reveal" data-immediate="1" data-section="center-iframe">
                <div class="center-video-title" id="centerIframeTitle">Loading…</div>

                <div class="video-embed" id="mainVideoContainer">
                  <iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ" loading="lazy" allowfullscreen></iframe>
                </div>

                <div class="cta-section" id="centerIframeButtons">
                  <a href="#" class="cta-btn"><i class="fa-solid fa-link"></i> Loading…</a>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="nva-card reveal reveal-right" data-immediate="1" data-section="announce-right">
                <div class="nva-head"><i class="fa-solid fa-megaphone"></i> <span>Announcements</span></div>
                <div class="nva-body">
                  <ul class="nva-list" id="announcementList">
                    <li><i class="fa-solid fa-bell"></i> <span>Loading…</span></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= ACHIEVEMENTS, STUDENTS ACTIVITY, PLACEMENT (LAZY) ================= --}}
        <section class="info-boxes reveal" data-lazy-key="achvRow">
          <div class="row g-3">
            <div class="col-lg-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-trophy"></i> Achievements</h5>
                <ul id="achievementList">
                  <li><i class="fa-solid fa-medal"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-users"></i> Students Activity</h5>
                <ul id="activityList">
                  <li><i class="fa-solid fa-calendar"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="info-box">
                <h5><i class="fa-solid fa-briefcase"></i> Placement Notice</h5>
                <ul id="placementList2">
                  <li><i class="fa-solid fa-building"></i> <span>Loading…</span></li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= COURSES OFFERED (LAZY) ================= --}}
        <section class="courses-section reveal" data-lazy-key="courses">
          <h2>Courses Offered</h2>
          <div class="row g-4" id="coursesContainer">
            <div class="col-lg-3 col-md-6">
              <div class="course-card">
                <img id="courseFallbackImage" alt="Course" class="course-img">
                <h3 class="course-title">Loading…</h3>
                <p class="course-desc">Please wait…</p>
                <div class="course-links">
                  <a href="#" class="course-link">Vision & Mission</a>
                  <a href="#" class="course-link">PEO, PSO, PO</a>
                  <a href="#" class="course-link">Faculty</a>
                  <a href="#" class="course-link">Department</a>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= STATISTICS (LAZY) ================= --}}
        <section class="stats-section reveal" id="statsSection" data-lazy-key="stats">
          <div class="stats-head">
            <h2 id="statsTitle">Key Stats</h2>
          </div>
          <div class="row g-4" id="statsRow">
            <div class="col-lg-3 col-6">
              <div class="stat-item">
                <div class="stat-icon"><i class="fa-solid fa-chart-column"></i></div>
                <div class="stat-num" data-count="0">0</div>
                <div class="stat-label">Loading…</div>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= TESTIMONIALS (LAZY) ================= --}}
        <section class="testimonial-section reveal" data-lazy-key="testimonials">
          <h2>Successful Entrepreneurs</h2>
          <div class="row g-4" id="testimonialContainer">
            <div class="col-lg-6">
              <div class="testimonial-card">
                <img id="testimonialFallbackAvatar" alt="Alumni" class="testimonial-avatar">
                <div class="testimonial-text">Loading…</div>
                <div class="testimonial-name">—</div>
                <div class="testimonial-role">—</div>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= ALUMNI SPEAK (LAZY) ================= --}}
        <section class="alumni-section reveal" data-lazy-key="alumni">
          <h2 id="alumniSpeakTitle">Alumni Speak</h2>
          <div class="row g-4" id="alumniVideoContainer">
            <div class="col-lg-4 col-md-6">
              <div class="alumni-video-card">
                <iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ" loading="lazy" allowfullscreen></iframe>
              </div>
            </div>
          </div>
        </section>

        {{-- ================= SUCCESS STORIES (LAZY) ================= --}}
        <section class="success-section reveal" data-lazy-key="success">
          <h2>Success Stories</h2>
          <div class="success-scroller" id="successStoriesContainer">
            <div class="success-scroller-item">
              <div class="success-card">
                <img id="successFallbackImage" alt="Success" class="success-img">
                <div class="success-desc">Loading…</div>
                <div class="success-name">—</div>
                <div class="success-role">—</div>
              </div>
            </div>
          </div>
        </section>

        <section class="recruiters-section reveal" data-anim="up">
          <div class="recruiters-wrap">
            @include('modules.ourRecruiters.viewAllOurRecruiters')
          </div>
        </section>

      </div> {{-- End of home-sections-container --}}
    </div>
  </main>

  {{-- Footer --}}
  @include('landing.components.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  /**
   * ✅ This file calls ONLY the routes listed in $homeApis:
   * - /notice-marquee, /hero-carousel, /quick-links, /notice-board
   * - /activities, /placement-notices, /stats, /courses
   * - /successful-entrepreneurs, /alumni-speak, /success-stories
   *
   * ✅ Recruiters dynamic API removed (full recruiters module is included in Blade).
   *
   * ✅ PERFORMANCE: below-fold loads only on scroll (IntersectionObserver)
   * ✅ UX: page-loader + richer animations
   */

  const HOME_APIS = @json($homeApis);

  /* ✅ NEW: Notice marquee GIF from frontend (public/assets/...) */
  const NOTICE_MARQUEE_GIF_SRC = @json(asset('assets/media/noticeMarquee/new.gif'));

  /* Attach common query params to every API call if present in URL */
  const PAGE_QS = new URLSearchParams(window.location.search);
  const deptParam  = (PAGE_QS.get('department') || '').trim();
  const limitParam = (PAGE_QS.get('limit') || '').trim();

  function withParams(u){
    const raw = String(u || '').trim();
    if(!raw) return raw;

    try{
      const url = new URL(raw, window.location.origin);
      if(deptParam) url.searchParams.set('department', deptParam);
      if(limitParam) url.searchParams.set('limit', limitParam);
      return url.toString();
    }catch(e){
      const qs = [];
      if(deptParam)  qs.push('department=' + encodeURIComponent(deptParam));
      if(limitParam) qs.push('limit=' + encodeURIComponent(limitParam));
      if(!qs.length) return raw;
      return raw + (raw.includes('?') ? '&' : '?') + qs.join('&');
    }
  }

  /* =========================
    ✅ Home Contact Popup (every refresh)
    ✅ FIXED: popup scroll starts at top + allow scrolling inside popup
  ========================= */
  const HOME_POPUP = (() => {
    const el = document.getElementById('homePopup');
    if(!el) return { open(){}, close(){} };

    const closeEls = el.querySelectorAll('[data-home-popup-close="1"]');

    const open = () => {
      if(el.classList.contains('is-open')) return;

      el.classList.add('is-open');
      el.setAttribute('aria-hidden','false');

      try{
        el.scrollTop = 0;
        const body = el.querySelector('.home-popup-body');
        if(body) body.scrollTop = 0;
      }catch(e){}

      document.body.style.overflow = 'hidden';
    };

    const close = () => {
      el.classList.remove('is-open');
      el.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    };

    closeEls.forEach(btn => btn.addEventListener('click', close));

    document.addEventListener('keydown', (e) => {
      if(e.key === 'Escape' && el.classList.contains('is-open')) close();
    });

    return { open, close };
  })();

  let __HOME_POPUP_SHOWN = false;
  function showHomePopupOnce(){
    if(__HOME_POPUP_SHOWN) return;
    __HOME_POPUP_SHOWN = true;
    setTimeout(() => HOME_POPUP.open(), 250);
  }

  /* =========================
    ✅ Page Loader controls
  ========================= */
  const LOADER = {
    root: document.getElementById('pageLoader'),
    bar:  document.getElementById('pageLoaderBar'),
    text: document.getElementById('pageLoaderText'),
    set(pct, label){
      if(this.bar) this.bar.style.width = Math.max(6, Math.min(100, pct || 0)) + '%';
      if(this.text) this.text.textContent = String(label || 'Loading…');
    },
    done(){
      if(!this.root) return;
      this.root.classList.add('is-done');
      this.root.setAttribute('aria-hidden','true');

      try{ showHomePopupOnce(); }catch(e){}
    }
  };

  setTimeout(() => { LOADER.done(); }, 12000);

  /* =========================
    SVG placeholders (no 404 ever)
  ========================= */
  function svgDataUri(svg){
    return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
  }
  const PLACEHOLDERS = {
    avatar: svgDataUri(`
      <svg xmlns="http://www.w3.org/2000/svg" width="160" height="160">
        <defs>
          <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#9E363A" stop-opacity=".16"/>
            <stop offset="1" stop-color="#C94B50" stop-opacity=".08"/>
          </linearGradient>
        </defs>
        <rect width="100%" height="100%" rx="80" fill="url(#g)"/>
        <circle cx="80" cy="62" r="30" fill="#9E363A" opacity=".35"/>
        <rect x="34" y="98" width="92" height="44" rx="22" fill="#6B2528" opacity=".28"/>
      </svg>
    `),
    image: svgDataUri(`
      <svg xmlns="http://www.w3.org/2000/svg" width="800" height="450">
        <defs>
          <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#9E363A" stop-opacity=".18"/>
            <stop offset="1" stop-color="#C94B50" stop-opacity=".08"/>
          </linearGradient>
        </defs>
        <rect width="100%" height="100%" rx="24" fill="url(#g)"/>
        <path d="M140 310 L300 180 L420 280 L520 220 L680 330 L680 370 L140 370 Z" fill="#9E363A" opacity=".25"/>
        <circle cx="310" cy="170" r="26" fill="#C94B50" opacity=".35"/>
        <text x="50%" y="54%" text-anchor="middle" font-family="Arial" font-size="26" fill="#6B2528" opacity=".8">Image</text>
      </svg>
    `)
  };

  document.getElementById('testimonialFallbackAvatar')?.setAttribute('src', PLACEHOLDERS.avatar);
  document.getElementById('successFallbackImage')?.setAttribute('src', PLACEHOLDERS.image);
  document.getElementById('courseFallbackImage')?.setAttribute('src', PLACEHOLDERS.image);

  function attachImgFallback(img, type){
    if(!img) return;
    img.addEventListener('error', () => {
      img.src = (type === 'avatar') ? PLACEHOLDERS.avatar : PLACEHOLDERS.image;
    }, { once: true });
  }

  /* =========================
    Helpers
  ========================= */
  function isObj(v){ return v && typeof v === 'object' && !Array.isArray(v); }
  function esc(s){
    return String(s ?? '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function chunkArray(arr, size){
    const out = [];
    const a = Array.isArray(arr) ? arr : [];
    const n = Math.max(1, parseInt(size || 1, 10) || 1);
    for(let i = 0; i < a.length; i += n) out.push(a.slice(i, i + n));
    return out;
  }

  function safeHref(u){
    const s0 = String(u ?? '').trim();
    if(!s0) return '#';
    if(/^https?:\/\//i.test(s0)) return s0;

    let s = s0.startsWith('/') ? s0 : ('/' + s0);
    s = s.replace(/\/placement_notices(?=\/|$)/gi, '/placement-notices');
    s = s.replace(/\/career_notices(?=\/|$)/gi, '/career-notices');
    s = s.replace(/\/why_us(?=\/|$)/gi, '/why-us');
    s = s.replace(/\/student_activities(?=\/|$)/gi, '/student-activities');
    return s;
  }

  function unwrapApi(json){
    return (json && typeof json === 'object' && json.data && typeof json.data === 'object')
      ? json.data
      : json;
  }

  function pickNoticeMarqueePayload(j){
    const root = unwrapApi(j || {});
    return root.notice_marquee || root.item || root;
  }

  let nmAnim = null;

  function renderNoticeMarquee(apiJson){
    const payload = pickNoticeMarqueePayload(apiJson);
    const itemsRaw = payload?.items ?? payload?.notice_items_json ?? [];
    const settings = payload?.settings ?? payload ?? {};

    const viewport = document.getElementById('noticeMarqueeViewport');
    const track = document.getElementById('noticeMarqueeTrack');
    if(!viewport || !track) return;

    const items = (Array.isArray(itemsRaw) ? itemsRaw : []).map(it => {
      if(typeof it === 'string') return { text: it, url: '' };
      if(it && typeof it === 'object'){
        return {
          text: (it.text ?? it.title ?? it.label ?? '').toString().trim(),
          url:  (it.url  ?? it.link  ?? it.href  ?? '').toString().trim(),
        };
      }
      return { text:'', url:'' };
    }).filter(x => x.text);

    const gifStart = NOTICE_MARQUEE_GIF_SRC
      ? `<img class="nm-gif nm-gif-start" src="${esc(NOTICE_MARQUEE_GIF_SRC)}" alt="" aria-hidden="true">`
      : '';

    const gifEnd = NOTICE_MARQUEE_GIF_SRC
      ? `<img class="nm-gif nm-gif-end" src="${esc(NOTICE_MARQUEE_GIF_SRC)}" alt="" aria-hidden="true">`
      : '';

    const html = items.length
      ? items.map((x, i) => {
          const t = esc(x.text);
          const u = x.url ? safeHref(x.url) : '';
          const node = u
            ? `<a class="nm-link" href="${esc(u)}">${t}</a>`
            : `<span class="nm-text">${t}</span>`;

          // ✅ REQUIRED FORMAT:
          // (gif) item1 .... (gif) item2 .... (gif)  -> last item gets suffix gif
          const isLast = (i === items.length - 1);
          const tail = isLast ? gifEnd : `<span class="nm-sep">•</span>`;
          return `${gifStart}${node}${tail}`;
        }).join('')
      : `<span class="nm-text">No notices available.</span>`;

    const loop = parseInt(settings.loop ?? 1, 10) === 1;
    track.innerHTML = `
      <div class="nm-run" data-run="1">${html}</div>
      ${loop ? `<div class="nm-run" data-run="2" aria-hidden="true">${html}</div>` : ``}
    `;

    if(nmAnim){ try{ nmAnim.cancel(); }catch(e){} nmAnim = null; }
    track.style.transform = 'translateX(0px)';

    const auto = parseInt(settings.auto_scroll ?? 1, 10) === 1;
    if(!auto) return;

    const dir = String(settings.direction ?? 'left').toLowerCase() === 'right' ? 'right' : 'left';
    const pxPerSec = Math.max(20, parseInt(settings.scroll_speed ?? 60, 10) || 60);
    const latency = Math.max(0, parseInt(settings.scroll_latency_ms ?? 0, 10) || 0);
    const pauseHover = parseInt(settings.pause_on_hover ?? 1, 10) === 1;

    requestAnimationFrame(() => {
      const run1 = track.querySelector('[data-run="1"]');
      if(!run1) return;

      const distance = run1.scrollWidth;
      if(!distance) return;

      const duration = Math.max(1200, Math.round((distance / pxPerSec) * 1000));
      const from = (dir === 'left') ? 0 : -distance;
      const to   = (dir === 'left') ? -distance : 0;

      const playOnce = () => {
        nmAnim = track.animate(
          [{ transform: `translateX(${from}px)` }, { transform: `translateX(${to}px)` }],
          { duration, iterations: 1, easing: 'linear', fill: 'forwards' }
        );

        nmAnim.onfinish = () => {
          if(loop){
            setTimeout(() => {
              track.style.transform = `translateX(${from}px)`;
              playOnce();
            }, latency);
          }
        };
      };

      track.style.transform = `translateX(${from}px)`;
      playOnce();

      viewport.onmouseenter = null;
      viewport.onmouseleave = null;
      if(pauseHover){
        viewport.onmouseenter = () => nmAnim && nmAnim.pause();
        viewport.onmouseleave = () => nmAnim && nmAnim.play();
      }
    });
  }

  async function loadNoticeMarquee(){
    const url = withParams(HOME_APIS.noticeMarquee);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    renderNoticeMarquee(json);
  }

  function decodeHtmlEntities(str){
    const t = document.createElement('textarea');
    t.innerHTML = String(str ?? '');
    return t.value;
  }

  function safeInlineHtml(html){
    const input = String(html ?? '');
    if(!input) return '';
    try{
      const parser = new DOMParser();
      const doc = parser.parseFromString(`<div>${input}</div>`, 'text/html');
      const root = doc.body.firstElementChild;

      const ALLOW = new Set(['B','I','U','STRONG','EM','BR','SPAN','P','UL','OL','LI']);
      const walk = (node) => {
        [...node.children].forEach(el => {
          if(!ALLOW.has(el.tagName)){
            const txt = doc.createTextNode(el.textContent || '');
            el.replaceWith(txt);
            return;
          }
          [...el.attributes].forEach(a => el.removeAttribute(a.name));
          walk(el);
        });
      };
      walk(root);
      return root.innerHTML;
    }catch(e){
      return esc(input);
    }
  }

  function normalizeRichText(v){
    const decoded = decodeHtmlEntities(v);
    return safeInlineHtml(decoded);
  }

  function toEmbedUrl(url){
    const u = String(url ?? '').trim();
    if(!u) return '';
    if(u.includes('youtube-nocookie.com/embed/')) return u;

    const m1 = u.match(/youtu\.be\/([a-zA-Z0-9_-]{6,})/);
    if(m1) return `https://www.youtube-nocookie.com/embed/${m1[1]}`;

    const m2 = u.match(/[?&]v=([a-zA-Z0-9_-]{6,})/);
    if(m2) return `https://www.youtube-nocookie.com/embed/${m2[1]}`;

    const m3 = u.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{6,})/);
    if(m3) return `https://www.youtube-nocookie.com/embed/${m3[1]}`;

    return u;
  }

  function initCarouselInstance(el, opts){
    if(!el || !window.bootstrap?.Carousel) return;
    try{
      const existing = bootstrap.Carousel.getInstance(el);
      if(existing) existing.dispose();
    }catch(e){}
    try{
      new bootstrap.Carousel(el, opts || {});
    }catch(e){}
  }

  function initSmoothAutoScrollList(listId) {
    const ul = document.getElementById(listId);
    if(!ul) return;

    ul.classList.remove('scrolling-upwards');

    const lis = Array.from(ul.querySelectorAll('li'));
    if(lis.length <= 7) {
      ul.classList.remove('autoscroll');
      return;
    }

    ul.classList.add('autoscroll');

    const children = Array.from(ul.children);
    children.forEach(ch => {
      const clone = ch.cloneNode(true);
      clone.setAttribute('data-clone','1');
      ul.appendChild(clone);
    });

    setTimeout(() => {
      ul.classList.add('scrolling-upwards');
    }, 100);
  }

  function initRevealObservers(){
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
    if(reduce){
      document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-in'));
      return;
    }

    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if(e.isIntersecting){
          e.target.classList.add('is-in');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.reveal:not(.is-in):not([data-lazy-key])').forEach(el => io.observe(el));
  }

  function animateCounters(){
    const els = document.querySelectorAll('.stat-num[data-count]');
    els.forEach(el => {
      if(el.dataset.animated === '1') return;

      const target = parseInt(String(el.getAttribute('data-count') || '0').replace(/[, ]/g,''), 10) || 0;
      const duration = 1200;
      const start = performance.now();

      function tick(t){
        const p = Math.min(1, (t - start) / duration);
        const val = Math.floor(target * p);
        el.textContent = val.toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
        else el.dataset.animated = '1';
      }

      el.textContent = '0';
      requestAnimationFrame(tick);
    });
  }
  let statsObserver = null;
  function attachStatsObserver(){
    const statsSection = document.getElementById('statsSection');
    if(!statsSection) return;
    if(statsObserver) statsObserver.disconnect();

    statsObserver = new IntersectionObserver((entries) => {
      if (entries.some(e => e.isIntersecting)){
        animateCounters();
        statsObserver.disconnect();
      }
    }, { threshold: 0.25 });

    statsObserver.observe(statsSection);
  }

  async function fetchJson(url){
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if(!res.ok) throw new Error(`HTTP ${res.status} @ ${url}`);
    return await res.json();
  }
  function unwrap(json){
    if(json && isObj(json.data)) return json.data;
    return json;
  }

  const SECTION_CACHE = new Map();
  function loadSection(key){
    if(SECTION_CACHE.has(key)) return SECTION_CACHE.get(key);

    const baseUrl = HOME_APIS[key];
    if(!baseUrl){
      const p = Promise.reject(new Error(`Missing HOME_APIS["${key}"]`));
      SECTION_CACHE.set(key, p);
      return p;
    }

    const url = withParams(baseUrl);
    const p = fetchJson(url).then(unwrap);
    SECTION_CACHE.set(key, p);
    return p;
  }

  function syncHeroBackgrounds(){
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    document.querySelectorAll('.hero-slide[data-hero-desktop]').forEach(el => {
      const d = el.getAttribute('data-hero-desktop') || '';
      const m = el.getAttribute('data-hero-mobile') || '';
      const url = (isMobile && m) ? m : (d || m);
      if(url) el.style.backgroundImage = `url('${url}')`;
    });
  }

  function renderHero(hero){
    const slidesEl = document.getElementById('heroSlides');
    const indEl = document.getElementById('heroIndicators');
    const heroRoot = document.getElementById('homeHero');
    if(!slidesEl || !indEl || !heroRoot) return;

    const items = (hero && Array.isArray(hero.items)) ? hero.items : [];
    const settings = (hero && isObj(hero.settings)) ? hero.settings : {};

    const autoplay = Number(settings.autoplay ?? 1) === 1;
    const interval = parseInt(settings.autoplay_delay_ms ?? 5000, 10) || 5000;

    const transition = String(settings.transition || 'slide').toLowerCase();
    const transitionMsRaw = parseInt(settings.transition_ms ?? 600, 10);
    const transitionMs = Number.isFinite(transitionMsRaw) ? Math.max(0, transitionMsRaw) : 600;

    heroRoot.classList.toggle('carousel-fade', transition === 'fade');
    heroRoot.style.setProperty('--hero-transition-ms', `${transitionMs}ms`);

    if(autoplay){
      heroRoot.setAttribute('data-bs-ride', 'carousel');
      heroRoot.setAttribute('data-bs-interval', String(interval));
    }else{
      heroRoot.removeAttribute('data-bs-ride');
      heroRoot.setAttribute('data-bs-interval', 'false');
    }
    heroRoot.setAttribute('data-bs-wrap', (Number(settings.loop ?? 1) === 1) ? 'true' : 'false');
    heroRoot.setAttribute('data-bs-pause', (Number(settings.pause_on_hover ?? 1) === 1) ? 'hover' : 'false');

    const showArrows = Number(settings.show_arrows ?? 1) === 1;
    const showDots   = Number(settings.show_dots ?? 1) === 1;

    const prevBtn = heroRoot.querySelector('.carousel-control-prev');
    const nextBtn = heroRoot.querySelector('.carousel-control-next');
    if(prevBtn) prevBtn.style.display = showArrows ? '' : 'none';
    if(nextBtn) nextBtn.style.display = showArrows ? '' : 'none';
    indEl.style.display = showDots ? '' : 'none';

    if(!items.length){
      return;
    }

    indEl.innerHTML = items.map((_, i) => `
      <button type="button" data-bs-target="#homeHero" data-bs-slide-to="${i}" class="${i===0?'active':''}" ${i===0?'aria-current="true"':''} aria-label="Slide ${i+1}"></button>
    `).join('');

    slidesEl.innerHTML = items.map((it, i) => {
      const desktop = String(it.image_url ?? '').trim();
      const mobile  = String(it.mobile_image_url ?? '').trim();
      const alt     = String(it.alt_text ?? '').trim();
      const overlayHtml = safeInlineHtml(it.overlay_text ?? '');

      const bgStyle = (desktop || mobile)
        ? `background-image:url('${esc(desktop || mobile)}');`
        : `background-image:linear-gradient(135deg, rgba(158,54,58,.95), rgba(107,37,40,.92));`;

      return `
        <div class="carousel-item ${i===0?'active':''}">
          <div class="hero-slide"
               data-hero-desktop="${esc(desktop)}"
               data-hero-mobile="${esc(mobile)}"
               style="${bgStyle}">
            <div class="hero-inner">
              <div class="hero-kicker">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>${alt ? esc(alt) : 'Welcome'}</span>
              </div>
              <div class="hero-title">${overlayHtml || '—'}</div>
              <div class="hero-actions">
                <a href="${esc(safeHref('/admissions'))}" class="btn btn-hero">Apply Now</a>
                <a href="${esc(safeHref('/courses'))}" class="btn btn-hero">Explore Programs</a>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    syncHeroBackgrounds();
    window.addEventListener('resize', syncHeroBackgrounds, { passive: true });

    initCarouselInstance(heroRoot, {
      interval: autoplay ? interval : false,
      pause: (Number(settings.pause_on_hover ?? 1) === 1) ? 'hover' : false,
      wrap: (Number(settings.loop ?? 1) === 1),
      ride: autoplay ? 'carousel' : false
    });
  }

  function setList(listId, items, iconClass, emptyText, opts = {}){
    const el = document.getElementById(listId);
    if(!el) return;

    el.querySelectorAll('[data-clone="1"]').forEach(n => n.remove());

    const arr = Array.isArray(items) ? items : [];
    const max = Number(opts.max ?? 50);

    if(!arr.length){
      el.classList.remove('autoscroll', 'scrolling-upwards');
      el.innerHTML = `<li><i class="${esc(iconClass)}"></i> <span>${esc(emptyText || 'No items available')}</span></li>`;
      return;
    }

    const sliced = arr.slice(0, max).map(it => {
      const title = it.title ?? it.text ?? it.name ?? '-';

      let url = it.url ?? it.href ?? it.link ?? '';
      if(!String(url || '').trim() && typeof opts.buildUrl === 'function'){
        try{ url = opts.buildUrl(it) || ''; }catch(e){ url = ''; }
      }

      const hasLink = String(url || '').trim().length > 0;
      const href = hasLink ? safeHref(url) : '';
      return { title, hasLink, href };
    });

    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

    const renderAll = () => {
      el.innerHTML = sliced.map(x => `
        <li>
          <i class="${esc(iconClass)}"></i>
          ${x.hasLink ? `<a href="${esc(x.href)}">${esc(x.title)}</a>` : `<span>${esc(x.title)}</span>`}
        </li>
      `).join('');

      setTimeout(() => initSmoothAutoScrollList(listId), 100);
    };

    if(reduce || opts.stagger === false){
      renderAll();
      return;
    }

    el.innerHTML = '';
    sliced.forEach((x, i) => {
      setTimeout(() => {
        const li = document.createElement('li');
        li.innerHTML = `
          <i class="${esc(iconClass)}"></i>
          ${x.hasLink ? `<a href="${esc(x.href)}">${esc(x.title)}</a>` : `<span>${esc(x.title)}</span>`}
        `;
        el.appendChild(li);

        if(i === sliced.length - 1){
          setTimeout(() => initSmoothAutoScrollList(listId), 150);
        }
      }, i * 45);
    });
  }

  function renderCenterIframe(center){
    const titleEl = document.getElementById('centerIframeTitle');
    const videoEl = document.getElementById('mainVideoContainer');
    const btnEl   = document.getElementById('centerIframeButtons');

    if(titleEl) titleEl.textContent = (center && center.title) ? String(center.title) : '—';

    if(videoEl){
      const embed = toEmbedUrl(center?.iframe_url || '');
      if(embed){
        videoEl.innerHTML = `
          <iframe
            src="${esc(embed)}"
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen></iframe>
        `;
      }else{
        videoEl.innerHTML = `<div class="d-flex align-items-center justify-content-center text-white" style="position:absolute;inset:0;">No video available</div>`;
      }
    }

    if(btnEl){
      const buttons = Array.isArray(center?.buttons_json) ? center.buttons_json : [];
      if(!buttons.length){
        btnEl.innerHTML = `<a href="#" class="cta-btn"><i class="fa-solid fa-link"></i> No actions</a>`;
        return;
      }

      const iconFor = (t) => {
        const s = String(t || '').toLowerCase();
        if(s.includes('counsel')) return 'fa-solid fa-calendar';
        if(s.includes('admission')) return 'fa-solid fa-pen';
        if(s.includes('fee') || s.includes('payment')) return 'fa-solid fa-credit-card';
        if(s.includes('tour')) return 'fa-solid fa-building';
        return 'fa-solid fa-link';
      };

      btnEl.innerHTML = buttons
        .slice()
        .sort((a,b)=>(Number(a.sort_order||0)-Number(b.sort_order||0)))
        .map((b, idx) => {
          const cls = (idx >= 2) ? 'cta-btn btn-secondary' : 'cta-btn';
          return `<a href="${esc(safeHref(b.url))}" class="${cls}" target="_blank" rel="noopener">
            <i class="${esc(iconFor(b.text))}"></i> ${esc(b.text || 'Open')}
          </a>`;
        }).join('');
    }
  }

  function renderStats(stats){
    const section = document.getElementById('statsSection');
    const titleEl = document.getElementById('statsTitle');
    const rowEl   = document.getElementById('statsRow');
    if(!section || !rowEl) return;

    const itemsRaw = Array.isArray(stats?.stats_items_json) ? stats.stats_items_json : [];
    const items = itemsRaw.slice().sort((a,b)=>(Number(a.sort_order||0)-Number(b.sort_order||0)));

    const title = stats?.metadata?.section_title || stats?.metadata?.title || 'Key Stats';
    if(titleEl) titleEl.textContent = String(title);

    const bg = String(stats?.background_image_url || '').trim();
    if(bg){
      section.classList.add('has-bg');
      section.style.backgroundImage = `linear-gradient(135deg, rgba(255,255,255,.88), rgba(255,255,255,.88)), url('${bg}')`;
    }else{
      section.classList.remove('has-bg');
      section.style.backgroundImage = '';
    }

    if(!items.length){
      rowEl.innerHTML = `<div class="col-12"><p class="muted-note">No stats published.</p></div>`;
      return;
    }

    const toStatCard = (it) => {
      const label = it.label || it.key || '—';
      const value = String(it.value ?? '0').replace(/[^\d]/g,'') || '0';
      const icon  = it.icon_class ? String(it.icon_class) : 'fa-solid fa-chart-column';

      return `
        <div class="col-lg-3 col-6">
          <div class="stat-item">
            <div class="stat-icon"><i class="${esc(icon)}"></i></div>
            <div class="stat-num" data-count="${esc(value)}">0</div>
            <div class="stat-label">${esc(label)}</div>
          </div>
        </div>
      `;
    };

    if(items.length <= 4){
      rowEl.innerHTML = items.slice(0,4).map(toStatCard).join('');
      attachStatsObserver();
      return;
    }

    const settings = {
      autoScroll: Boolean(stats?.auto_scroll ?? true),
      interval: parseInt(stats?.scroll_latency_ms ?? 3000, 10) || 3000,
      wrap: Boolean(stats?.loop ?? true),
      showArrows: Boolean(stats?.show_arrows ?? true),
      showDots: Boolean(stats?.show_dots ?? false),
    };

    const groups = chunkArray(items, 4);
    const hasMulti = groups.length > 1;

    rowEl.innerHTML = `
      <div class="col-12">
        <div id="statsCarousel" class="carousel slide stats-carousel controls-out indicators-out"
             ${settings.autoScroll ? 'data-bs-ride="carousel"' : ''}
             data-bs-interval="${settings.autoScroll ? esc(settings.interval) : 'false'}"
             data-bs-wrap="${settings.wrap ? 'true' : 'false'}"
             data-bs-pause="${settings.autoScroll ? 'hover' : 'false'}">

          <div class="carousel-inner">
            ${groups.map((chunk, idx) => `
              <div class="carousel-item ${idx===0?'active':''}">
                <div class="row g-4 justify-content-center">
                  ${chunk.map(toStatCard).join('')}
                </div>
              </div>
            `).join('')}
          </div>

          <div class="carousel-indicators" style="${(settings.showDots && hasMulti) ? '' : 'display:none'}">
            ${groups.map((_, i) => `
              <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="${i}"
                      class="${i===0?'active':''}" ${i===0?'aria-current="true"':''} aria-label="Slide ${i+1}"></button>
            `).join('')}
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#statsCarousel" data-bs-slide="prev" style="${(settings.showArrows && hasMulti) ? '' : 'display:none'}">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#statsCarousel" data-bs-slide="next" style="${(settings.showArrows && hasMulti) ? '' : 'display:none'}">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    `;

    const carouselEl = document.getElementById('statsCarousel');
    initCarouselInstance(carouselEl, {
      interval: settings.autoScroll ? settings.interval : false,
      ride: settings.autoScroll ? 'carousel' : false,
      pause: settings.autoScroll ? 'hover' : false,
      wrap: settings.wrap
    });

    attachStatsObserver();
  }

  function renderTestimonials(arr){
    const container = document.getElementById('testimonialContainer');
    if(!container) return;

    const items = Array.isArray(arr) ? arr : [];
    if(!items.length){
      container.innerHTML = `<div class="col-12"><p class="muted-note">No testimonials available.</p></div>`;
      return;
    }

    const cleaned = items.slice(0, 12);
    const isMobile = window.innerWidth < 768;
    const perSlide = isMobile ? 1 : 2;

    const groups = chunkArray(cleaned, perSlide);
    const hasMulti = groups.length > 1;

    container.innerHTML = `
      <div class="col-12">
        <div id="entrepreneursCarousel" class="carousel slide testimonial-carousel controls-out indicators-out"
             data-bs-ride="carousel"
             data-bs-interval="6000"
             data-bs-wrap="true"
             data-bs-pause="hover">

          <div class="carousel-inner">
            ${groups.map((chunk, idx) => `
              <div class="carousel-item ${idx===0?'active':''}">
                <div class="row g-4">
                  ${chunk.map(item => {
                    const avatar = item.avatar || item.photo_url || item.image_url || PLACEHOLDERS.avatar;
                    const rawText = item.text || item.description || item.quote || '';
                    const richText = normalizeRichText(rawText);

                    const name   = item.name || item.title || '—';
                    const company = item.company_name || item.company || '';
                    const ttl = item.title && item.title !== name ? item.title : '';
                    const role = item.role || [ttl, company].filter(Boolean).join(', ');

                    return `
                      <div class="col-lg-6">
                        <div class="testimonial-card">
                          <img src="${esc(avatar)}" loading="lazy" alt="${esc(name)}" class="testimonial-avatar">
                          <div class="testimonial-text">${richText || esc(rawText || '—')}</div>
                          <div class="testimonial-name">${esc(name)}</div>
                          <div class="testimonial-role">${esc(role || '—')}</div>
                        </div>
                      </div>
                    `;
                  }).join('')}
                </div>
              </div>
            `).join('')}
          </div>

          <div class="carousel-indicators" style="${hasMulti ? '' : 'display:none'}">
            ${groups.map((_, i) => `
              <button type="button" data-bs-target="#entrepreneursCarousel" data-bs-slide-to="${i}"
                      class="${i===0?'active':''}" ${i===0?'aria-current="true"':''} aria-label="Slide ${i+1}"></button>
            `).join('')}
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#entrepreneursCarousel" data-bs-slide="prev" style="${hasMulti ? '' : 'display:none'}">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#entrepreneursCarousel" data-bs-slide="next" style="${hasMulti ? '' : 'display:none'}">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>

        </div>
      </div>
    `;

    container.querySelectorAll('img.testimonial-avatar').forEach(img => attachImgFallback(img, 'avatar'));

    const carouselEl = document.getElementById('entrepreneursCarousel');
    initCarouselInstance(carouselEl, { interval: 6000, ride: 'carousel', pause: 'hover', wrap: true });
  }

  function renderAlumniSpeak(alumni){
    const titleEl = document.getElementById('alumniSpeakTitle');
    const container = document.getElementById('alumniVideoContainer');
    if(!container) return;

    if(titleEl) titleEl.textContent = alumni?.title ? String(alumni.title) : 'Alumni Speak';

    const vidsRaw = Array.isArray(alumni?.iframe_urls_json) ? alumni.iframe_urls_json : [];
    const vids = vidsRaw.slice().sort((a,b)=>(Number(a.sort_order||0)-Number(b.sort_order||0))).slice(0, 12);

    if(!vids.length){
      container.innerHTML = `<div class="col-12"><p class="muted-note">No alumni videos available.</p></div>`;
      return;
    }

    const isMobile = window.innerWidth < 768;
    const perSlide = isMobile ? 1 : 3;

    if(vids.length <= perSlide){
      container.innerHTML = vids.slice(0, 6).map(v => {
        const embed = v.video_id
          ? `https://www.youtube-nocookie.com/embed/${String(v.video_id)}`
          : toEmbedUrl(v.url || '');
        const ttl = v.title || 'Video';

        return `
          <div class="col-lg-4 col-md-6">
            <div class="alumni-video-card">
              <iframe
                src="${esc(embed)}"
                title="${esc(ttl)}"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
            </div>
          </div>
        `;
      }).join('');
      return;
    }

    const groups = chunkArray(vids, perSlide);
    const hasMulti = groups.length > 1;

    container.innerHTML = `
      <div class="col-12">
        <div id="alumniCarousel" class="carousel slide alumni-carousel controls-out"
             data-bs-interval="false"
             data-bs-wrap="false">
          <div class="carousel-inner">
            ${groups.map((chunk, idx) => `
              <div class="carousel-item ${idx===0?'active':''}">
                <div class="row g-4">
                  ${chunk.map(v => {
                    const embed = v.video_id
                      ? `https://www.youtube-nocookie.com/embed/${String(v.video_id)}`
                      : toEmbedUrl(v.url || '');
                    const ttl = v.title || 'Video';
                    return `
                      <div class="col-lg-4 col-md-6">
                        <div class="alumni-video-card">
                          <iframe
                            src="${esc(embed)}"
                            title="${esc(ttl)}"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                        </div>
                      </div>
                    `;
                  }).join('')}
                </div>
              </div>
            `).join('')}
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#alumniCarousel" data-bs-slide="prev" style="${hasMulti ? '' : 'display:none'}">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#alumniCarousel" data-bs-slide="next" style="${hasMulti ? '' : 'display:none'}">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    `;

    const carouselEl = document.getElementById('alumniCarousel');
    initCarouselInstance(carouselEl, { interval: false, ride: false, pause: false, wrap: false });
  }

  function renderSuccessStories(arr){
    const container = document.getElementById('successStoriesContainer');
    if(!container) return;

    const items = Array.isArray(arr) ? arr : [];
    if(!items.length){
      container.innerHTML = `<p class="muted-note w-100">No success stories available.</p>`;
      return;
    }

    container.innerHTML = items.slice(0, 12).map(story => {
      const img  = story.image_url || story.image || story.photo_url || PLACEHOLDERS.image;

      const rawDesc = story.description || story.text || '';
      const descHtml = normalizeRichText(rawDesc);

      const name = story.name || story.title || '—';
      const role = story.role || story.year || story.subtitle || '';

      const uuid = String(story.uuid || story.story_uuid || story.id || '').trim();
      const href = uuid ? safeHref(`/success-stories/view/${uuid}`) : '#';
      const tagOpen = uuid ? `<a class="success-card" href="${esc(href)}">` : `<div class="success-card">`;
      const tagClose = uuid ? `</a>` : `</div>`;

      return `
        <div class="success-scroller-item">
          ${tagOpen}
            <img src="${esc(img)}" loading="lazy" alt="${esc(name)}" class="success-img">
            <div class="success-desc">${descHtml || esc(rawDesc || '—')}</div>
            <div class="success-name">${esc(name)}</div>
            <div class="success-role">${esc(role || '—')}</div>
          ${tagClose}
        </div>
      `;
    }).join('');

    container.querySelectorAll('img.success-img').forEach(img => attachImgFallback(img, 'image'));
  }

  function renderCourses(arr){
    const container = document.getElementById('coursesContainer');
    if(!container) return;

    const items = Array.isArray(arr) ? arr : [];
    if(!items.length){
      container.innerHTML = `<div class="col-12"><p class="muted-note">Courses not available right now.</p></div>`;
      return;
    }

    container.innerHTML = items.slice(0, 8).map(course => {
      const img  = course.cover_image || course.image_url || course.image || PLACEHOLDERS.image;
      const name = course.title || course.name || 'Course';
      const desc = course.summary || course.blurb || course.description || '';

      const baseUrl = safeHref(course.url || course.vision_link || course.dept_link || '#');

      const links = Array.isArray(course.links) ? course.links : [
        { text: 'Vision & Mission', url: baseUrl },
        { text: 'PEO, PSO, PO',     url: baseUrl },
        { text: 'Faculty',          url: baseUrl },
        { text: 'Department',       url: baseUrl },
      ];

      return `
        <div class="col-lg-3 col-md-6">
          <div class="course-card">
            <img src="${esc(img)}" loading="lazy" alt="${esc(name)}" class="course-img">
            <h3 class="course-title">${esc(name)}</h3>
            <p class="course-desc">${esc(desc || '—')}</p>
            <div class="course-links">
              ${links.slice(0,4).map(l => `
                <a href="${esc(safeHref(l.url || l.href))}" class="course-link">${esc(l.text || l.title || 'Link')}</a>
              `).join('')}
            </div>
          </div>
        </div>
      `;
    }).join('');

    container.querySelectorAll('img.course-img').forEach(img => attachImgFallback(img, 'image'));
  }

  let FIRST_API_ERROR = null;
  function showApiAlert(err){
    if(FIRST_API_ERROR) return;
    FIRST_API_ERROR = err;

    const alertBox = document.getElementById('homeApiAlert');
    if(!alertBox) return;

    const lines = Object.entries(HOME_APIS || {}).map(([k, v]) => `${k}: ${withParams(v)}`);
    alertBox.style.display = '';
    alertBox.innerHTML = `
      Home API error. Please verify section endpoints in <code>$homeApis</code>.<br>
      <span style="font-weight:900">Error:</span> <code>${esc(err?.message || String(err))}</code>
      <pre>${esc(lines.join('\n'))}</pre>
    `;
  }

  async function loadImmediateSections(){
    LOADER.set(18, 'Loading hero carousel…');
    try{
      const p = await loadSection('hero');
      const hero = p.hero_carousel || p.hero || p;
      renderHero(hero);
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    LOADER.set(36, 'Loading notice marquee…');
    try{
      await loadNoticeMarquee();
    }catch(e){
      console.warn(e);
      showApiAlert(e);
      renderNoticeMarquee({ items: ['Welcome.'], settings: { auto_scroll: 0 } });
    }

    LOADER.set(56, 'Loading quick links…');
    try{
      const p = await loadSection('infoBoxes');

      setList('careerList',      p.career_notices, 'fa-solid fa-chevron-right', 'No career notices.', { stagger:true, max: 60 });
      setList('whyMsitList',     p.why_us,         'fa-solid fa-check',         'No highlights.',     { stagger:true, max: 60 });
      setList('scholarshipList', p.scholarships,   'fa-solid fa-gift',          'No scholarships.',   { stagger:true, max: 60 });
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    LOADER.set(78, 'Loading notice board…');
    try{
      const p = await loadSection('nvaRow');
      renderCenterIframe(p.center_iframe || p.centerIframe || p.center || null);

      setList('noticeList',       p.notices,       'fa-solid fa-caret-right', 'No notices.', { max: 80, stagger:true });
      setList('announcementList', p.announcements, 'fa-solid fa-caret-right', 'No announcements.', { max: 80, stagger:true });
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    LOADER.set(100, 'Almost done…');
    setTimeout(() => LOADER.done(), 250);
  }

  const LAZY_CONFIG = {
    stats: {
      load: () => loadSection('stats'),
      render: (payload) => renderStats(payload.stats || payload)
    },
    achvRow: {
      load: () => loadSection('achvRow'),
      render: (payload) => {
        setList('achievementList', payload.achievements,       'fa-solid fa-medal',    'No achievements.', { stagger:true, max: 80 });
        setList('activityList',    payload.student_activities, 'fa-solid fa-calendar', 'No activities.',   { stagger:true, max: 80 });

        loadSection('placementNotices')
          .then(p2 => {
            const data = p2.placement_notices || p2.items || p2;
            setList('placementList2', data, 'fa-solid fa-building', 'No placements.', {
              stagger:true, max: 80,
              buildUrl: (it) => {
                const slug = it.slug || it.uuid || it.id;
                if(!slug) return '';
                return `/placement-notices/${slug}`;
              }
            });
          })
          .catch(err => {
            console.warn(err);
            showApiAlert(err);
          });
      }
    },
    testimonials: {
      load: () => loadSection('testimonials'),
      render: (payload) => renderTestimonials(payload.successful_entrepreneurs || payload.items || payload)
    },
    alumni: {
      load: () => loadSection('alumni'),
      render: (payload) => renderAlumniSpeak(payload.alumni_speak || payload)
    },
    success: {
      load: () => loadSection('success'),
      render: (payload) => renderSuccessStories(payload.success_stories || payload.items || payload)
    },
    courses: {
      load: () => loadSection('courses'),
      render: (payload) => renderCourses(payload.courses || payload.items || payload)
    }
  };

  let storedTestimonials = null;
  let storedAlumni = null;
  let resizeTimeout;

  function handleResponsiveResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(async () => {
      if (storedTestimonials) renderTestimonials(storedTestimonials);
      if (storedAlumni) renderAlumniSpeak(storedAlumni);
    }, 250);
  }

  function initLazySections(){
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
    const sections = Array.from(document.querySelectorAll('[data-lazy-key]'));
    if(!sections.length) return;

    if(reduce){
      (async () => {
        for(const sec of sections){
          const key = sec.getAttribute('data-lazy-key');
          const conf = LAZY_CONFIG[key];
          if(!conf) continue;
          try{
            const payload = await conf.load();
            if (key === 'testimonials') storedTestimonials = payload.successful_entrepreneurs || payload.items || payload;
            if (key === 'alumni') storedAlumni = payload.alumni_speak || payload;

            conf.render(payload);
            sec.classList.add('is-in');
            sec.dataset.rendered = '1';
          }catch(e){
            console.warn(e);
            showApiAlert(e);
            sec.classList.add('is-in');
            sec.dataset.rendered = '1';
          }
        }
      })();
      return;
    }

    const io = new IntersectionObserver((entries) => {
      entries.forEach(async (e) => {
        if(!e.isIntersecting) return;

        const sec = e.target;
        const key = sec.getAttribute('data-lazy-key');
        const conf = LAZY_CONFIG[key];
        if(!conf){ io.unobserve(sec); return; }

        if(sec.dataset.rendered === '1'){
          sec.classList.add('is-in');
          io.unobserve(sec);
          return;
        }

        sec.classList.add('is-in');
        sec.dataset.rendered = '1';
        io.unobserve(sec);

        try{
          const payload = await conf.load();
          if (key === 'testimonials') storedTestimonials = payload.successful_entrepreneurs || payload.items || payload;
          if (key === 'alumni') storedAlumni = payload.alumni_speak || payload;

          setTimeout(() => {
            try{ conf.render(payload); }catch(err){}
          }, 70);
        }catch(err){
          console.warn(err);
          showApiAlert(err);
        }
      });
    }, { threshold: 0.12, rootMargin: '140px 0px' });

    sections.forEach(sec => io.observe(sec));
  }

  async function bootHome(){
    try{
      initRevealObservers();
      await loadImmediateSections();
      initLazySections();

      window.addEventListener('resize', handleResponsiveResize, { passive: true });
    }catch(err){
      console.error('Home boot error:', err);
      showApiAlert(err);
      LOADER.done();
    }
  }

  document.addEventListener('DOMContentLoaded', bootHome);
  </script>

  @stack('scripts')
</body>
</html>
