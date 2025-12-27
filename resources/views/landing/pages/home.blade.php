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

  {{-- Common UI (your EduPro theme) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    :root{
      --brand: var(--primary-color, #9E363A);
      --brand2: var(--secondary-color, #6B2528);
      --accent: var(--accent-color, #C94B50);
      --line: var(--line-strong, #e6c8ca);
      --surface: var(--surface, #fff);
      --ink: var(--ink, #111);
      --muted: var(--muted-color, #6b7280);
      --shadow: var(--shadow-2, 0 10px 28px rgba(0,0,0,.10));
      --r-xl: 18px;
    }

    body{ background: var(--bg-body, #f6f7fb); color: var(--ink); }

    /* ===== hero ===== */
    .hero-wrap{ position:relative; overflow:hidden; }
    .hero-card{
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .hero-slide{
      min-height: 460px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .hero-slide::before{
      content:"";
      position:absolute; inset:0;
      background: linear-gradient(90deg, rgba(0,0,0,.62), rgba(0,0,0,.18));
    }
    .hero-inner{
      position:relative;
      padding: 56px 26px;
      max-width: 860px;
      color:#fff;
    }
    .hero-kicker{
      display:inline-flex; gap:10px; align-items:center;
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.20);
      font-weight: 700;
      font-size: 12px;
      letter-spacing:.3px;
    }
    .hero-title{
      font-weight: 900;
      line-height: 1.08;
      margin: 14px 0 10px;
      font-size: clamp(28px, 3.2vw, 44px);
    }
    .hero-sub{
      color: rgba(255,255,255,.92);
      font-size: 15px;
      max-width: 60ch;
    }
    .hero-actions{ display:flex; gap:10px; flex-wrap:wrap; margin-top: 16px; }
    .btn-brand{
      background: var(--accent);
      border: 0;
      color:#fff;
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 800;
    }
    .btn-brand:hover{ background: var(--brand); color:#fff; }
    .btn-ghost{
      border: 1px solid rgba(255,255,255,.35);
      background: rgba(255,255,255,.10);
      color:#fff;
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 800;
    }
    .btn-ghost:hover{ background: rgba(255,255,255,.18); color:#fff; }

    /* ===== top strip ===== */
    .top-strip{
      margin-top: 18px;
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .strip-row{ display:flex; align-items:stretch; gap:0; }
    .strip-badge{
      background: var(--brand);
      color:#fff;
      padding: 10px 14px;
      font-weight: 900;
      display:flex;
      align-items:center;
      gap:10px;
      white-space:nowrap;
    }
    .strip-badge i{ opacity:.95; }
    .strip-marq{
      flex:1 1 auto;
      padding: 10px 14px;
      color: var(--muted);
      min-width:0;
    }
    .strip-marq marquee{ width:100%; }
    .strip-links{
      display:flex; gap:8px; padding: 10px 12px;
      border-left: 1px solid var(--line);
      flex:0 0 auto;
      flex-wrap:wrap;
      justify-content:flex-end;
      min-width: 260px;
    }
    .strip-link{
      display:inline-flex; gap:8px; align-items:center;
      border: 1px solid var(--line);
      background: #fff;
      border-radius: 999px;
      padding: 7px 10px;
      text-decoration:none;
      color: var(--brand);
      font-weight: 800;
      font-size: 12px;
      white-space:nowrap;
    }
    html.theme-dark .strip-link{ background: rgba(255,255,255,.05); }
    .strip-link:hover{ background: rgba(158,54,58,.06); }

    /* ===== section ===== */
    .sec{ padding: 26px 0; }
    .sec-title{
      font-weight: 900;
      margin: 0 0 8px;
      font-size: clamp(20px, 2.2vw, 28px);
    }
    .sec-sub{ color: var(--muted); margin:0 0 16px; }

    /* ===== cards ===== */
    .x-card{
      border-radius: var(--r-xl);
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .x-card-pad{ padding: 16px; }
    .x-mini{
      border-radius: 16px;
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: 0 8px 22px rgba(0,0,0,.06);
      padding: 14px;
      height: 100%;
    }
    .x-mini .ico{
      width: 44px; height: 44px;
      border-radius: 14px;
      display:inline-flex; align-items:center; justify-content:center;
      background: rgba(158,54,58,.10);
      color: var(--brand);
      font-size: 18px;
      margin-bottom: 10px;
    }
    .x-mini h6{ font-weight: 900; margin:0 0 6px; }
    .x-mini p{ margin:0; color: var(--muted); font-size: 13px; }

    /* ===== stats ===== */
    .stat{
      display:flex; gap:12px; align-items:center;
      padding: 14px;
      border-radius: 16px;
      border: 1px solid var(--line);
      background: linear-gradient(180deg, rgba(158,54,58,.05), rgba(158,54,58,.02));
      height: 100%;
    }
    .stat .s-ico{
      width: 46px; height: 46px;
      border-radius: 16px;
      background: rgba(201,75,80,.16);
      color: var(--brand);
      display:inline-flex; align-items:center; justify-content:center;
      font-size: 18px;
      flex:0 0 auto;
    }
    .stat .s-num{ font-weight: 950; font-size: 22px; line-height: 1; }
    .stat .s-lbl{ color: var(--muted); font-size: 12px; margin-top: 2px; }

    /* ===== courses ===== */
    .course{
      border-radius: 18px;
      border: 1px solid var(--line);
      background: var(--surface);
      box-shadow: 0 8px 22px rgba(0,0,0,.06);
      padding: 14px;
      height: 100%;
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .course:hover{ transform: translateY(-2px); box-shadow: 0 12px 26px rgba(0,0,0,.10); }
    .course .tag{
      display:inline-flex;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      background: rgba(158,54,58,.10);
      color: var(--brand);
      border: 1px solid rgba(158,54,58,.18);
    }
    .course h6{ font-weight: 950; margin: 10px 0 6px; }
    .course p{ margin:0; color: var(--muted); font-size: 13px; }
    .course a{ text-decoration:none; color: var(--brand); font-weight: 900; font-size: 13px; }

    /* ===== lists ===== */
    .clean-list{ list-style:none; padding:0; margin:0; }
    .clean-list li{
      display:flex; gap:10px; align-items:flex-start;
      padding: 10px 0;
      border-bottom: 1px dashed rgba(0,0,0,.14);
    }
    .clean-list li:last-child{ border-bottom:0; }
    .clean-list i{ color: var(--brand); margin-top: 2px; }

    /* ===== enquiry form ===== */
    .enq{
      background: linear-gradient(135deg, rgba(158,54,58,.10), rgba(201,75,80,.06));
    }
    .form-control,.form-select{
      border-radius: 14px;
      border: 1px solid var(--line);
      min-height: 44px;
    }
    .form-control:focus,.form-select:focus{
      border-color: rgba(158,54,58,.55);
      box-shadow: 0 0 0 .2rem rgba(201,75,80,.18);
    }

    /* ===== simple recruiter badges ===== */
    .brand-badge{
      border: 1px solid var(--line);
      background: var(--surface);
      border-radius: 999px;
      padding: 10px 12px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight: 900;
      color: var(--muted);
      height: 46px;
    }

    @media (max-width: 992px){
      .strip-links{ min-width: 0; border-left: 0; border-top: 1px solid var(--line); width: 100%; justify-content:flex-start; }
      .strip-row{ flex-wrap:wrap; }
    }
  </style>
</head>

<body>

{{-- Header (your existing) --}}
@include('landing.components.header')

<main class="pb-4">

  {{-- ================= HERO ================= --}}
  <section class="container mt-3 hero-wrap">
    <div class="hero-card">
      <div id="homeHero" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#homeHero" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#homeHero" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#homeHero" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner">
          {{-- Slide 1 --}}
          <div class="carousel-item active">
            <div class="hero-slide" style="background-image:url('{{ asset('assets/media/images/web/hero-1.jpg') }}');">
              <div class="hero-inner">
                <div class="hero-kicker">
                  <i class="fa-solid fa-graduation-cap"></i>
                  <span>Approved • Affiliated • Accredited</span>
                </div>
                <h1 class="hero-title">Build Your Future With Industry-Ready Education</h1>
                <p class="hero-sub">
                  A modern college portal experience — admissions, academics, notices, events, and placements — all in one place.
                </p>
                <div class="hero-actions">
                  <a href="/admissions" class="btn btn-brand">
                    <i class="fa-solid fa-paper-plane me-1"></i> Apply Now
                  </a>
                  <a href="/courses" class="btn btn-ghost">
                    <i class="fa-solid fa-layer-group me-1"></i> Explore Programs
                  </a>
                  <a href="/contact" class="btn btn-ghost">
                    <i class="fa-solid fa-phone me-1"></i> Contact
                  </a>
                </div>
              </div>
            </div>
          </div>

          {{-- Slide 2 --}}
          <div class="carousel-item">
            <div class="hero-slide" style="background-image:url('{{ asset('assets/media/images/web/hero-2.jpg') }}');">
              <div class="hero-inner">
                <div class="hero-kicker">
                  <i class="fa-solid fa-flask"></i>
                  <span>Labs • Innovation • Research</span>
                </div>
                <h2 class="hero-title">Hands-On Learning With Modern Labs & Mentors</h2>
                <p class="hero-sub">
                  Learn with practical projects, mentorship, and skill development that matches real industry needs.
                </p>
                <div class="hero-actions">
                  <a href="/campus-life" class="btn btn-brand">
                    <i class="fa-solid fa-camera-retro me-1"></i> Campus Life
                  </a>
                  <a href="/research" class="btn btn-ghost">
                    <i class="fa-solid fa-microscope me-1"></i> Research
                  </a>
                </div>
              </div>
            </div>
          </div>

          {{-- Slide 3 --}}
          <div class="carousel-item">
            <div class="hero-slide" style="background-image:url('{{ asset('assets/media/images/web/hero-3.jpg') }}');">
              <div class="hero-inner">
                <div class="hero-kicker">
                  <i class="fa-solid fa-briefcase"></i>
                  <span>Training • Internships • Placements</span>
                </div>
                <h2 class="hero-title">A Strong Path To Internships & Placements</h2>
                <p class="hero-sub">
                  Career guidance, aptitude training, mock interviews, and campus drives to help you land your dream role.
                </p>
                <div class="hero-actions">
                  <a href="/placements" class="btn btn-brand">
                    <i class="fa-solid fa-chart-line me-1"></i> Placement Cell
                  </a>
                  <a href="/alumni" class="btn btn-ghost">
                    <i class="fa-solid fa-users me-1"></i> Alumni
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#homeHero" data-bs-slide="prev" aria-label="Previous slide">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#homeHero" data-bs-slide="next" aria-label="Next slide">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
      </div>
    </div>

    {{-- ================= TOP STRIP (Notices + Quick Links) ================= --}}
    <div class="top-strip mt-3">
      <div class="strip-row">
        <div class="strip-badge">
          <i class="fa-solid fa-bullhorn"></i>
          <span>Announcements</span>
        </div>
        <div class="strip-marq">
          <marquee behavior="scroll" direction="left" scrollamount="5">
            <strong>Admissions Open 2026</strong> • Scholarship forms available •
            New semester routine published •
            Training session for final year students next week •
            Campus drive schedule updated
          </marquee>
        </div>
        <div class="strip-links">
          <a class="strip-link" href="/notices"><i class="fa-solid fa-newspaper"></i> Notices</a>
          <a class="strip-link" href="/events"><i class="fa-solid fa-calendar-days"></i> Events</a>
          <a class="strip-link" href="/placements"><i class="fa-solid fa-building"></i> Placements</a>
          <a class="strip-link" href="/admissions"><i class="fa-solid fa-pen-to-square"></i> Admissions</a>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= HIGHLIGHTS ================= --}}
  <section class="container sec">
    <div class="row g-3">
      <div class="col-lg-3 col-md-6">
        <div class="x-mini">
          <div class="ico"><i class="fa-solid fa-award"></i></div>
          <h6>Accreditation & Quality</h6>
          <p>Academic excellence with structured outcomes, audits, and continuous improvement.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="x-mini">
          <div class="ico"><i class="fa-solid fa-chalkboard-user"></i></div>
          <h6>Expert Faculty</h6>
          <p>Experienced mentors with supportive learning, labs, and project-based guidance.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="x-mini">
          <div class="ico"><i class="fa-solid fa-laptop-code"></i></div>
          <h6>Skill-First Learning</h6>
          <p>Workshops, coding clubs, industry talks, and internship-oriented training.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="x-mini">
          <div class="ico"><i class="fa-solid fa-people-group"></i></div>
          <h6>Clubs & Activities</h6>
          <p>Sports, cultural events, tech fests, and leadership opportunities on campus.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= STATS + WHY US ================= --}}
  <section class="container sec pt-0">
    <div class="row g-3 align-items-stretch">
      <div class="col-lg-7">
        <div class="x-card x-card-pad h-100">
          <h2 class="sec-title mb-1">Why Choose Our College</h2>
          <p class="sec-sub mb-3">A portal-style homepage inspired by typical engineering college sites (courses, notices, placements, enquiry).</p>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="stat">
                <div class="s-ico"><i class="fa-solid fa-book-open"></i></div>
                <div>
                  <div class="s-num" data-count="12">0</div>
                  <div class="s-lbl">UG/PG Programs</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="stat">
                <div class="s-ico"><i class="fa-solid fa-user-tie"></i></div>
                <div>
                  <div class="s-num" data-count="180">0</div>
                  <div class="s-lbl">Faculty Members</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="stat">
                <div class="s-ico"><i class="fa-solid fa-building-columns"></i></div>
                <div>
                  <div class="s-num" data-count="25">0</div>
                  <div class="s-lbl">Years of Excellence</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="stat">
                <div class="s-ico"><i class="fa-solid fa-briefcase"></i></div>
                <div>
                  <div class="s-num" data-count="90">0</div>
                  <div class="s-lbl">Placement Partners</div>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-3" style="border-color:var(--line)">

          <div class="row g-3">
            <div class="col-md-6">
              <ul class="clean-list">
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Industry tie-ups</strong> for training & internships.</span></li>
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Modern labs</strong> for core departments.</span></li>
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Scholarships</strong> for eligible students.</span></li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul class="clean-list">
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Student activities</strong> & clubs for growth.</span></li>
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Career guidance</strong> and mock interviews.</span></li>
                <li><i class="fa-solid fa-circle-check"></i><span><strong>Central portal</strong> for notices & updates.</span></li>
              </ul>
            </div>
          </div>

        </div>
      </div>

      <div class="col-lg-5">
        <div class="x-card x-card-pad h-100">
          <h2 class="sec-title mb-1">Placement Notice</h2>
          <p class="sec-sub mb-3">Recent campus drives (static sample data).</p>

          <ul class="clean-list">
            <li>
              <i class="fa-solid fa-building"></i>
              <div>
                <div class="fw-bold">TechCorp Drive — 15 Jan 2026</div>
                <div class="text-muted small">Eligible: CSE/IT/ECE • YOP: 2026</div>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-building"></i>
              <div>
                <div class="fw-bold">DataWorks Hiring — 22 Jan 2026</div>
                <div class="text-muted small">Eligible: CSE/CSBS • YOP: 2026</div>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-building"></i>
              <div>
                <div class="fw-bold">Core Engineering Drive — 03 Feb 2026</div>
                <div class="text-muted small">Eligible: EE/ME/CE • YOP: 2026</div>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-building"></i>
              <div>
                <div class="fw-bold">FinServe Campus — 10 Feb 2026</div>
                <div class="text-muted small">Eligible: BBA/CS/IT • YOP: 2026</div>
              </div>
            </li>
          </ul>

          <div class="d-flex gap-2 flex-wrap mt-3">
            <a href="/placements" class="btn btn-brand">
              <i class="fa-solid fa-arrow-right me-1"></i> View All
            </a>
            <a href="/training" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
              <i class="fa-solid fa-dumbbell me-1"></i> Training
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= COURSES OFFERED ================= --}}
  <section class="container sec pt-0">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-2">
      <div>
        <h2 class="sec-title mb-1">Courses Offered</h2>
        <p class="sec-sub mb-0">Popular departments (static preview — connect to DB later).</p>
      </div>
      <a href="/courses" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
        <i class="fa-solid fa-layer-group me-1"></i> All Programs
      </a>
    </div>

    <div class="row g-3">
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Computer Science & Engineering</h6>
          <p>Programming, systems, AI, cloud and modern software engineering.</p>
          <div class="mt-2"><a href="/departments/cse">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Electronics & Communication</h6>
          <p>Circuits, communication, VLSI, embedded systems and IoT.</p>
          <div class="mt-2"><a href="/departments/ece">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Electrical Engineering</h6>
          <p>Power systems, machines, control, renewable energy and design.</p>
          <div class="mt-2"><a href="/departments/ee">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Mechanical Engineering</h6>
          <p>Manufacturing, thermal, CAD/CAM, robotics and automation.</p>
          <div class="mt-2"><a href="/departments/me">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Civil Engineering</h6>
          <p>Structures, construction, surveying, environment and planning.</p>
          <div class="mt-2"><a href="/departments/ce">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>Information Technology</h6>
          <p>Software, databases, networks, security and enterprise systems.</p>
          <div class="mt-2"><a href="/departments/it">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">B.Tech</span>
          <h6>AI & Data Science</h6>
          <p>ML, analytics, data engineering, NLP and applied AI projects.</p>
          <div class="mt-2"><a href="/departments/aids">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="course">
          <span class="tag">UG</span>
          <h6>BBA</h6>
          <p>Business fundamentals with modern skills and industry exposure.</p>
          <div class="mt-2"><a href="/departments/bba">View Department <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= NEWS + EVENTS ================= --}}
  <section class="container sec pt-0">
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="x-card x-card-pad h-100">
          <h2 class="sec-title mb-1">Notice & Updates</h2>
          <p class="sec-sub mb-3">Academic announcements, circulars, and student updates.</p>

          <ul class="clean-list">
            <li>
              <i class="fa-solid fa-thumbtack"></i>
              <div>
                <div class="fw-bold">Semester Registration Starts</div>
                <div class="text-muted small">Registration window: 05 Jan 2026 – 12 Jan 2026</div>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-thumbtack"></i>
              <div>
                <div class="fw-bold">Scholarship Form Submission</div>
                <div class="text-muted small">Submit documents to admin office by 20 Jan 2026</div>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-thumbtack"></i>
              <div>
                <div class="fw-bold">Workshop: Modern Web Development</div>
                <div class="text-muted small">Free workshop for 1st & 2nd year — Limited seats</div>
              </div>
            </li>
          </ul>

          <div class="mt-3">
            <a href="/notices" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
              <i class="fa-solid fa-newspaper me-1"></i> View All Notices
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="x-card x-card-pad h-100">
          <h2 class="sec-title mb-1">Events & Activities</h2>
          <p class="sec-sub mb-3">Campus events, seminars, and student activities.</p>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="x-mini">
                <div class="ico"><i class="fa-solid fa-calendar-check"></i></div>
                <h6>Orientation 2026</h6>
                <p>Welcome session for new students with department briefings.</p>
                <div class="mt-2 small text-muted"><i class="fa-regular fa-clock me-1"></i> 08 Jan 2026</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="x-mini">
                <div class="ico"><i class="fa-solid fa-robot"></i></div>
                <h6>Tech Fest</h6>
                <p>Hackathons, robotics, coding competitions, and exhibitions.</p>
                <div class="mt-2 small text-muted"><i class="fa-regular fa-clock me-1"></i> 20–22 Feb 2026</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="x-mini">
                <div class="ico"><i class="fa-solid fa-microphone"></i></div>
                <h6>Industry Talk</h6>
                <p>Guest lecture on AI careers, interview prep, and portfolios.</p>
                <div class="mt-2 small text-muted"><i class="fa-regular fa-clock me-1"></i> 28 Jan 2026</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="x-mini">
                <div class="ico"><i class="fa-solid fa-futbol"></i></div>
                <h6>Sports Week</h6>
                <p>Inter-department tournaments and fitness activities.</p>
                <div class="mt-2 small text-muted"><i class="fa-regular fa-clock me-1"></i> 10–15 Mar 2026</div>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <a href="/events" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
              <i class="fa-solid fa-calendar-days me-1"></i> View Calendar
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= RECRUITERS / PLACEMENTS ================= --}}
  <section class="container sec pt-0">
    <div class="x-card x-card-pad">
      <div class="d-flex align-items-end justify-content-between flex-wrap gap-2">
        <div>
          <h2 class="sec-title mb-1">Our Recruiters</h2>
          <p class="sec-sub mb-0">A snapshot of companies that frequently hire from campus (static sample).</p>
        </div>
        <a href="/placements" class="btn btn-brand">
          <i class="fa-solid fa-briefcase me-1"></i> Placement Cell
        </a>
      </div>

      <div class="row g-2 mt-2">
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">TCS</div></div>
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">Wipro</div></div>
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">Infosys</div></div>
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">Capgemini</div></div>
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">HCL</div></div>
        <div class="col-6 col-md-3 col-lg-2"><div class="brand-badge">Tech Mahindra</div></div>
      </div>

      <hr class="my-3" style="border-color:var(--line)">

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="stat">
            <div class="s-ico"><i class="fa-solid fa-file-signature"></i></div>
            <div>
              <div class="s-num" data-count="550">0</div>
              <div class="s-lbl">Offers (Last Season)</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="stat">
            <div class="s-ico"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <div>
              <div class="s-num" data-count="12">0</div>
              <div class="s-lbl">Highest Package (LPA)</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="stat">
            <div class="s-ico"><i class="fa-solid fa-people-arrows"></i></div>
            <div>
              <div class="s-num" data-count="80">0</div>
              <div class="s-lbl">Internship Partners</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  {{-- ================= ENQUIRY FORM ================= --}}
  <section class="container sec pt-0">
    <div class="x-card x-card-pad enq">
      <div class="row g-3 align-items-center">
        <div class="col-lg-5">
          <h2 class="sec-title mb-1">Start Your Journey Toward a Brighter Future</h2>
          <p class="sec-sub mb-0">
            Have questions about admissions, courses, or campus life? Share your details and we’ll reach out.
          </p>

          <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="tel:+919999999999" class="btn btn-brand">
              <i class="fa-solid fa-phone me-1"></i> Call Now
            </a>
            <a href="https://wa.me/919999999999" target="_blank" rel="noopener" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
              <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
            </a>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="x-card x-card-pad" style="box-shadow:none;">
            <form action="javascript:void(0)" id="enquiryForm">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="small fw-bold mb-1">First Name</label>
                  <input class="form-control" name="first_name" placeholder="Enter first name" required>
                </div>
                <div class="col-md-6">
                  <label class="small fw-bold mb-1">Last Name</label>
                  <input class="form-control" name="last_name" placeholder="Enter last name" required>
                </div>
                <div class="col-md-6">
                  <label class="small fw-bold mb-1">Phone</label>
                  <input class="form-control" name="phone" placeholder="+91..." required>
                </div>
                <div class="col-md-6">
                  <label class="small fw-bold mb-1">Email</label>
                  <input class="form-control" type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="col-md-12">
                  <label class="small fw-bold mb-1">Interested Program</label>
                  <select class="form-select" name="program">
                    <option>B.Tech (CSE)</option>
                    <option>B.Tech (ECE)</option>
                    <option>B.Tech (EE)</option>
                    <option>B.Tech (ME)</option>
                    <option>B.Tech (CE)</option>
                    <option>BBA</option>
                    <option>Other</option>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="small fw-bold mb-1">Message (optional)</label>
                  <textarea class="form-control" name="message" rows="3" placeholder="Ask about admissions, fees, scholarship, etc."></textarea>
                </div>
                <div class="col-12">
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="agree" required>
                    <label class="form-check-label small" for="agree">
                      I agree to be contacted regarding admissions and updates.
                    </label>
                  </div>
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap mt-2">
                  <button class="btn btn-brand" type="submit">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Enquiry
                  </button>
                  <a href="/admissions" class="btn btn-outline-secondary" style="border-radius:14px;border-color:var(--line);">
                    <i class="fa-solid fa-circle-info me-1"></i> Admission Info
                  </a>
                </div>
              </div>
              <div id="enqToast" class="alert alert-success d-none mt-3 mb-0">
                ✅ Thanks! Your enquiry is recorded (static demo). Connect this form to your backend API when ready.
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

{{-- footer skipped as requested --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  // counter animation
  function animateCounters(){
    const els = document.querySelectorAll('[data-count]');
    els.forEach(el => {
      const target = parseInt(el.getAttribute('data-count') || '0', 10);
      const duration = 900;
      const start = performance.now();
      const from = 0;

      function tick(t){
        const p = Math.min(1, (t - start) / duration);
        const val = Math.floor(from + (target - from) * (0.15 + 0.85 * p)); // smooth-ish
        el.textContent = val.toString();
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    });
  }

  // run counters when visible
  const obs = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)){
      animateCounters();
      obs.disconnect();
    }
  }, { threshold: 0.25 });

  const watch = document.querySelector('main');
  if (watch) obs.observe(watch);

  // enquiry demo
  const form = document.getElementById('enquiryForm');
  const toast = document.getElementById('enqToast');
  if (form){
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      toast?.classList.remove('d-none');
      form.reset();
      setTimeout(() => toast?.classList.add('d-none'), 3500);
    });
  }
})();
</script>

@stack('scripts')
</body>
</html>
