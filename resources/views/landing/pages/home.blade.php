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
     * IMPORTANT:
     * Set this to the REAL endpoint that returns the JSON you pasted.
     * Example if your route is: Route::get('/api/home/current', ...) then use url('/api/home/current')
     */
    $homeApiUrl = $homeApiUrl ?? url('/api/public/grand-homepage');
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

    /* ===== scrolling announcement ===== */
    .announcement-strip{
      background: linear-gradient(135deg, #fef3c7, #fed7aa);
      padding: 14px 0;
      border-bottom: 2px solid #f59e0b;
      margin-top: 20px;
    }
    .announcement-strip marquee{
      font-weight: 700;
      color: #92400e;
      font-size: 15px;
    }

    /* ===== three info boxes ===== */
    .info-boxes{ margin-top: 30px; }
    .info-box{
      background: var(--brand);
      color: #fff;
      border-radius: 16px;
      padding: 24px;
      height: 100%;
      box-shadow: var(--shadow);
    }
    .info-box h5{
      font-weight: 900;
      margin-bottom: 12px;
      font-size: 18px;
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
    .info-box a{ color:#fff; text-decoration:none; font-weight:700; }
    .info-box a:hover{ text-decoration:underline; }

    /* ===== video section ===== */
    .video-section{
      margin-top: 40px;
      background: var(--surface);
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      padding: 40px;
      box-shadow: var(--shadow);
    }
    .video-section h2{
      text-align: center;
      font-weight: 900;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .video-embed{
      position: relative;
      width: 100%;
      padding-bottom: 56.25%;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
      background: #111;
    }
    .video-embed iframe{
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
    }

    /* ===== cta buttons ===== */
    .cta-section{
      margin-top: 30px;
      text-align: center;
    }
    .cta-btn{
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #f59e0b;
      color: #fff;
      border: 0;
      border-radius: 999px;
      padding: 14px 32px;
      font-weight: 900;
      font-size: 16px;
      margin: 0 8px 12px;
      box-shadow: 0 4px 12px rgba(245,158,11,.3);
      transition: all .2s;
      text-decoration:none;
    }
    .cta-btn:hover{
      background: #d97706;
      transform: translateY(-2px);
      color: #fff;
    }
    .cta-btn.btn-secondary{
      background: #991b1b;
      box-shadow: 0 4px 12px rgba(153,27,27,.25);
    }
    .cta-btn.btn-secondary:hover{
      background: #7f1d1d;
      color:#fff;
    }

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
    .stats-section .stats-head{
      text-align:center;
      margin-bottom: 26px;
    }
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
    .stat-label{
      font-size: 16px;
      color: var(--muted);
      font-weight: 800;
    }
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
      font-weight: 900;
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
    }
    .testimonial-avatar{
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--brand);
      margin-bottom: 16px;
      background: #fff;
    }
    .testimonial-text{
      font-style: italic;
      color: var(--ink);
      margin-bottom: 16px;
      line-height: 1.6;
    }
    .testimonial-name{
      font-weight: 900;
      color: var(--brand);
      margin-bottom: 4px;
    }
    .testimonial-role{
      font-size: 13px;
      color: var(--muted);
      font-weight: 700;
    }

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
      font-weight: 900;
      color: var(--brand);
      margin-bottom: 30px;
      font-size: clamp(22px, 3vw, 36px);
    }
    .alumni-video-card{
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0,0,0,.1);
      height: 100%;
      background:#111;
    }
    .alumni-video-card iframe{
      width: 100%;
      height: 240px;
      display:block;
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
      font-weight: 900;
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
      box-shadow: 0 6px 18px rgba(0,0,0,.08);
      transition: transform .2s;
    }
    .success-card:hover{ transform: translateY(-4px); }
    .success-img{
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 16px;
      background:#eee;
    }
    .success-desc{
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 12px;
      line-height: 1.5;
    }
    .success-name{
      font-weight: 900;
      color: var(--brand);
      font-size: 16px;
      margin-bottom: 4px;
    }
    .success-role{
      font-size: 13px;
      color: var(--muted);
      font-weight: 700;
    }

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
      font-weight: 900;
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
      transition: all .2s;
    }
    .course-card:hover{
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(0,0,0,.12);
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
      font-weight: 900;
      color: var(--brand);
      font-size: 20px;
      margin-bottom: 12px;
    }
    .course-desc{
      font-size: 14px;
      color: var(--muted);
      line-height: 1.6;
      margin-bottom: 16px;
    }
    .course-links{
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .course-link{
      font-size: 12px;
      padding: 6px 12px;
      background: rgba(158,54,58,.15);
      color: var(--brand);
      border-radius: 6px;
      text-decoration: none;
      font-weight: 800;
    }
    .course-link:hover{
      background: var(--brand);
      color: #fff;
    }

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
      font-weight: 900;
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
      border-radius: 12px;
      padding: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 80px;
      transition: all .2s;
      position:relative;
      overflow:hidden;
      text-decoration:none;
    }
    .recruiter-logo:hover{
      box-shadow: 0 8px 20px rgba(0,0,0,.1);
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
      font-weight: 700;
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
      font-weight: 800;
      display:none;
    }
    .home-alert code{
      font-weight: 900;
      color:#7c2d12;
      background: rgba(255,255,255,.55);
      padding: 2px 6px;
      border-radius: 8px;
    }

    @media (max-width: 768px){
      .hero-inner{ padding: 40px 24px; }
      .info-boxes{ margin-top: 20px; }
      .stat-num{ font-size: 36px; }
      .video-section, .testimonial-section, .alumni-section, .courses-section, .recruiters-section{ padding: 26px; }
    }
  </style>
