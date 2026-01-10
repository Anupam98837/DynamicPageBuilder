{{-- resources/views/layouts/msit/structure.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title','MSIT Home Builder Admin')</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

  @stack('styles')
  @yield('styles')

  <style>
    :root{
      --w3-rail-w: 256px;
      --w3-rail-bg:       var(--surface);
      --w3-rail-text:     var(--text-color);
      --w3-rail-muted:    var(--muted-color);
      --w3-rail-border:   var(--line-strong);
      --w3-rail-hover:    rgba(2,6,23,.045);
      --w3-rail-active:   rgba(13,148,136,.12);

      --w3-rule-grad-l:   linear-gradient(90deg, rgba(2,6,23,0), rgba(2,6,23,.14), rgba(2,6,23,0));
      --w3-rule-grad-d:   linear-gradient(90deg, rgba(226,232,240,0), rgba(226,232,240,.22), rgba(226,232,240,0));
    }

    body{min-height:100dvh;background:var(--bg-body);color:var(--text-color)}

    /* Sidebar */
    .w3-sidebar{
      position:fixed; inset:0 auto 0 0; width:var(--w3-rail-w); background:var(--w3-rail-bg);
      border-right:1px solid var(--w3-rail-border); display:flex; flex-direction:column; z-index:1041;
      transform:translateX(0); transition:transform .28s ease;
    }
    .w3-sidebar-head{
      height:88px;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:12px 0;
      border-bottom:1px solid var(--w3-rail-border);
    }
    .w3-brand{display:flex; align-items:center; justify-content:center; text-decoration:none;}
    .w3-brand img{height:56px; width:auto; max-width:180px; display:block;}

    .w3-sidebar-scroll{flex:1; overflow:auto; padding:8px 10px}

    /* Section separators */
    .w3-nav-section{padding:10px 6px 6px}
    .w3-section-title{
      display:flex; align-items:center; gap:8px; color:var(--primary-color);
      font-size:.72rem; font-weight:700; letter-spacing:.12rem; text-transform:uppercase; padding:0 6px;
    }
    .w3-section-rule{height:10px; display:grid; align-items:center}
    .w3-section-rule::before{content:""; height:1px; width:100%; background:var(--w3-rule-grad-l)}
    html.theme-dark .w3-section-rule::before{ background:var(--w3-rule-grad-d) }

    /* Menu */
    .w3-menu{display:grid; gap:4px; padding:6px 4px}
    .w3-link{
      display:flex; align-items:center; gap:10px; padding:9px 10px;
      color:var(--w3-rail-text); border-radius:10px;
      transition:background .18s ease, transform .18s ease;
      text-decoration:none;
    }
    .w3-link i{opacity:.9; min-width:18px; text-align:center}
    .w3-link:hover{background:var(--w3-rail-hover); transform:translateX(2px)}
    .w3-link.active{background:var(--w3-rail-active); position:relative}
    .w3-link.active::before{
      content:""; position:absolute; left:-6px; top:8px; bottom:8px; width:3px; background:var(--accent-color); border-radius:4px;
    }

    /* Group / Submenu */
    .w3-group{display:grid; gap:4px; margin-top:2px}
    .w3-toggle{cursor:pointer}
    .w3-toggle .w3-chev{
      margin-left:auto; margin-right:2px; padding-left:6px;
      transition:transform .18s ease; opacity:.85;
    }
    .w3-toggle.w3-open .w3-chev{transform:rotate(180deg)}

    .w3-submenu{
      display:grid; gap:2px; margin-left:8px; padding-left:8px; border-left:1px dashed var(--w3-rail-border);
      max-height:0; overflow:hidden; transition:max-height .24s ease;
    }
    .w3-submenu.w3-open{max-height:800px}
    .w3-submenu .w3-link{padding:8px 10px 8px 34px; font-size:.86rem}

    .w3-sidebar-foot{border-top:1px solid var(--w3-rail-border); padding:8px 10px}

    /* Appbar */
    .w3-appbar{
      position:sticky; top:0; z-index:1030; height:64px; background:var(--surface);
      border-bottom:1px solid var(--line-strong); display:flex; align-items:center;
    }
    .w3-appbar-inner{
      width:100%;
      display:flex; align-items:center; gap:10px; padding:0 12px;
    }

    /* Mobile appbar logo */
    .w3-app-logo{display:flex; align-items:center; gap:8px; text-decoration:none}
    .w3-app-logo img{height:22px}
    .w3-app-logo span{font-family:var(--font-head); font-weight:700; color:var(--ink); font-size:.98rem}

    .w3-icon-btn{
      width:36px; height:36px; display:inline-grid; place-items:center; border:1px solid var(--line-strong);
      background:#fff; color:var(--secondary-color); border-radius:999px;
      transition:transform .18s ease, background .18s ease;
    }
    .w3-icon-btn:hover{background:#f6f8fc; transform:translateY(-1px)}

    /* Hamburger (morph) */
    .w3-hamburger{
      width:40px; height:40px; border:1px solid var(--line-strong); border-radius:999px;
      background:#fff; display:inline-grid; place-items:center; cursor:pointer
    }
    .w3-bars{position:relative; width:18px; height:12px}
    .w3-bar{
      position:absolute; left:0; width:100%; height:2px; background:#1f2a44; border-radius:2px;
      transition:transform .25s ease, opacity .2s ease, top .25s ease
    }
    .w3-bar:nth-child(1){top:0}
    .w3-bar:nth-child(2){top:5px}
    .w3-bar:nth-child(3){top:10px}
    .w3-hamburger.is-active .w3-bar:nth-child(1){top:5px; transform:rotate(45deg)}
    .w3-hamburger.is-active .w3-bar:nth-child(2){opacity:0}
    .w3-hamburger.is-active .w3-bar:nth-child(3){top:5px; transform:rotate(-45deg)}

    /* Content */
    .w3-content{
      padding:16px;
      max-width:1280px;
      margin-inline:auto;
      transition:padding .28s ease;
    }
    @media (min-width: 992px){
      .w3-content{ padding-left: calc(16px + var(--w3-rail-w)); }
      .w3-app-logo{display:none}
    }

    /* Overlay (mobile sidebar overlay) */
    .w3-overlay{
      position:fixed; top:0; bottom:0; right:0; left:var(--w3-rail-w);
      background:rgba(0,0,0,.45); z-index:1040; opacity:0; visibility:hidden; pointer-events:none;
      transition:opacity .2s ease, visibility .2s ease;
    }
    .w3-overlay.w3-on{opacity:1; visibility:visible; pointer-events:auto}

    /* Mobile */
    @media (max-width: 991px){
      .w3-sidebar{transform:translateX(-100%)}
      .w3-sidebar.w3-on{transform:translateX(0)}
      .w3-content{ padding-left:16px; }
      .js-theme-btn{display:none!important}
      .w3-app-logo{display:flex}
      .w3-overlay{left:var(--w3-rail-w)}
    }

    /* Dark flips */
    html.theme-dark .w3-sidebar{background:var(--surface); border-right-color:var(--line-strong)}
    html.theme-dark .w3-sidebar-head{border-bottom-color:var(--line-strong)}
    html.theme-dark .w3-link:hover{background:#0c172d}
    html.theme-dark .w3-link.active{background:rgba(20,184,166,.12)}
    html.theme-dark .w3-overlay{background:rgba(0,0,0,.55)}

    html.theme-dark .w3-appbar{background:var(--surface); border-bottom-color:var(--line-strong)}
    html.theme-dark .w3-icon-btn, html.theme-dark .w3-hamburger{background:var(--surface); border-color:var(--line-strong); color:var(--text-color)}
    html.theme-dark .w3-icon-btn:hover, html.theme-dark .w3-hamburger:hover{background:#0c172d}
    html.theme-dark .w3-bar{ background:#e8edf7; }

    html.theme-dark .dropdown-menu{ background:#0f172a; border-color:var(--line-strong); }
    html.theme-dark .dropdown-menu .dropdown-header{ color:var(--text-color); }
    html.theme-dark .dropdown-menu .dropdown-item{ color:var(--text-color); }
    html.theme-dark .dropdown-menu .dropdown-item:hover{ background:#13203a; color:var(--accent-color); }
  </style>

  <style>
    /* Dark theme scrollbars */
    html.theme-dark ::-webkit-scrollbar { width: 8px !important; }
    html.theme-dark ::-webkit-scrollbar-track { background: #1e293b !important; border-radius: 4px !important; }
    html.theme-dark ::-webkit-scrollbar-thumb { background: #475569 !important; border-radius: 4px !important; }
    html.theme-dark ::-webkit-scrollbar-thumb:hover { background: #64748b !important; }
    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar { width: 6px !important; }
    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-track { background: #1e293b !important; }
    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-thumb { background: #475569 !important; }
  </style>
</head>
<body>

{{-- ✅ Overlay partial inside wrapper (block while loading, none after loading) --}}
<div id="pageLoadingWrap" style="display:block;">
  @include('partials.overlay')
</div>
<!-- Sidebar -->
<aside id="sidebar" class="w3-sidebar" aria-label="Sidebar">
  <div class="w3-sidebar-head">
    <a href="/dashboard" class="w3-brand">
      <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="MSIT Home Builder">
    </a>
  </div>

  <div class="w3-sidebar-scroll">

    <!-- Overview (STATIC) -->
    <div class="w3-nav-section">
      <div class="w3-section-title"><i class="fa-solid fa-chart-simple"></i> OVERVIEW</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="Overview">
      <a href="/dashboard" class="w3-link">
        <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a>
    </nav>


    {{-- ✅ ADMIN FULL MENU (static) --}}
    <div id="adminFullMenu" style="display:none">
      <nav class="w3-menu" aria-label="Site Builder (Admin)">

                {{-- =======================
           ACCESS & CONTROL
        ======================== --}}
        <div class="w3-nav-section" style="padding:6px 6px 2px">
          <div class="w3-section-title"><i class="fa-solid fa-user-shield"></i> ACCESS & CONTROL</div>
          <div class="w3-section-rule"></div>
        </div>
 
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-users" aria-expanded="false">
            <i class="fa-solid fa-user-shield"></i><span>Users & Access</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-users" class="w3-submenu" role="group" aria-label="Users submenu">
            <a href="/user/manage" class="w3-link">All Users</a>
                        <a href="/senior-authority/manage" class="w3-link">Senior Authority</a>
                                <a href="/faculty/manage" class="w3-link">Faculty</a>
            <a href="/other-roles/manage" class="w3-link">Other Roles</a>
            <a href="/students/manage" class="w3-link">Students</a>
 
          </div>
         
        </div>

        {{-- =======================
           ACADEMICS
        ======================== --}}
        <div class="w3-nav-section" style="padding:10px 6px 2px">
          <div class="w3-section-title"><i class="fa-solid fa-graduation-cap"></i> ACADEMICS</div>
          <div class="w3-section-rule"></div>
        </div>

        <!-- Departments -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-departments" aria-expanded="false">
            <i class="fa-solid fa-building"></i><span>Departments</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-departments" class="w3-submenu" role="group" aria-label="Departments submenu">
            <a href="/department/manage" class="w3-link">Manage Departments</a>
            <a href="/department/curriculum-syllabus" class="w3-link">Curriculum & Syllabus</a>
            <a href="/department/gallery" class="w3-link">Gallery</a>
          </div>
        </div>

        <!-- Courses -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-courses" aria-expanded="false">
            <i class="fa-solid fa-book-open-reader"></i><span>Courses</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-courses" class="w3-submenu" role="group" aria-label="Courses submenu">
            <a href="/course/manage" class="w3-link">Manage Courses</a>
            <a href="/course/semester/manage" class="w3-link">Manage Semesters</a>
            <a href="/course/semester/section/manage" class="w3-link">Manage Sections</a>
          </div>
        </div>

        {{-- =======================
           SITE SETTINGS & CONTENT (site related changes)
        ======================== --}}
        <div class="w3-nav-section" style="padding:10px 6px 2px">
          <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> SITE SETTINGS & CONTENT</div>
          <div class="w3-section-rule"></div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-header-menu" aria-expanded="false">
            <i class="fa-solid fa-bars"></i><span>Header Menu</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-header-menu" class="w3-submenu" role="group" aria-label="Header Menu submenu">
            <a href="/header/menu/create" class="w3-link">Create Header Menu</a>
            <a href="/header/menu/manage" class="w3-link">Manage Header Menus</a>
            <a href="/top-header/menu" class="w3-link">Top Header Menus</a>
          </div>
        </div>

        <!-- Header Components (Single Link) -->
        <a href="/header-components/manage" class="w3-link">
          <i class="fa-solid fa-header"></i><span>Header Components</span>
        </a>

        <!-- Footer Components -->
        <a href="/footer-components/manage" class="w3-link">
          <i class="fa-solid fa-window-minimize"></i><span>Footer Components</span>
        </a>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-pages" aria-expanded="false">
            <i class="fa-solid fa-file-lines"></i><span>Pages</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-pages" class="w3-submenu" role="group" aria-label="Pages submenu">
            <a href="/pages/create" class="w3-link">Create Page</a>
            <a href="/pages/manage" class="w3-link">Manage Page</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-page-submenu" aria-expanded="false">
            <i class="fa-solid fa-sitemap"></i><span>Page Submenu</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-page-submenu" class="w3-submenu" role="group" aria-label="Page Submenu submenu">
            <a href="/page/submenu/create" class="w3-link">Create Page Submenu</a>
            <a href="/page/submenu/manage" class="w3-link">Manage Page Submenu</a>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-contact-info" aria-expanded="false">
            <i class="fa-solid fa-address-book"></i><span>Contact Info</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-contact-info" class="w3-submenu" role="group" aria-label="Contact Info submenu">
            <a href="/contact-info/manage" class="w3-link">Manage Contact Info</a>
            <!-- <a href="/contact/forms/manage" class="w3-link">Contact Forms</a>
            <a href="/contact/locations/manage" class="w3-link">Office Locations</a> -->
            <a href="/contact-us/manage" class="w3-link">Manage Contacts</a>
            <a href="/contact-us-visibility/manage" class="w3-link">Contact Visibility</a>
          </div>
        </div>

        <!-- Hero Carousel -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-hero-carousel" aria-expanded="false">
            <i class="fa-solid fa-images"></i><span>Hero Carousel</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-hero-carousel" class="w3-submenu" role="group" aria-label="Hero Carousel submenu">
            <a href="/hero-carousel/manage" class="w3-link">Manage Hero Carousel</a>
            <a href="/hero-carousel/settings" class="w3-link">Hero Carousel Settings</a>
          </div>
        </div>

        <!-- Center Iframes -->
        <a href="/center-iframes/manage" class="w3-link">
          <i class="fa-solid fa-window-maximize"></i><span>Center Iframes</span>
        </a>

        <!-- Stats Settings -->
        <a href="/stats/settings" class="w3-link">
          <i class="fa-solid fa-chart-line"></i><span>Stats</span>
        </a>

        <!-- Notice Marquee Settings -->
        <a href="/notice-marquee/settings" class="w3-link">
          <i class="fa-solid fa-scroll"></i><span>Notice Marquee</span>
        </a>

        {{-- =======================
           PLACEMENT & OUTCOMES
        ======================== --}}
        <div class="w3-nav-section" style="padding:10px 6px 2px">
          <div class="w3-section-title"><i class="fa-solid fa-briefcase"></i> PLACEMENT & OUTCOMES</div>
          <div class="w3-section-rule"></div>
        </div>

        <!-- Recruiters (Single Link) -->
        <a href="/recruiters" class="w3-link">
          <i class="fa-solid fa-handshake"></i><span>Recruiters</span>
        </a>

        <!-- Success Stories (Single Link) -->
        <a href="/success-stories/manage" class="w3-link">
          <i class="fa-solid fa-trophy"></i><span>Success Stories</span>
        </a>

        <!-- Events (Single Link) -->
        <a href="/events/manage" class="w3-link">
          <i class="fa-solid fa-calendar-days"></i><span>Events</span>
        </a>

        <!-- Placement -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-placement" aria-expanded="false">
            <i class="fa-solid fa-briefcase"></i><span>Placement</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-placement" class="w3-submenu" role="group" aria-label="Placement submenu">
            <a href="/department/placement-notices" class="w3-link">Placement Notices</a>
            <a href="/department/placed-students" class="w3-link">Placed Students</a>
            <a href="/department/successful-entrepreneurs" class="w3-link">Successful Entrepreneurs</a>
          </div>
        </div>

        <!-- Alumni Speak -->
        <a href="/alumni-speak/manage" class="w3-link">
          <i class="fa-solid fa-microphone"></i><span>Alumni Speak</span>
        </a>

        {{-- =======================
           COMMUNICATIONS (notices/announcements/etc + feedback)
        ======================== --}}
        <div class="w3-nav-section" style="padding:10px 6px 2px">
          <div class="w3-section-title"><i class="fa-solid fa-bell"></i> COMMUNICATIONS</div>
          <div class="w3-section-rule"></div>
        </div>

        <!-- Notifications -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-notifications" aria-expanded="false">
            <i class="fa-solid fa-bell"></i><span>Notifications</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-notifications" class="w3-submenu" role="group" aria-label="Notifications submenu">
            <a href="/department/announcements" class="w3-link">Announcements</a>
            <a href="/department/achievements" class="w3-link">Achievements</a>
            <a href="/department/notices" class="w3-link">Notices</a>
            <a href="/department/student-activities" class="w3-link">Student Activities</a>
            <a href="/career-notices" class="w3-link">Career Notices</a>
            <a href="/why-us" class="w3-link">Why Us</a>
            <a href="/scholarships" class="w3-link">Scholarships</a>
          </div>
        </div>

        <!-- Feedback -->
        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-feedback" aria-expanded="false">
            <i class="fa-solid fa-comment-dots"></i><span>Feedback</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-feedback" class="w3-submenu" role="group" aria-label="Feedback submenu">
            <a href="/feedback/question/manage" class="w3-link">Manage Questions</a>
            <a href="/feedback/post/manage" class="w3-link">Manage Posts</a>
          </div>
        </div>

      </nav>

      <!-- privileges (admin static) -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> privileges</div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Privileges">

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-dashboard-menu" aria-expanded="false">
            <i class="fa-solid fa-puzzle-piece"></i><span>Dashboard Menu</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-dashboard-menu" class="w3-submenu" role="group" aria-label="Dashboard Menu submenu">
            <a href="/dashboard-menu/create" class="w3-link">
              <i class="fa-solid fa-puzzle-piece"></i><span>Create Dashboard Menu</span>
            </a>
            <a href="/dashboard-menu/manage" class="w3-link">
              <i class="fa-solid fa-puzzle-piece"></i><span>Manage Dashboard Menu</span>
            </a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-page-privilege" aria-expanded="false">
            <i class="fa-solid fa-shield-halved"></i><span>Page Privilege</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-page-privilege" class="w3-submenu" role="group" aria-label="Page Privilege submenu">
            <a href="/page-privilege/create" class="w3-link">
              <i class="fa-solid fa-circle-plus"></i><span>Create Page Privilege</span>
            </a>
            <a href="/page-privilege/manage" class="w3-link">
              <i class="fa-solid fa-list-check"></i><span>Manage Page Privilege</span>
            </a>
          </div>
        </div>

      </nav>
    </div>

    {{-- ✅ DYNAMIC MENU (normal users): populated from /api/my/sidebar-menus --}}
    <nav id="dynamicMenu" class="w3-menu" aria-label="Site Builder (Dynamic)" style="display:none"></nav>

    <!-- System (STATIC) -->
    <div class="w3-nav-section">
      <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> SYSTEM</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="System">
      <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-manage-profile" aria-expanded="false">
          <i class="fa-solid fa-user-gear"></i>
          <span>Manage Profile</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>

        <div id="sm-manage-profile" class="w3-submenu" role="group" aria-label="Manage Profile submenu">
          <a href="/user/personal-information/manage" class="w3-link">Personal Information</a>
          <a href="/user/education/manage" class="w3-link">Educations</a>
          <a href="/user/honors/manage" class="w3-link">Honors & Awards</a>
          <a href="/user/journals/manage" class="w3-link">Journals</a>
          <a href="/user/conference-publications/manage" class="w3-link">Conference Publications</a>
          <a href="/user/teaching-engagements/manage" class="w3-link">Teaching Engagements</a>
          <a href="/user/social-media/manage" class="w3-link">Social Media</a>
        </div>
      </div>

      <a href="/settings/general" class="w3-link">
        <i class="fa-solid fa-gear"></i>
        <span>Settings</span>
      </a>
    </nav>

    <!-- Account (mobile only) -->
    <div class="w3-nav-section d-lg-none">
      <div class="w3-section-title"><i class="fa-solid fa-user"></i> ACCOUNT</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu d-lg-none" aria-label="Account">
      <a href="/feedback/submit" class="w3-link"><i class="fa-solid fa-comment-dots"></i><span> Feedback</span></a>
      <a href="/profile" class="w3-link"><i class="fa fa-id-badge"></i><span>Profile</span></a>
      <a href="/settings" class="w3-link"><i class="fa fa-gear"></i><span>Settings</span></a>
    </nav>

  </div>

  <div class="w3-sidebar-foot">
    <a href="#" id="logoutBtnSidebar" class="w3-link" style="padding:8px 10px">
      <i class="fa fa-right-from-bracket"></i><span>Logout</span>
    </a>
  </div>
</aside>


<!-- Appbar -->
<header class="w3-appbar">
  <div class="w3-appbar-inner">
    <button id="btnHamburger" class="w3-hamburger d-lg-none" aria-label="Open menu" aria-expanded="false" title="Menu">
      <span class="w3-bars" aria-hidden="true">
        <span class="w3-bar"></span><span class="w3-bar"></span><span class="w3-bar"></span>
      </span>
    </button>

    <!-- Mobile brand -->
    <a href="/dashboard" class="w3-app-logo d-lg-none">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="MSIT Home Builder">
      <span>MSIT Builder</span>
    </a>

    <strong class="ms-1 d-none d-lg-inline" style="font-family:var(--font-head);color:var(--ink)">
      @yield('title','MSIT Home Builder')
    </strong>

    <div class="ms-auto d-flex align-items-center gap-2">
      <!-- Theme toggle (desktop only) -->
      <button id="btnTheme" class="w3-icon-btn js-theme-btn d-none d-lg-inline-grid" aria-label="Toggle theme" title="Toggle theme">
        <i class="fa-regular fa-moon" id="themeIcon"></i>
      </button>

      <!-- Alerts -->
      <div class="dropdown">
        <a href="#" class="w3-icon-btn" id="alertsMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Alerts" title="Alerts">
          <i class="fa-regular fa-bell"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-2 shadow" style="min-width:320px">
          <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <strong>Notifications</strong>
            <a class="text-muted" href="/notifications">View all</a>
          </div>
          <div class="w3-note rounded-xs">
            <div class="small">
              <strong>Content update</strong> — New notice pending approval.
            </div>
          </div>
        </div>
      </div>

      <!-- User (desktop only) -->
      <div class="dropdown d-none d-lg-block">
        <a href="#" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 px-3" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-regular fa-user"></i>
          <span id="userRoleLabel" class="d-none d-xl-inline">Admin</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header">Account</li>
          <li><a class="dropdown-item" href="/profile"><i class="fa fa-id-badge me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="/settings"><i class="fa fa-gear me-2"></i>Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</header>

<!-- Overlay (mobile sidebar overlay) -->
<div id="sidebarOverlay" class="w3-overlay" aria-hidden="true"></div>

<!-- Content -->
<main class="w3-content mx-auto">
  <section class="panel mx-auto">@yield('content')</section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
@yield('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {
  const html = document.documentElement;

  // ✅ overlay wrapper (block while loading, none after)
  const wrap = document.getElementById('pageLoadingWrap');
  const showLoading = () => { if (wrap) wrap.style.display = 'block'; };
  const hideLoading = () => { if (wrap) wrap.style.display = 'none'; };

  showLoading();

  // ===== Theme
  const THEME_KEY = 'theme';
  const btnTheme = document.getElementById('btnTheme');
  const themeIcon = document.getElementById('themeIcon');

  function setTheme(mode){
    const isDark = mode === 'dark';
    html.classList.toggle('theme-dark', isDark);
    localStorage.setItem(THEME_KEY, mode);
    if (themeIcon) themeIcon.className = isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon';
  }
  setTheme(
    localStorage.getItem(THEME_KEY) ||
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
  );
  btnTheme?.addEventListener('click', () => setTheme(html.classList.contains('theme-dark') ? 'light' : 'dark'));

  // ===== Sidebar toggle (mobile)
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const btnHamburger = document.getElementById('btnHamburger');

  const openSidebar = () => {
    sidebar.classList.add('w3-on');
    overlay.classList.add('w3-on');
    btnHamburger?.classList.add('is-active');
    btnHamburger?.setAttribute('aria-expanded','true');
    btnHamburger?.setAttribute('aria-label','Close menu');
  };
  const closeSidebar = () => {
    sidebar.classList.remove('w3-on');
    overlay.classList.remove('w3-on');
    btnHamburger?.classList.remove('is-active');
    btnHamburger?.setAttribute('aria-expanded','false');
    btnHamburger?.setAttribute('aria-label','Open menu');
  };

  btnHamburger?.addEventListener('click', () => sidebar.classList.contains('w3-on') ? closeSidebar() : openSidebar());
  overlay?.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });

  // ===== Role label
  const roleLabelEl = document.getElementById('userRoleLabel');
  function titleizeRole(r){
    if (!r) return 'Admin';
    return r.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
  }
  const roleFromStorage = sessionStorage.getItem('role') || localStorage.getItem('role');
  if (roleLabelEl) roleLabelEl.textContent = titleizeRole(roleFromStorage) || 'Admin';

  // ===== Auth helpers
  function getBearerToken(){
    return sessionStorage.getItem('token') || localStorage.getItem('token') || null;
  }

  // ===== Submenus (bind once per element)
  function bindSubmenuToggles(root=document){
    root.querySelectorAll('.w3-toggle').forEach(tg => {
      if (tg.__bound) return;
      tg.__bound = true;

      tg.addEventListener('click', (e) => {
        e.preventDefault();
        const id = tg.dataset.target;
        const el = document.getElementById(id);
        if (!el) return;

        const open = el.classList.toggle('w3-open');
        tg.classList.toggle('w3-open', open);
        tg.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });
  }

  // ===== Active link + open parent
  function markActiveLinks(){
    const path = window.location.pathname.replace(/\/+$/, '');
    document.querySelectorAll('.w3-menu a[href]').forEach(a => {
      const href = a.getAttribute('href');
      if (href && href !== '#' && href.replace(/\/+$/, '') === path){
        a.classList.add('active');
        const sub = a.closest('.w3-submenu');
        if (sub){
          sub.classList.add('w3-open');
          const toggle = sub.previousElementSibling;
          toggle?.classList.add('w3-open');
          toggle?.setAttribute('aria-expanded','true');
        }
      }
    });
  }

  // ===== Dynamic menu builder
  const adminFullMenu = document.getElementById('adminFullMenu');
  const dynamicMenu = document.getElementById('dynamicMenu');

  function safeText(v){ return (v ?? '').toString(); }

  function iconHtml(iconClass, fallback='fa-solid fa-circle'){
    const cls = safeText(iconClass).trim();
    return `<i class="${cls || fallback}"></i>`;
  }

  function renderDynamicTree(tree){
    if (!dynamicMenu) return;
    dynamicMenu.innerHTML = '';

    (tree || []).forEach((header, hi) => {
      const hid = parseInt(header?.id || 0, 10);
      if (!hid) return;

      const headerName = safeText(header?.name || 'Menu');
      const headerIcon = header?.icon_class || 'fa-solid fa-folder';
      const subId = `dyn-sub-${hid}-${hi}`;

      const wrap = document.createElement('div');
      wrap.className = 'w3-group';

      wrap.innerHTML = `
        <a href="#" class="w3-link w3-toggle" data-target="${subId}" aria-expanded="false">
          ${iconHtml(headerIcon, 'fa-solid fa-folder')}<span>${headerName}</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="${subId}" class="w3-submenu" role="group" aria-label="${headerName} submenu"></div>
      `;

      const sub = wrap.querySelector(`#${subId}`);
      const pages = Array.isArray(header?.children) ? header.children : [];

      pages.forEach((p) => {
        const href = safeText(p?.href || '#');
        const name = safeText(p?.name || 'Page');
        const pIcon = safeText(p?.icon_class || '');

        const a = document.createElement('a');
        a.className = 'w3-link';
        a.href = href === '' ? '#' : href;

        // For pages: if API sends icon_class null, we keep just text (like your sample)
        a.innerHTML = pIcon ? `${iconHtml(pIcon)}<span>${name}</span>` : `<span>${name}</span>`;
        sub.appendChild(a);
      });

      if (sub.children.length) dynamicMenu.appendChild(wrap);
    });

    bindSubmenuToggles(dynamicMenu);
  }

  async function loadSidebarByToken(){
    const token = getBearerToken();
    const role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();

    // ✅ ADMIN: show full static menu
    if (role === 'admin') {
      if (adminFullMenu) adminFullMenu.style.display = '';
      if (dynamicMenu) dynamicMenu.style.display = 'none';
      if (adminFullMenu) bindSubmenuToggles(adminFullMenu);
      return;
    }

    // ✅ Normal users: require token, otherwise keep only static Overview + System
    if (!token) {
      if (adminFullMenu) adminFullMenu.style.display = 'none';
      if (dynamicMenu) dynamicMenu.style.display = 'none';
      return;
    }

    try{
      const res = await fetch('/api/my/sidebar-menus', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer ' + token,
          'Accept': 'application/json'
        }
      });

      if (!res.ok){
        if (adminFullMenu) adminFullMenu.style.display = 'none';
        if (dynamicMenu) dynamicMenu.style.display = 'none';
        return;
      }

      const data = await res.json();

      // ✅ If backend returns string "all" for admin-like access
      if (data === 'all' || data?.tree === 'all') {
        if (adminFullMenu) adminFullMenu.style.display = '';
        if (dynamicMenu) dynamicMenu.style.display = 'none';
        if (adminFullMenu) bindSubmenuToggles(adminFullMenu);
        return;
      }

      const tree = Array.isArray(data?.tree) ? data.tree : [];
      if (tree.length) {
        if (adminFullMenu) adminFullMenu.style.display = 'none';
        if (dynamicMenu) dynamicMenu.style.display = '';
        renderDynamicTree(tree);
      } else {
        if (adminFullMenu) adminFullMenu.style.display = 'none';
        if (dynamicMenu) dynamicMenu.style.display = 'none';
      }

    }catch(e){
      if (adminFullMenu) adminFullMenu.style.display = 'none';
      if (dynamicMenu) dynamicMenu.style.display = 'none';
    }
  }

  // ===== Logout (token-based)
  const API_LOGOUT = '/api/auth/logout';
  const LOGIN_PAGE = '/login';

  function clearAuthStorage(){
    try { sessionStorage.removeItem('token'); } catch(e){}
    try { sessionStorage.removeItem('role'); } catch(e){}
    try { localStorage.removeItem('token'); } catch(e){}
    try { localStorage.removeItem('role'); } catch(e){}
  }

  async function performLogout(){
    const token = getBearerToken();

    const confirm = await Swal.fire({
      title: 'Log out?',
      text: 'You will be signed out of MSIT Home Builder.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, logout',
      cancelButtonText: 'Cancel',
      focusCancel: true,
      confirmButtonColor: '#9E363A'
    });

    if (!confirm.isConfirmed) return;

    let ok = false;
    if (token){
      try{
        const res = await fetch(API_LOGOUT, {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
          },
          body: ''
        });
        ok = res.ok;
      }catch(e){ ok = false; }
    }

    clearAuthStorage();

    await Swal.fire({
      title: ok ? 'Logged out' : 'Signed out locally',
      text: ok ? 'See you soon 👋' : 'Your session was cleared on this device.',
      icon: ok ? 'success' : 'info',
      timer: 1200,
      showConfirmButton: false
    });

    window.location.replace(LOGIN_PAGE);
  }

  document.getElementById('logoutBtn')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });
  document.getElementById('logoutBtnSidebar')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });

  // ===== INIT (hide overlay only after theme + sidebar + active link is ready)
  (async () => {
    try{
      bindSubmenuToggles(document);     // static: system + overview toggles (if any)
      await loadSidebarByToken();       // dynamic/admin menu decision + build
      markActiveLinks();               // after menu is rendered
    } finally {
      hideLoading();                   // ✅ wrapper display none
    }
  })();
});
</script>

</body>
</html>
