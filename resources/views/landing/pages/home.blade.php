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
     */
    $homeApis = $homeApis ?? [
      // Above-fold (loads immediately one-by-one)
      'hero'          => url('/api/public/grand-homepage/hero-carousel'),
      'noticeMarquee' => url('/api/public/grand-homepage/notice-marquee'),
      'infoBoxes'     => url('/api/public/grand-homepage/quick-links'),
      'nvaRow'        => url('/api/public/grand-homepage/notice-board'),

      // Lazy (loads on scroll)
      'stats'           => url('/api/public/grand-homepage/stats'),
      'achvRow'          => url('/api/public/grand-homepage/activities'),
      'placementNotices' => url('/api/public/grand-homepage/placement-notices'),

      'testimonials'   => url('/api/public/grand-homepage/successful-entrepreneurs'),
      'alumni'         => url('/api/public/grand-homepage/alumni-speak'),
      'success'        => url('/api/public/grand-homepage/success-stories'),
      'courses'        => url('/api/public/grand-homepage/courses'),
      'recruiters'     => url('/api/public/grand-homepage/recruiters'),
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
    .page-loader{
      position: fixed;
      inset: 0;
      z-index: 99999;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 18px;
      background: rgba(246,247,251,.75);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      transition: opacity .35s ease, visibility .35s ease;
    }
    .page-loader.is-done{ opacity:0; visibility:hidden; pointer-events:none; }
    .loader-card{
      width: min(520px, 92vw);
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(158,54,58,.18);
      border-radius: 20px;
      box-shadow: 0 18px 44px rgba(2,6,23,.16);
      padding: 18px 18px 16px;
      overflow:hidden;
      position: relative;
    }
    .loader-card::before{
      content:"";
      position:absolute; inset:-120px -120px auto auto;
      width: 260px; height: 260px;
      background: radial-gradient(circle at 30% 30%, rgba(201,75,80,.22), rgba(201,75,80,0));
      transform: rotate(10deg);
      pointer-events:none;
    }
    .loader-top{
      display:flex;
      align-items:center;
      gap: 12px;
      position: relative;
    }
    .loader-logo{
      width: 42px; height: 42px;
      border-radius: 14px;
      display:inline-flex; align-items:center; justify-content:center;
      background: linear-gradient(135deg, rgba(158,54,58,.16), rgba(201,75,80,.10));
      border: 1px solid rgba(158,54,58,.18);
      color: var(--brand);
      flex: 0 0 auto;
    }
    .loader-title{
      font-weight: 950;
      margin: 0;
      font-size: 16px;
      color:#0f172a;
      line-height: 1.15;
    }
    .loader-sub{
      margin: 2px 0 0;
      color: var(--muted);
      font-weight: 800;
      font-size: 13px;
    }
    .loader-bar{
      margin-top: 14px;
      height: 10px;
      border-radius: 999px;
      background: rgba(2,6,23,.06);
      overflow:hidden;
      border: 1px solid rgba(2,6,23,.06);
      position: relative;
    }
    .loader-bar > span{
      display:block;
      height:100%;
      width: 10%;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--brand), var(--accent), var(--brand2));
      transition: width .35s ease;
      position: relative;
    }
    .loader-bar > span::after{
      content:"";
      position:absolute; inset:0;
      background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.35), rgba(255,255,255,0));
      transform: translateX(-60%);
      animation: loaderShine 1.1s linear infinite;
      mix-blend-mode: overlay;
    }
    @keyframes loaderShine{
      to{ transform: translateX(160%); }
    }
    .loader-row{
      margin-top: 12px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      position: relative;
    }
    .loader-step{
      font-weight: 900;
      color: #7a2626;
      font-size: 13px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 70%;
    }
    .loader-spinner{
      width: 28px; height: 28px;
      border-radius: 50%;
      border: 3px solid rgba(158,54,58,.22);
      border-top-color: var(--brand);
      animation: spin .75s linear infinite;
      flex: 0 0 auto;
    }
    @keyframes spin{ to{ transform: rotate(360deg); } }

    /* =========================
      Motion / Reveal (loads one-by-one + on scroll)
    ========================= */
    .reveal{
      opacity: 0;
      transform: translateY(22px);
      transition: opacity .7s ease, transform .85s cubic-bezier(.2,.8,.2,1);
      will-change: opacity, transform;
    }
    .reveal.reveal-left{ transform: translateX(-22px); }
    .reveal.reveal-right{ transform: translateX(22px); }
    .reveal.is-in{ opacity: 1; transform: translate3d(0,0,0); }
    @media (prefers-reduced-motion: reduce){
      .reveal, .reveal.reveal-left, .reveal.reveal-right{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      .loader-spinner{ animation: none !important; }
      .loader-bar > span::after{ animation: none !important; }
    }

    /* ===== hero carousel ===== */
    .hero-wrap{ position:relative; overflow:hidden; margin-top: 20px; }
    .hero-card{
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .hero-slide{
      min-height: 500px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .hero-slide::before{
      content:"";
      position:absolute; inset:0;
      background: linear-gradient(90deg, rgba(0,0,0,.65), rgba(0,0,0,.20));
    }
    .hero-inner{
      position:relative;
      padding: 60px 40px;
      max-width: 980px;
      color:#fff;
    }
    .hero-kicker{
      display:inline-flex; gap:10px; align-items:center;
      padding: 8px 16px;
      border-radius: 999px;
      background: rgba(255,255,255,.16);
      border: 1px solid rgba(255,255,255,.25);
      font-weight: 700;
      font-size: 13px;
      letter-spacing:.4px;
      margin-bottom: 20px;
    }
    .hero-title{
      font-weight: 900;
      line-height: 1.1;
      margin: 0 0 16px;
      font-size: clamp(28px, 4vw, 52px);
    }
    .hero-actions{ display:flex; gap:12px; flex-wrap:wrap; margin-top: 20px; }
    .btn-hero{
      background: var(--accent);
      border: 0;
      color:#fff;
      border-radius: 12px;
      padding: 12px 24px;
      font-weight: 800;
      font-size: 15px;
    }
    .btn-hero:hover{ background: var(--brand); color:#fff; }

    /* =========================
      Top strip (NOTICE MARQUEE ONLY)
      - announcements marquee removed
      ✅ clickable items only when URL exists
    ========================= */
    .notice-strip{
      background: linear-gradient(135deg, rgba(158,54,58,.12), rgba(201,75,80,.08));
      border: 1px solid rgba(158,54,58,.18);
      border-radius: 16px;
      padding: 12px 14px;
      margin-top: 18px;
      box-shadow: 0 10px 22px rgba(2,6,23,.06);
      overflow:hidden;
    }
    .notice-strip .strip-ico{
      width: 34px; height: 34px;
      display:inline-flex; align-items:center; justify-content:center;
      border-radius: 999px;
      background: rgba(158,54,58,.12);
      color: var(--brand);
      border: 1px solid rgba(158,54,58,.18);
      flex: 0 0 auto;
    }
    .notice-strip marquee{
      font-weight: 900;
      color: #7a2626;
      font-size: 14.5px;
    }
    .notice-strip marquee .nm-link{
      color: #7a2626;
      text-decoration: none;
      font-weight: 950;
      cursor: pointer;
    }
    .notice-strip marquee .nm-link:hover{
      text-decoration: underline;
      color: var(--brand);
    }
    .notice-strip marquee .nm-text{
      color: #7a2626;
      font-weight: 900;
      cursor: default;
    }
    .notice-strip marquee .nm-sep{
      opacity: .75;
      padding: 0 10px;
      user-select:none;
    }

    /* ===== three info boxes ===== */
    .info-boxes{ margin-top: 26px; }
    .info-box{
      background: var(--brand);
      color: #fff;
      border-radius: 16px;
      padding: 24px;
      height: 100%;
      box-shadow: var(--shadow);
      position:relative;
      overflow:hidden;
    }
    .info-box::after{
      content:"";
      position:absolute; inset:-40px -40px auto auto;
      width: 160px; height: 160px;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), rgba(255,255,255,0));
      transform: rotate(18deg);
      pointer-events:none;
    }
    .info-box h5{
      font-weight: 900;
      margin-bottom: 12px;
      font-size: 18px;
      display:flex; align-items:center; gap:10px;
    }
    .info-box ul{
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .info-box ul li{
      padding: 8px 0;
      border-bottom: 1px dashed rgba(255,255,255,.3);
      font-size: 14px;
      display:flex;
      align-items:flex-start;
      gap:10px;
    }
    .info-box ul li:last-child{ border-bottom: 0; }
    .info-box i{ margin-top: 2px; opacity: .92; }
    .info-box a{ color:#fff; text-decoration:none; font-weight:800; }
    .info-box a:hover{ text-decoration:underline; }

    /* =========================
      Notice + Center Iframe + Announcements
      - Placement box REMOVED here (because you have two)
    ========================= */
    .nva-card{
      background: var(--brand);
      border-radius: 18px;
      box-shadow: var(--shadow);
      padding: 14px;
      height: 100%;
      border: 1px solid rgba(255,255,255,.10);
      overflow:hidden;
    }
    .nva-head{
      display:flex; align-items:center; justify-content:center;
      gap: 10px;
      color:#fff;
      font-weight: 950;
      letter-spacing:.3px;
      padding: 6px 8px 12px;
      font-size: 20px;
      user-select:none;
    }
    .nva-head i{
      opacity:.95;
      filter: drop-shadow(0 6px 10px rgba(0,0,0,.12));
    }
    .nva-body{
      background: #fff;
      border-radius: 14px;
      border: 1px solid rgba(17,17,17,.06);
      padding: 12px;
      color: var(--ink);
    }
    .nva-list{
      list-style:none;
      padding: 0;
      margin: 0;
      max-height: 260px;
      overflow:auto;
    }
    .nva-list::-webkit-scrollbar{ width: 6px; }
    .nva-list::-webkit-scrollbar-thumb{ background: rgba(17,17,17,.18); border-radius: 999px; }
    .nva-list li{
      display:flex;
      align-items:flex-start;
      gap: 10px;
      padding: 9px 6px;
      border-bottom: 1px dashed rgba(2,6,23,.12);
      font-weight: 700;
      color: #0f172a;
    }
    .nva-list li:last-child{ border-bottom:0; }
    .nva-list li i{ margin-top: 3px; color: rgba(15,23,42,.55); }
    .nva-list a{
      color: #0f172a;
      text-decoration:none;
      font-weight: 800;
      line-height: 1.25;
    }
    .nva-list a:hover{ color: var(--brand); text-decoration: underline; }

    /* center iframe card */
    .center-video-card{
      background: var(--surface);
      border-radius: 18px;
      border: 1px solid var(--line);
      box-shadow: var(--shadow);
      padding: 14px;
      height: 100%;
      overflow:hidden;
    }
    .center-video-title{
      font-weight: 950;
      color: #0f172a;
      margin: 2px 0 12px;
      text-align:center;
      font-size: 22px;
    }
    .video-embed{
      position: relative;
      width: 100%;
      padding-bottom: 56.25%;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 24px rgba(2,6,23,.12);
      background: #111;
    }
    .video-embed iframe{
      position:absolute; inset:0;
      width:100%; height:100%;
      border:0;
    }
    .cta-section{
      margin-top: 12px;
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content:center;
    }
    .cta-btn{
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: #f59e0b;
      color: #fff;
      border: 0;
      border-radius: 999px;
      padding: 12px 18px;
      font-weight: 950;
      font-size: 15px;
      box-shadow: 0 6px 14px rgba(245,158,11,.28);
      transition: transform .15s ease, filter .15s ease, background .15s ease;
      text-decoration:none;
      min-width: 200px;
    }
    .cta-btn:hover{ background:#d97706; transform: translateY(-1px); color:#fff; }
    .cta-btn.btn-secondary{ background:#991b1b; box-shadow: 0 6px 14px rgba(153,27,27,.22); }
    .cta-btn.btn-secondary:hover{ background:#7f1d1d; color:#fff; }

    /* ===== stats counter ===== */
    .stats-section{
      margin-top: 40px;
      background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(201,75,80,.04));
      border-radius: var(--r-xl);
      padding: 50px 30px;
      border: 1px solid rgba(158,54,58,.12);
      position:relative;
      overflow:hidden;
    }
    .stats-section.has-bg{
      background-size: cover;
      background-position: center;
    }
    .stats-section .stats-head{ text-align:center; margin-bottom: 26px; }
    .stats-section .stats-head h2{
      margin:0;
      font-weight: 950;
      color: var(--brand);
      font-size: clamp(22px, 3vw, 34px);
    }
    .stat-item{ text-align: center; }
    .stat-num{
      font-size: clamp(40px, 5vw, 64px);
      font-weight: 950;
      color: var(--brand);
      line-height: 1;
      margin-bottom: 8px;
    }
    .stat-label{ font-size: 16px; color: var(--muted); font-weight: 800; }
    .stat-icon{
      display:inline-flex;
      width: 42px; height: 42px;
      align-items:center; justify-content:center;
      border-radius: 999px;
      background: rgba(158,54,58,.10);
      color: var(--brand);
      margin-bottom: 10px;
      border: 1px solid rgba(158,54,58,.18);
    }

    /* ===== testimonials ===== */
    .testimonial-section{
      margin-top: 50px;
      background: var(--surface);
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      padding: 40px;
      box-shadow: var(--shadow);
    }
    .testimonial-section h2{
      text-align: center;
      font-weight: 950;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .testimonial-card{
      background: linear-gradient(135deg, rgba(158,54,58,.06), rgba(201,75,80,.03));
      border-radius: 16px;
      padding: 30px;
      height: 100%;
      border: 1px solid var(--line);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .testimonial-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 14px 26px rgba(2,6,23,.10);
    }
    .testimonial-avatar{
      width: 80px; height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--brand);
      margin-bottom: 16px;
      background: #fff;
    }
    /* ✅ ensure it never looks like "code"; render rich text cleanly */
    .testimonial-text{
      font-style: italic;
      color: var(--ink);
      margin-bottom: 16px;
      line-height: 1.6;
      font-family: inherit;
      background: transparent;
      padding: 0;
      border-radius: 0;
      white-space: normal;
      word-break: break-word;
    }
    .testimonial-text p{ margin: 0 0 10px; }
    .testimonial-text p:last-child{ margin-bottom: 0; }
    .testimonial-text ul, .testimonial-text ol{ margin: 8px 0 0 18px; }
    .testimonial-name{ font-weight: 950; color: var(--brand); margin-bottom: 4px; }
    .testimonial-role{ font-size: 13px; color: var(--muted); font-weight: 800; }

    /* ===== alumni videos ===== */
    .alumni-section{
      margin-top: 40px;
      background: var(--surface);
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      padding: 40px;
      box-shadow: var(--shadow);
    }
    .alumni-section h2{
      text-align: center;
      font-weight: 950;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .alumni-video-card{
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 22px rgba(2,6,23,.10);
      height: 100%;
      background:#111;
    }
    .alumni-video-card iframe{
      width: 100%;
      height: 240px;
      display:block;
      border:0;
    }

    /* ===== success stories ===== */
    .success-section{
      margin-top: 40px;
      background: #f9fafb;
      border-radius: var(--r-xl);
      padding: 40px;
      border: 1px solid rgba(17,17,17,.06);
    }
    .success-section h2{
      text-align: center;
      font-weight: 950;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .success-card{
      background: var(--surface);
      border-radius: 16px;
      padding: 20px;
      height: 100%;
      border: 1px solid var(--line);
      box-shadow: 0 10px 22px rgba(2,6,23,.08);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .success-card:hover{ transform: translateY(-3px); box-shadow: 0 16px 28px rgba(2,6,23,.10); }
    .success-img{
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 16px;
      background:#eee;
    }
    .success-desc{ font-size: 14px; color: var(--muted); margin-bottom: 12px; line-height: 1.5; }
    .success-name{ font-weight: 950; color: var(--brand); font-size: 16px; margin-bottom: 4px; }
    .success-role{ font-size: 13px; color: var(--muted); font-weight: 800; }

    /* ===== courses section ===== */
    .courses-section{
      margin-top: 50px;
      background: var(--surface);
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      padding: 40px;
      box-shadow: var(--shadow);
    }
    .courses-section h2{
      text-align: center;
      font-weight: 950;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .course-card{
      background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(201,75,80,.04));
      border-radius: 16px;
      padding: 24px;
      height: 100%;
      border: 1px solid var(--line);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .course-card:hover{
      transform: translateY(-3px);
      box-shadow: 0 16px 30px rgba(2,6,23,.12);
    }
    .course-img{
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 16px;
      background:#eee;
    }
    .course-title{
      font-weight: 950;
      color: var(--brand);
      font-size: 20px;
      margin-bottom: 10px;
    }
    .course-desc{ font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 14px; }
    .course-links{ display:flex; gap: 8px; flex-wrap: wrap; }
    .course-link{
      font-size: 12px;
      padding: 6px 12px;
      background: rgba(158,54,58,.15);
      color: var(--brand);
      border-radius: 999px;
      text-decoration: none;
      font-weight: 900;
      transition: background .15s ease, color .15s ease, transform .15s ease;
    }
    .course-link:hover{ background: var(--brand); color: #fff; transform: translateY(-1px); }

    /* ===== recruiters ===== */
    .recruiters-section{
      margin-top: 50px;
      background: var(--surface);
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      padding: 40px;
      box-shadow: var(--shadow);
    }
    .recruiters-section h2{
      text-align: center;
      font-weight: 950;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .recruiter-grid{
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 16px;
      margin-top: 24px;
    }
    .recruiter-logo{
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 14px;
      display:flex;
      align-items:center;
      justify-content:center;
      height: 84px;
      transition: transform .16s ease, box-shadow .16s ease;
      overflow:hidden;
      text-decoration:none;
    }
    .recruiter-logo:hover{
      box-shadow: 0 14px 26px rgba(2,6,23,.10);
      transform: translateY(-2px);
    }
    .recruiter-logo img{
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      display:block;
    }

    .muted-note{
      color: var(--muted);
      font-weight: 800;
      text-align:center;
      margin: 0;
      padding: 10px 0 0;
    }

    /* small alert (for API error only) */
    .home-alert{
      margin-top: 18px;
      border-radius: 14px;
      border: 1px solid rgba(245,158,11,.35);
      background: linear-gradient(135deg, rgba(254,243,199,.85), rgba(254,215,170,.65));
      padding: 14px 16px;
      color: #92400e;
      font-weight: 900;
      display:none;
    }
    .home-alert code{
      font-weight: 950;
      color:#7c2d12;
      background: rgba(255,255,255,.55);
      padding: 2px 6px;
      border-radius: 8px;
    }
    .home-alert pre{
      margin: 8px 0 0;
      white-space: pre-wrap;
      background: rgba(255,255,255,.55);
      padding: 10px 12px;
      border-radius: 12px;
      font-size: 12.5px;
      line-height: 1.4;
      color: #7c2d12;
    }

    @media (max-width: 768px){
      .hero-inner{ padding: 40px 24px; }
      .info-boxes{ margin-top: 18px; }
      .stat-num{ font-size: 36px; }
      .testimonial-section, .alumni-section, .courses-section, .recruiters-section{ padding: 26px; }
      .success-section{ padding: 26px; }
      .center-video-title{ font-size: 18px; }
      .cta-btn{ min-width: 160px; font-size: 14px; padding: 11px 14px; }
      .nva-list{ max-height: 220px; }
      .loader-card{ padding: 16px; border-radius: 18px; }
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

  {{-- Main Header --}}
  @include('landing.components.header')

  {{-- Header Menu --}}
  @include('landing.components.headerMenu')

  <main class="pb-5">
    <div class="container">

      <div class="home-alert" id="homeApiAlert">
        Home API error. Please verify section endpoints in <code>$homeApis</code>.
      </div>

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

      {{-- ================= TOP NOTICE MARQUEE (NOTICE ONLY) ================= --}}
      <section class="notice-strip reveal is-in" data-anim="up">
        <div class="d-flex align-items-center gap-3">
          <div class="strip-ico"><i class="fa-solid fa-bullhorn"></i></div>
          <div class="flex-grow-1" id="noticeMarquee">
            <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
              Loading notices…
            </marquee>
          </div>
        </div>
      </section>

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
        <div class="row g-4" id="successStoriesContainer">
          <div class="col-lg-3 col-md-6">
            <div class="success-card">
              <img id="successFallbackImage" alt="Success" class="success-img">
              <p class="success-desc">Loading…</p>
              <div class="success-name">—</div>
              <div class="success-role">—</div>
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

      {{-- ================= TOP RECRUITERS (LAZY) ================= --}}
      <section class="recruiters-section reveal" data-lazy-key="recruiters">
        <h2>Top Recruiters</h2>
        <div class="recruiter-grid" id="recruitersContainer"></div>
        <p class="muted-note" id="recruitersNote"></p>
      </section>

    </div>
  </main>

  {{-- Footer --}}
@include('landing.components.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  /**
   * ✅ FIXED: Your APIs were mismatched earlier.
   * This file now calls ONLY the routes you listed:
   * - /notice-marquee, /hero-carousel, /quick-links, /notice-board
   * - /activities, /placement-notices, /stats, /courses, /recruiters
   * - /successful-entrepreneurs, /alumni-speak, /success-stories
   *
   * ✅ PERFORMANCE: below-fold loads only on scroll (IntersectionObserver)
   * ✅ UX: added page-loader + richer animations
   */

  const HOME_APIS = @json($homeApis);

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
    }
  };

  // safety: never block forever
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

  // set initial fallback imgs (no 404)
  document.getElementById('testimonialFallbackAvatar')?.setAttribute('src', PLACEHOLDERS.avatar);
  document.getElementById('successFallbackImage')?.setAttribute('src', PLACEHOLDERS.image);
  document.getElementById('courseFallbackImage')?.setAttribute('src', PLACEHOLDERS.image);

  /* Any img that fails later -> fallback */
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

  /**
   * URL normalize:
   * - keeps external URLs
   * - ensures leading /
   * - converts placement_notices to placement-notices (required)
   */
  function safeHref(u){
    const s0 = String(u ?? '').trim();
    if(!s0) return '#';
    if(/^https?:\/\//i.test(s0)) return s0;

    let s = s0.startsWith('/') ? s0 : ('/' + s0);
    s = s.replace(/\/placement_notices(?=\/|$)/gi, '/placement-notices');
    return s;
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

      // ✅ allow safe formatting and paragraphs/lists for testimonials
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
    // handles both "<p>..</p>" and "&lt;p&gt;..&lt;/p&gt;"
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

  /* =========================
    Reveal animation on view
    NOTE: skip [data-lazy-key] here (lazy loader controls those)
  ========================= */
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

  /* =========================
    Counter Animation
  ========================= */
  function animateCounters(){
    const els = document.querySelectorAll('.stat-num[data-count]');
    els.forEach(el => {
      const target = parseInt(String(el.getAttribute('data-count') || '0').replace(/[, ]/g,''), 10) || 0;
      const duration = 1200;
      const start = performance.now();

      function tick(t){
        const p = Math.min(1, (t - start) / duration);
        const val = Math.floor(target * p);
        el.textContent = val.toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
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

  /* =========================
    Fetch (per-section) + cache
  ========================= */
  async function fetchJson(url){
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if(!res.ok) throw new Error(`HTTP ${res.status} @ ${url}`);
    return await res.json();
  }
  function unwrap(json){
    // supports {success:true, data:{...}} OR {success:true, key:...} OR { ... }
    if(json && isObj(json.data)) return json.data;
    return json;
  }

  const SECTION_CACHE = new Map(); // key -> Promise(payload)
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

  /* =========================
    Renderers
  ========================= */
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
      return; // keep fallback slide
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

    try{
      const existing = bootstrap.Carousel.getInstance(heroRoot);
      if(existing) existing.dispose();
      new bootstrap.Carousel(heroRoot, {
        interval: autoplay ? interval : false,
        pause: (Number(settings.pause_on_hover ?? 1) === 1) ? 'hover' : false,
        wrap: (Number(settings.loop ?? 1) === 1),
        ride: autoplay ? 'carousel' : false
      });
    }catch(e){}
  }

  /* one-by-one list render (nice feel) */
  function setList(listId, items, iconClass, emptyText, opts = {}){
    const el = document.getElementById(listId);
    if(!el) return;

    const arr = Array.isArray(items) ? items : [];
    const max = Number(opts.max ?? 7);

    if(!arr.length){
      el.innerHTML = `<li><i class="${esc(iconClass)}"></i> <span>${esc(emptyText || 'No items available')}</span></li>`;
      return;
    }

    const slice = arr.slice(0, max).map(it => {
      const title = it.title ?? it.text ?? it.name ?? '-';

      let url = it.url ?? it.href ?? it.link ?? '';
      // Optional builder for cases where API doesn't return url
      if(!String(url || '').trim() && typeof opts.buildUrl === 'function'){
        try{ url = opts.buildUrl(it) || ''; }catch(e){ url = ''; }
      }

      const hasLink = String(url || '').trim().length > 0;
      const href = hasLink ? safeHref(url) : '';
      return { title, hasLink, href };
    });

    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
    if(reduce || opts.stagger === false){
      el.innerHTML = slice.map(x => `
        <li>
          <i class="${esc(iconClass)}"></i>
          ${x.hasLink ? `<a href="${esc(x.href)}">${esc(x.title)}</a>` : `<span>${esc(x.title)}</span>`}
        </li>
      `).join('');
      return;
    }

    el.innerHTML = '';
    slice.forEach((x, i) => {
      setTimeout(() => {
        const li = document.createElement('li');
        li.innerHTML = `
          <i class="${esc(iconClass)}"></i>
          ${x.hasLink ? `<a href="${esc(x.href)}">${esc(x.title)}</a>` : `<span>${esc(x.title)}</span>`}
        `;
        el.appendChild(li);
      }, i * 45);
    });
  }

  /* =========================
    ✅ Notice marquee: clickable only if URL exists
  ========================= */
  function renderNoticeMarquee(noticeMarquee){
    const host = document.getElementById('noticeMarquee');
    if(!host) return;

    const items = Array.isArray(noticeMarquee?.items) ? noticeMarquee.items : [];

    const nodes = items.map(it => {
      let text = '';
      let url  = '';

      if(typeof it === 'string'){
        text = it.trim();
      }else if(isObj(it)){
        text = String(it.text ?? it.label ?? it.title ?? '').trim();
        url  = String(it.url ?? it.href ?? it.link ?? it.route ?? '').trim();
      }

      if(!text) return null;

      if(url){
        const href = safeHref(url);
        return `<a class="nm-link" href="${esc(href)}">${esc(text)}</a>`;
      }
      return `<span class="nm-text">${esc(text)}</span>`;
    }).filter(Boolean);

    if(!nodes.length){
      host.innerHTML = `
        <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
          <span class="nm-text">Welcome.</span>
        </marquee>
      `;
      return;
    }

    host.innerHTML = `
      <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
        ${nodes.join('<span class="nm-sep">•</span>')}
      </marquee>
    `;
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

    const items = Array.isArray(stats?.stats_items_json) ? stats.stats_items_json : [];
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

    const cols = (n) => {
      if(n <= 4) return 'col-lg-3 col-6';
      if(n <= 6) return 'col-lg-2 col-md-4 col-6';
      return 'col-lg-2 col-md-3 col-6';
    };

    rowEl.innerHTML = items
      .slice()
      .sort((a,b)=>(Number(a.sort_order||0)-Number(b.sort_order||0)))
      .map((it) => {
        const label = it.label || it.key || '—';
        const value = String(it.value ?? '0').replace(/[^\d]/g,'') || '0';
        const icon  = it.icon_class ? String(it.icon_class) : 'fa-solid fa-chart-column';

        return `
          <div class="${cols(items.length)}">
            <div class="stat-item">
              <div class="stat-icon"><i class="${esc(icon)}"></i></div>
              <div class="stat-num" data-count="${esc(value)}">0</div>
              <div class="stat-label">${esc(label)}</div>
            </div>
          </div>
        `;
      }).join('');

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

    container.innerHTML = items.slice(0, 6).map(item => {
      const avatar = item.avatar || item.photo_url || item.image_url || PLACEHOLDERS.avatar;

      // ✅ Fix: show text normally (no “<p>..</p>” appearing as code)
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
    }).join('');

    container.querySelectorAll('img.testimonial-avatar').forEach(img => attachImgFallback(img, 'avatar'));
  }

  function renderAlumniSpeak(alumni){
    const titleEl = document.getElementById('alumniSpeakTitle');
    const container = document.getElementById('alumniVideoContainer');
    if(!container) return;

    if(titleEl) titleEl.textContent = alumni?.title ? String(alumni.title) : 'Alumni Speak';

    const vids = Array.isArray(alumni?.iframe_urls_json) ? alumni.iframe_urls_json : [];
    if(!vids.length){
      container.innerHTML = `<div class="col-12"><p class="muted-note">No alumni videos available.</p></div>`;
      return;
    }

    container.innerHTML = vids
      .slice()
      .sort((a,b)=>(Number(a.sort_order||0)-Number(b.sort_order||0)))
      .slice(0, 6)
      .map(v => {
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
  }

  function renderSuccessStories(arr){
    const container = document.getElementById('successStoriesContainer');
    if(!container) return;

    const items = Array.isArray(arr) ? arr : [];
    if(!items.length){
      container.innerHTML = `<div class="col-12"><p class="muted-note">No success stories available.</p></div>`;
      return;
    }

    container.innerHTML = items.slice(0, 8).map(story => {
      const img  = story.image_url || story.image || story.photo_url || PLACEHOLDERS.image;
      const desc = story.description || story.text || '';
      const name = story.name || story.title || '—';
      const role = story.role || story.year || story.subtitle || '';

      return `
        <div class="col-lg-3 col-md-6">
          <div class="success-card">
            <img src="${esc(img)}" loading="lazy" alt="${esc(name)}" class="success-img">
            <p class="success-desc">${esc(desc || '—')}</p>
            <div class="success-name">${esc(name)}</div>
            <div class="success-role">${esc(role || '—')}</div>
          </div>
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

  function renderRecruiters(arr){
    const container = document.getElementById('recruitersContainer');
    const note = document.getElementById('recruitersNote');
    if(!container) return;

    const items = Array.isArray(arr) ? arr : [];
    if(!items.length){
      container.innerHTML = '';
      if(note) note.textContent = 'No recruiters available.';
      return;
    }

    const sorted = items.slice().sort((a,b)=>{
      const fa = Number(a.is_featured_home||0), fb = Number(b.is_featured_home||0);
      if(fa !== fb) return fb-fa;
      return Number(a.sort_order||0) - Number(b.sort_order||0);
    });

    const shown = sorted.slice(0, 18);
    container.innerHTML = shown.map(r => {
      const logo  = r.logo_url || r.logo || PLACEHOLDERS.image;
      const title = r.title || r.name || 'Recruiter';
      const href  = r.url ? safeHref(r.url) : '#';

      return `
        <a class="recruiter-logo" href="${esc(href)}" ${href !== '#' ? 'target="_blank" rel="noopener"' : ''} title="${esc(title)}">
          <img src="${esc(logo)}" loading="lazy" alt="${esc(title)}">
        </a>
      `;
    }).join('');

    container.querySelectorAll('.recruiter-logo img').forEach(img => attachImgFallback(img, 'image'));

    if(note){
      note.textContent = (items.length > shown.length)
        ? `Showing ${shown.length} of ${items.length} recruiters.`
        : '';
    }
  }

  /* =========================
    Error alert (show first failing API)
  ========================= */
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

  /* =========================
    Above-fold: load one-by-one (ONLY THESE APIs)
  ========================= */
  async function loadImmediateSections(){
    // A) HERO ( /hero-carousel )
    LOADER.set(18, 'Loading hero carousel…');
    try{
      const p = await loadSection('hero');
      const hero = p.hero_carousel || p.hero || p;
      renderHero(hero);
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    // B) NOTICE MARQUEE ( /notice-marquee )
    LOADER.set(36, 'Loading notice marquee…');
    try{
      const p = await loadSection('noticeMarquee');
      const nm = p.notice_marquee || p;
      renderNoticeMarquee(nm);
    }catch(e){
      console.warn(e);
      showApiAlert(e);
      renderNoticeMarquee({ items: ['Welcome.'] });
    }

    // C) THREE INFO BOXES ( /quick-links )
    LOADER.set(56, 'Loading quick links…');
    try{
      const p = await loadSection('infoBoxes');
      setList('careerList',      p.career_notices, 'fa-solid fa-chevron-right', 'No career notices.', { stagger:true });
      setList('whyMsitList',     p.why_us,         'fa-solid fa-check',         'No highlights.',     { stagger:true });
      setList('scholarshipList', p.scholarships,   'fa-solid fa-gift',          'No scholarships.',   { stagger:true });
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    // D) NOTICE + CENTER IFRAME + ANNOUNCEMENTS ( /notice-board )
    LOADER.set(78, 'Loading notice board…');
    try{
      const p = await loadSection('nvaRow');
      renderCenterIframe(p.center_iframe || p.centerIframe || p.center || null);
      setList('noticeList',       p.notices,       'fa-solid fa-caret-right', 'No notices.',       { max: 12, stagger:true });
      setList('announcementList', p.announcements, 'fa-solid fa-caret-right', 'No announcements.', { max: 12, stagger:true });
    }catch(e){
      console.warn(e);
      showApiAlert(e);
    }

    LOADER.set(100, 'Almost done…');
    setTimeout(() => LOADER.done(), 250);
  }

  /* =========================
    Lazy Loading on Scroll
  ========================= */
  const LAZY_CONFIG = {
    stats: {
      load: () => loadSection('stats'),
      render: (payload) => renderStats(payload.stats || payload)
    },
    achvRow: {
      // ✅ activities loads achievements + student_activities
      // ✅ placement notices comes from a separate API (still loaded only when this row is visible)
      load: () => loadSection('achvRow'),
      render: (payload) => {
        setList('achievementList', payload.achievements,       'fa-solid fa-medal',    'No achievements.', { stagger:true, max: 8 });
        setList('activityList',    payload.student_activities, 'fa-solid fa-calendar', 'No activities.',   { stagger:true, max: 8 });

        loadSection('placementNotices')
          .then(p2 => {
            const data = p2.placement_notices || p2.items || p2;
            setList('placementList2', data, 'fa-solid fa-building', 'No placements.', {
              stagger:true, max: 8,
              // if API doesn't provide url, try to build:
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
    },
    recruiters: {
      load: () => loadSection('recruiters'),
      render: (payload) => renderRecruiters(payload.recruiters || payload.items || payload)
    }
  };

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

        // reveal animation while loading
        sec.classList.add('is-in');
        sec.dataset.rendered = '1';
        io.unobserve(sec);

        try{
          const payload = await conf.load();
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

  /* =========================
    Boot
  ========================= */
  async function bootHome(){
    try{
      initRevealObservers();
      await loadImmediateSections();
      initLazySections();
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