</head>

<body>
{{-- Main Header --}}
@include('landing.components.header')

{{-- Header Menu --}}
@include('landing.components.headerMenu')

<main class="pb-5">
  <div class="container">

    <div class="home-alert" id="homeApiAlert">
      Home API not found. Update <code>$homeApiUrl</code> in this view (or pass it from controller).
    </div>

    {{-- ================= HERO CAROUSEL ================= --}}
    <section class="hero-wrap">
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

    {{-- ================= ANNOUNCEMENT STRIP ================= --}}
    <section class="announcement-strip">
      <div id="announcementMarquee">
        <marquee behavior="scroll" direction="left" scrollamount="6">
          Loading announcements…
        </marquee>
      </div>
    </section>

    {{-- ================= THREE INFO BOXES ================= --}}
    <section class="info-boxes">
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

    {{-- ================= MAIN VIDEO (CENTER IFRAME) ================= --}}
    <section class="video-section">
      <h2 id="centerIframeTitle">Loading…</h2>
      <div class="video-embed" id="mainVideoContainer">
        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>
      </div>
      <div class="cta-section" id="centerIframeButtons">
        <a href="#" class="cta-btn"><i class="fa-solid fa-link"></i> Loading…</a>
      </div>
    </section>

    {{-- ================= NOTICE, ANNOUNCEMENTS, PLACEMENT ================= --}}
    <section class="info-boxes">
      <div class="row g-3">
        <div class="col-lg-4">
          <div class="info-box">
            <h5><i class="fa-solid fa-bullhorn"></i> Notice</h5>
            <ul id="noticeList">
              <li><i class="fa-solid fa-file"></i> <span>Loading…</span></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="info-box">
            <h5><i class="fa-solid fa-megaphone"></i> Announcements</h5>
            <ul id="announcementList">
              <li><i class="fa-solid fa-bell"></i> <span>Loading…</span></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="info-box">
            <h5><i class="fa-solid fa-briefcase"></i> Placement Notice</h5>
            <ul id="placementList">
              <li><i class="fa-solid fa-building"></i> <span>Loading…</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    {{-- ================= STATISTICS ================= --}}
    <section class="stats-section" id="statsSection">
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

    {{-- ================= ACHIEVEMENTS, STUDENTS ACTIVITY, PLACEMENT ================= --}}
    <section class="info-boxes">
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

    {{-- ================= TESTIMONIALS ================= --}}
    <section class="testimonial-section">
      <h2>Successful Entrepreneurs</h2>
      <div class="row g-4" id="testimonialContainer">
        <div class="col-lg-6">
          <div class="testimonial-card">
            <img id="testimonialFallbackAvatar" alt="Alumni" class="testimonial-avatar">
            <p class="testimonial-text">Loading…</p>
            <div class="testimonial-name">—</div>
            <div class="testimonial-role">—</div>
          </div>
        </div>
      </div>
    </section>

    {{-- ================= ALUMNI SPEAK ================= --}}
    <section class="alumni-section">
      <h2 id="alumniSpeakTitle">Alumni Speak</h2>
      <div class="row g-4" id="alumniVideoContainer">
        <div class="col-lg-4 col-md-6">
          <div class="alumni-video-card">
            <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </section>

    {{-- ================= SUCCESS STORIES ================= --}}
    <section class="success-section">
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

    {{-- ================= COURSES OFFERED ================= --}}
    <section class="courses-section">
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

    {{-- ================= TOP RECRUITERS ================= --}}
    <section class="recruiters-section">
      <h2>Top Recruiters</h2>
      <div class="recruiter-grid" id="recruitersContainer"></div>
      <p class="muted-note" id="recruitersNote"></p>
    </section>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * FIXES INCLUDED:
 * 1) Removed references to missing local placeholder images (no more avatar-placeholder.jpg / hero-1.jpg / etc 404).
 * 2) Added safe SVG data-uri placeholders + auto onerror fallback for any broken image URLs.
 * 3) ONLY ONE API request now (so you won't see 5-6 404s). If your endpoint differs, update $homeApiUrl.
 */

const HOME_API_URL = @json($homeApiUrl);

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
function safeHref(u){
  const s = String(u ?? '').trim();
  if(!s) return '#';
  if(/^https?:\/\//i.test(s)) return s;
  if(s.startsWith('/')) return s;
  return '/' + s;
}
function safeInlineHtml(html){
  const input = String(html ?? '');
  if(!input) return '';
  try{
    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div>${input}</div>`, 'text/html');
    const root = doc.body.firstElementChild;

    const ALLOW = new Set(['B','I','U','STRONG','EM','BR','SPAN']);
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
  Fetch (single URL -> no multiple 404 spam)
========================= */
async function fetchHomeJson(url){
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok) throw new Error(`HTTP ${res.status} @ ${url}`);
  return await res.json();
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
    if(url){
      el.style.backgroundImage = `url('${url}')`;
    }
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
    // keep fallback slide
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

    // if no image urls, fallback to gradient (no 404)
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

function setList(listId, items, iconClass, emptyText){
  const el = document.getElementById(listId);
  if(!el) return;

  const arr = Array.isArray(items) ? items : [];
  if(!arr.length){
    el.innerHTML = `<li><i class="${esc(iconClass)}"></i> <span>${esc(emptyText || 'No items available')}</span></li>`;
    return;
  }

  const max = 7;
  el.innerHTML = arr.slice(0, max).map(it => {
    const title = it.title ?? it.text ?? it.name ?? '-';
    const url = it.url ?? it.href ?? '';
    const hasLink = String(url || '').trim().length > 0;
    return `
      <li>
        <i class="${esc(iconClass)}"></i>
        ${hasLink ? `<a href="${esc(safeHref(url))}">${esc(title)}</a>` : `<span>${esc(title)}</span>`}
      </li>
    `;
  }).join('');
}

function renderMarquee(noticeMarquee, announcementsArr, noticesArr){
  const host = document.getElementById('announcementMarquee');
  if(!host) return;

  let parts = [];
  if(noticeMarquee && Array.isArray(noticeMarquee.notice_items_json)){
    parts = noticeMarquee.notice_items_json.map(x => x?.text || x?.title || '').filter(Boolean);
  }
  if(!parts.length && Array.isArray(announcementsArr)){
    parts = announcementsArr.map(x => x?.title || x?.text || '').filter(Boolean);
  }
  if(!parts.length && Array.isArray(noticesArr)){
    parts = noticesArr.map(x => x?.title || x?.text || '').filter(Boolean);
  }

  if(!parts.length){
    host.innerHTML = `<marquee behavior="scroll" direction="left" scrollamount="6">Welcome.</marquee>`;
    return;
  }

  const text = parts.join(' • ');
  host.innerHTML = `<marquee behavior="scroll" direction="left" scrollamount="6">${esc(text)}</marquee>`;
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
    attachStatsObserver();
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
    const avatar = item.avatar || item.image_url || item.photo_url || PLACEHOLDERS.avatar;
    const text   = item.text || item.description || item.quote || '';
    const name   = item.name || item.title || '—';
    const role   = item.role || item.designation || item.company || '';

    const imgId = 'av_' + Math.random().toString(16).slice(2);
    return `
      <div class="col-lg-6">
        <div class="testimonial-card">
          <img id="${imgId}" src="${esc(avatar)}" alt="${esc(name)}" class="testimonial-avatar">
          <p class="testimonial-text">${esc(text || '—')}</p>
          <div class="testimonial-name">${esc(name)}</div>
          <div class="testimonial-role">${esc(role || '—')}</div>
        </div>
      </div>
    `;
  }).join('');

  // attach img fallbacks
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
    const img = story.image_url || story.image || story.photo_url || PLACEHOLDERS.image;
    const desc = story.description || story.text || '';
    const name = story.name || story.title || '—';
    const role = story.role || story.department || story.subtitle || '';

    return `
      <div class="col-lg-3 col-md-6">
        <div class="success-card">
          <img src="${esc(img)}" alt="${esc(name)}" class="success-img">
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
    const img = course.image_url || course.image || PLACEHOLDERS.image;
    const name = course.title || course.name || 'Course';
    const desc = course.blurb || course.description || '';
    const links = Array.isArray(course.links) ? course.links : [];

    return `
      <div class="col-lg-3 col-md-6">
        <div class="course-card">
          <img src="${esc(img)}" alt="${esc(name)}" class="course-img">
          <h3 class="course-title">${esc(name)}</h3>
          <p class="course-desc">${esc(desc || '—')}</p>
          <div class="course-links">
            ${links.length ? links.slice(0,4).map(l => `
              <a href="${esc(safeHref(l.url || l.href))}" class="course-link">${esc(l.text || l.title || 'Link')}</a>
            `).join('') : `<a href="#" class="course-link">View</a>`}
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
    const logo = r.logo_url || r.logo || PLACEHOLDERS.image;
    const title = r.title || r.name || 'Recruiter';
    const href = r.url ? safeHref(r.url) : '#';

    return `
      <a class="recruiter-logo" href="${esc(href)}" ${href !== '#' ? 'target="_blank" rel="noopener"' : ''} title="${esc(title)}">
        <img src="${esc(logo)}" alt="${esc(title)}">
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
  Boot
========================= */
async function bootHome(){
  const alertBox = document.getElementById('homeApiAlert');

  try{
    const json = await fetchHomeJson(HOME_API_URL);

    // normalize
    const payload = (json && isObj(json.data) && !json.hero_carousel) ? json.data : json;

    if(alertBox) alertBox.style.display = 'none';

    renderHero(payload.hero_carousel);
    renderMarquee(payload.notice_marquee, payload.announcements, payload.notices);

    setList('careerList',      payload.career_notices, 'fa-solid fa-chevron-right', 'No career notices.');
    setList('whyMsitList',     payload.why_us,         'fa-solid fa-check',         'No highlights.');
    setList('scholarshipList', payload.scholarships,   'fa-solid fa-gift',          'No scholarships.');

    renderCenterIframe(payload.center_iframe);

    setList('noticeList',       payload.notices,           'fa-solid fa-file',     'No notices.');
    setList('announcementList', payload.announcements,     'fa-solid fa-bell',     'No announcements.');
    setList('placementList',    payload.placement_notices, 'fa-solid fa-building', 'No placements.');

    setList('achievementList', payload.achievements,        'fa-solid fa-medal',    'No achievements.');
    setList('activityList',    payload.student_activities,  'fa-solid fa-calendar', 'No activities.');
    setList('placementList2',  payload.placement_notices,   'fa-solid fa-building', 'No placements.');

    renderStats(payload.stats);
    renderTestimonials(payload.successful_entrepreneurs);
    renderAlumniSpeak(payload.alumni_speak);
    renderSuccessStories(payload.success_stories);
    renderCourses(payload.courses);
    renderRecruiters(payload.recruiters);

  }catch(err){
    console.error('Home boot error:', err);
    if(alertBox){
      alertBox.style.display = '';
      alertBox.innerHTML = `Home API not found. Update <code>$homeApiUrl</code> in this view (or pass it from controller).<br><span style="font-weight:800">Tried:</span> <code>${esc(HOME_API_URL)}</code>`;
    }
  }
}

document.addEventListener('DOMContentLoaded', bootHome);
</script>

@stack('scripts')
</body>
</html>
