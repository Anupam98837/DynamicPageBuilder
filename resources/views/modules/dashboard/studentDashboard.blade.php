{{-- resources/views/modules/dashboard/studentDashboard.blade.php --}}
@section('title','Student Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Student Dashboard (MSIT theme)
 * For now: Logo + Welcome text only
 * Keeps the SAME structure/classes as Admin dashboard
 * ========================= */

.ad-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}

/* Hero (same as admin structure) */
.ad-hero{
  position:relative;
  border-radius:22px;
  padding:20px 20px;
  color:#fff;
  overflow:hidden;
  box-shadow:var(--shadow-3);
  background:linear-gradient(135deg,
    var(--primary-color) 0%,
    color-mix(in oklab, var(--primary-color) 70%, #7c3aed) 100%);
  border:1px solid color-mix(in oklab, #fff 15%, transparent);
}
.ad-hero::before{
  content:'';
  position:absolute;right:-80px;top:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle, rgba(255,255,255,.14) 0%, rgba(255,255,255,0) 70%);
}
.ad-hero-inner{position:relative;z-index:1;display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.ad-hero-left{min-width:260px;flex:1;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.ad-hero-title{font-size:26px;font-weight:800;letter-spacing:-.2px;margin:0;font-family:var(--font-head)}
.ad-hero-sub{margin:6px 0 0;font-size:14px;opacity:.92}

/* Logo */
.sd-logo{
  width:54px;height:54px;border-radius:14px;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.18);
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;flex:0 0 auto;
}
.sd-logo img{width:100%;height:100%;object-fit:contain;padding:10px;filter:drop-shadow(0 6px 10px rgba(0,0,0,.15))}

/* Grid / Cards (same skeleton structure) */
.ad-grid{margin-top:14px;display:grid;grid-template-columns:repeat(12, minmax(0,1fr));gap:14px}
.ad-col-12{grid-column:span 12}

.ad-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.ad-card-head{
  padding:14px 16px;
  border-bottom:1px solid var(--line-soft);
  display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.ad-card-title{
  display:flex;align-items:center;gap:10px;
  font-weight:800;color:var(--ink);
  font-family:var(--font-head);
}
.ad-card-sub{font-size:12.5px;color:var(--muted-color);margin-top:3px}
.ad-card-body{padding:14px 16px}

/* Welcome block */
.sd-welcome{
  border:1px dashed var(--line-strong);
  border-radius:14px;
  padding:14px 14px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
}
.sd-welcome h3{
  margin:0 0 6px;
  font-size:18px;
  font-weight:900;
  font-family:var(--font-head);
  color:var(--ink);
}
.sd-welcome p{
  margin:0;
  color:var(--muted-color);
  font-size:14px;
  line-height:1.6;
}

@media (max-width: 992px){
  .ad-hero-left{min-width:0}
  .sd-logo{width:52px;height:52px}
}
</style>
@endpush

@section('content')
<div class="ad-wrap">

  {{-- HERO --}}
  <div class="ad-hero">
    <div class="ad-hero-inner">
      <div class="ad-hero-left">
        <div class="sd-logo">
          {{-- Use your actual logo path here if different --}}
          <img src="{{ asset('assets/images/logo.png') }}"
               alt="Logo"
               onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=&quot;fa-solid fa-graduation-cap&quot; style=&quot;font-size:20px;opacity:.95&quot;></i>';">
        </div>

        <div>
          <h1 class="ad-hero-title">Student Dashboard</h1>
          <div class="ad-hero-sub">Welcome back! Your learning space is getting ready 🚀</div>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('/student/courses') }}" class="btn btn-light">
          <i class="fa-solid fa-book-open"></i> My Courses
        </a>
      </div>
    </div>
  </div>

  <div class="ad-grid">

    <div class="ad-col-12">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-hand-sparkles"></i> Welcome</div>
            <div class="ad-card-sub">This student dashboard is currently in setup mode</div>
          </div>
        </div>

        <div class="ad-card-body">
          <div class="sd-welcome">
            <h3>Hey Student 👋</h3>
            <p>
              Your dashboard will soon show your courses, attendance, assignments, results, and announcements —
              all in one place. For now, you can start by visiting <strong>My Courses</strong>.
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
