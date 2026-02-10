{{-- resources/views/home.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  {{-- ✅ Server-side meta tags (SEO + share friendly) --}}
@include('landing.components.metaTags')

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ config('app.name','College Portal') }} — Home</title>

{{-- Bootstrap + Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

{{-- Common UI --}}
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/common/home.css') }}">

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
'hero' => url('/api/public/grand-homepage/hero-carousel'),
'noticeMarquee' => url('/api/public/grand-homepage/notice-marquee'),
'infoBoxes' => url('/api/public/grand-homepage/quick-links'),
'nvaRow' => url('/api/public/grand-homepage/notice-board'),

// Lazy (loads on scroll)
'stats' => url('/api/public/grand-homepage/stats'),
'achvRow' => url('/api/public/grand-homepage/activities'),
'placementNotices'=> url('/api/public/grand-homepage/placement-notices'),

'testimonials' => url('/api/public/grand-homepage/successful-entrepreneurs'),
'alumni' => url('/api/public/grand-homepage/alumni-speak'),
'success' => url('/api/public/grand-homepage/success-stories'),
'courses' => url('/api/public/grand-homepage/courses'),
];
@endphp

<style>
/* ... (all existing CSS remains the same, just adding new styles for popup header) ... */

/* ✅ NEW: Styles for popup header section */
.home-popup-header-section {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.popup-logo-container {
    flex: 0 0 52px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup-logo {
    width: 52px;
    height: 52px;
    object-fit: contain;
    display: block;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(158,54,58,.16), rgba(201,75,80,.10));
    border: 1px solid rgba(158,54,58,.18);
    padding: 6px;
}

.popup-header-text {
    flex: 1 1 auto;
    min-width: 0;
}

.popup-header-title {
    margin: 0;
    font-weight: 950;
    color: #0f172a;
    font-size: 16px;
    line-height: 1.15;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.popup-header-rotate {
    margin: 4px 0 0;
    color: var(--muted);
    font-weight: 800;
    font-size: 13px;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: opacity .18s ease, transform .10s ease, color .15s ease .10s;
}

.popup-header-rotate:hover {
    color: #0D29AC;
    cursor: pointer;
}

/* ✅ NEW: smooth fade when rotating */
.popup-header-rotate.is-fading{
    opacity: 0;
    transform: translateY(-2px);
}

/* ✅ NEW: Popup helper description text (below rotating line) */
.popup-header-desc{
    margin: 6px 0 0;
    color: var(--muted);
    font-weight: 650;
    font-size: 12.5px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ==========================================================
✅ UPDATED (ONLY): AUTO + MANUAL SCROLL FOR ALL LIST SECTIONS
- consistent slow/medium speed (JS based)
- NO GAP at the end (seamless loop)
- scrollbar always available for manual mouse scrolling
- pauses on hover/user interaction, resumes smoothly
========================================================== */
.nva-body,
.info-ul-viewport{
    max-height: 260px;
    overflow-y: auto;           /* ✅ manual scroll always available */
    overflow-x: hidden;
    position: relative;
    padding-right: 4px;
    scrollbar-gutter: stable;   /* ✅ prevents layout shift when scrollbar appears */
    overscroll-behavior: contain;
}

/* Custom scrollbar (same theme for both) */
.nva-body::-webkit-scrollbar,
.info-ul-viewport::-webkit-scrollbar {
    width: 6px;
}

.nva-body::-webkit-scrollbar-track,
.info-ul-viewport::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 4px;
    margin: 4px 0;
}

.nva-body::-webkit-scrollbar-thumb,
.info-ul-viewport::-webkit-scrollbar-thumb {
    background: #9E363A;
    border-radius: 4px;
    opacity: 0.5;
}

.nva-body::-webkit-scrollbar-thumb:hover,
.info-ul-viewport::-webkit-scrollbar-thumb:hover {
    background: #6B2528;
    opacity: 0.8;
}

/* Firefox scrollbar */
.nva-body,
.info-ul-viewport {
    scrollbar-width: thin;
    scrollbar-color: #9E363A #f8f9fa;
}

/* ✅ FIXED: Ensure nva-list items are properly spaced */
.nva-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nva-list li {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    transition: all 0.2s ease;
}

.nva-list li:last-child {
    border-bottom: none;
}

.nva-list li:hover {
    background: rgba(158, 54, 58, 0.05);
    border-radius: 6px;
    transform: translateX(2px);
}

.nva-list li i {
    color: #9E363A;
    font-size: 12px;
    margin-top: 2px;
    flex-shrink: 0;
}

.nva-list li span,
.nva-list li a {
    color: #333;
    font-size: 13.5px;
    line-height: 1.4;
    font-weight: 500;
    text-decoration: none;
    flex: 1;
    word-break: break-word;
}

.nva-list li a:hover {
    color: #9E363A;
    text-decoration: underline;
}

/* ... (rest of existing CSS remains unchanged) ... */
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
<div class="home-popup-header-section">
    <div class="popup-logo-container">
        <img id="popupHeaderLogo" class="popup-logo mh-skel" alt="College Logo" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2252%22%20height%3D%2252%22%20viewBox%3D%220%200%2052%2052%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20rx%3D%2212%22%20fill%3D%22%23f3f4f6%22%2F%3E%3Cpath%20d%3D%22M16%2016h20v20H16z%22%20fill%3D%22%239E363A%22%20opacity%3D%22.35%22%2F%3E%3C%2Fsvg%3E">
    </div>
    <div class="popup-header-text">
        <h3 class="popup-header-title mh-skel" id="popupHeaderTitle">Contact Us</h3>
        <p class="popup-header-rotate mh-skel" id="popupHeaderRotate"></p>

        {{-- ✅ NEW: helper text below rotating text --}}
        <p class="popup-header-desc" id="popupHeaderDesc">
            Have questions about admissions, courses, or campus life? Fill out the quick enquiry form and our team will get back to you with all the information you need. Let us help you take the next step in your academic career with confidence.
        </p>
    </div>
</div>

<button type="button" class="home-popup-close" aria-label="Close" data-home-popup-close="1">
<i class="fa-solid fa-xmark"></i>
</button>
</div>

<div class="home-popup-body">
{{-- Later: replace placeholder with your include --}}
@include('modules.enquiry.createEnquiry')
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
{{-- ✅ UPDATED (ONLY): when no data inserted, don't show hero-kicker / hero-title --}}
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
<div class="info-ul-viewport">
<ul id="careerList">
<li><i class="fa-solid fa-chevron-right"></i> <span>Loading…</span></li>
</ul>
</div>
</div>
</div>
<div class="col-lg-4 col-md-4">
<div class="info-box">
<h5><i class="fa-solid fa-star"></i> Why MSIT</h5>
<div class="info-ul-viewport">
<ul id="whyMsitList">
<li><i class="fa-solid fa-check"></i> <span>Loading…</span></li>
</ul>
</div>
</div>
</div>
<div class="col-lg-4 col-md-4">
<div class="info-box">
<h5><i class="fa-solid fa-award"></i> Scholarship</h5>
<div class="info-ul-viewport">
<ul id="scholarshipList">
<li><i class="fa-solid fa-gift"></i> <span>Loading…</span></li>
</ul>
</div>
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
<div class="info-ul-viewport">
<ul id="achievementList">
<li><i class="fa-solid fa-medal"></i> <span>Loading…</span></li>
</ul>
</div>
</div>
</div>
<div class="col-lg-4">
<div class="info-box">
<h5><i class="fa-solid fa-users"></i> Students Activity</h5>
<div class="info-ul-viewport">
<ul id="activityList">
<li><i class="fa-solid fa-calendar"></i> <span>Loading…</span></li>
</ul>
</div>
</div>
</div>
<div class="col-lg-4">
<div class="info-box">
<h5><i class="fa-solid fa-briefcase"></i> Placement Notice</h5>
<div class="info-ul-viewport">
<ul id="placementList2">
<li><i class="fa-solid fa-building"></i> <span>Loading…</span></li>
</ul>
</div>
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

{{-- ================= ✅ NEW: AICTE UG COURSES (LAZY) ================= --}}
<section class="ugc-section reveal" data-lazy-key="coursesUg">
<h2>AICTE UG Courses</h2>
<div class="row g-4" id="ugCoursesContainer">
<div class="col-lg-3 col-md-6">
<a class="ugc-card" href="#" aria-disabled="true">
<img id="ugCourseFallbackImage" alt="Course" class="ugc-img">
<div class="ugc-title">Loading…</div>
</a>
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
const deptParam = (PAGE_QS.get('department') || '').trim();
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
if(deptParam) qs.push('department=' + encodeURIComponent(deptParam));
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
bar: document.getElementById('pageLoaderBar'),
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
✅ NEW: Popup rotating text engine (AUTO rotate)
✅ FIXED: Handles rotating_text_json coming as string/array/object
========================= */
let __POPUP_ROTATE_TIMER = null;

function stopPopupRotate(){
try{
if(__POPUP_ROTATE_TIMER){
clearInterval(__POPUP_ROTATE_TIMER);
__POPUP_ROTATE_TIMER = null;
}
}catch(e){}
}

function normalizeRotateLines(raw){
if(raw == null) return [];

let v = raw;

// if backend sends JSON string like '["one","two"]'
if(typeof v === 'string'){
const s = v.trim();
if(!s) return [];
try{
const parsed = JSON.parse(s);
v = parsed;
}catch(e){
 // fallback: split by newline / pipe / comma
return s.split(/\r?\n|\||,/g).map(x => String(x||'').trim()).filter(Boolean);
}
}

// if object wrapper like {lines:[...]} or {items:[...]}
if(v && typeof v === 'object' && !Array.isArray(v)){
v = v.lines || v.items || v.texts || [];
}

if(!Array.isArray(v)) return [];

return v.map(x => String(x ?? '').trim()).filter(Boolean);
}

function startPopupRotate(lines, el, intervalMs = 2600){
stopPopupRotate();
if(!el) return;

const arr = Array.isArray(lines) ? lines : [];
if(!arr.length){
el.textContent = '';
return;
}

let idx = 0;
el.textContent = arr[0];

if(arr.length === 1){
return; // nothing to rotate
}

const ms = Math.max(1200, parseInt(intervalMs || 2600, 10) || 2600);

// ✅ click also rotates (kept), but ensure only one handler
el.onclick = null;
el.onclick = () => {
idx = (idx + 1) % arr.length;
el.textContent = arr[idx];
};

__POPUP_ROTATE_TIMER = setInterval(() => {
idx = (idx + 1) % arr.length;

el.classList.add('is-fading');
setTimeout(() => {
el.textContent = arr[idx];
el.classList.remove('is-fading');
}, 160);
}, ms);
}

/* =========================
✅ NEW: Function to load header data for popup
========================= */
async function loadHeaderDataForPopup() {
    try {
        const endpointBase = "{{ url('/api/header-components') }}";
        const qs = new URLSearchParams({
            per_page: '1',
            page: '1',
            sort: 'updated_at',
            direction: 'desc'
        });

        const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const res = await fetch(endpointBase.replace(/\/+$/,'') + '?' + qs.toString(), { headers });
        const js = await res.json().catch(() => ({}));

        const items = Array.isArray(js?.data) ? js.data : [];
        const item = items[0] || null;

        // Elements
        const popupLogo = document.getElementById('popupHeaderLogo');
        const popupTitle = document.getElementById('popupHeaderTitle');
        const popupRotate = document.getElementById('popupHeaderRotate');

        if (!res.ok || !item) {
            // Use defaults if no data
            if(popupTitle) popupTitle.textContent = 'Contact Us';
            if(popupRotate) popupRotate.textContent = '';
            stopPopupRotate();
            return;
        }

        // Set logo
        const logoUrl = item.primary_logo_full_url || item.primary_logo_url || '';
        if (popupLogo && logoUrl) {
            popupLogo.src = normalizeUrl(logoUrl);
            popupLogo.classList.remove('mh-skel');
        }

        // Set title
        const headerText = (item.header_text || 'Contact Us').toString().trim();
        if(popupTitle){
            popupTitle.textContent = headerText;
            popupTitle.classList.remove('mh-skel');
        }

        // ✅ FIXED: Rotating text auto rotation (not single line)
        const rotateLinesRaw = (item.rotating_text_json ?? item.rotating_text ?? item.rotating_lines ?? []);
        const rotateLines = normalizeRotateLines(rotateLinesRaw);

        if (popupRotate) {
            popupRotate.classList.remove('mh-skel');

            // if empty, keep it blank
            if (!rotateLines.length) {
                popupRotate.textContent = '';
                stopPopupRotate();
            } else {
                // Start auto-rotate
                startPopupRotate(rotateLines, popupRotate, item.rotating_text_interval_ms || 2600);
            }
        }

    } catch (error) {
        console.warn('Failed to load header data for popup:', error);
    }
}

function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
}

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
document.getElementById('ugCourseFallbackImage')?.setAttribute('src', PLACEHOLDERS.image);

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
url: (it.url ?? it.link ?? it.href ?? '').toString().trim(),
};
}
return { text:'', url:'' };
}).filter(x => x.text);

const loop = parseInt(settings.loop ?? 1, 10) === 1;

const logo = NOTICE_MARQUEE_GIF_SRC
? `<img class="nm-gif" src="${esc(NOTICE_MARQUEE_GIF_SRC)}" alt="" aria-hidden="true">`
: '';

const buildRunHtml = () => {
if(!items.length) return `<span class="nm-text">No notices available.</span>`;

const body = items.map((x) => {
const t = esc(x.text);
const u = x.url ? safeHref(x.url) : '';
const node = u
? `<a class="nm-link" href="${esc(u)}">${t}</a>`
: `<span class="nm-text">${t}</span>`;
return `${logo}${node}`;
}).join('');

return loop ? body : (body + logo);
};

const html = buildRunHtml();

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
const to = (dir === 'left') ? -distance : 0;

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

/* ==========================================================
✅ AUTO-SCROLL ENGINE (BOTTOM → TOP UPWARD) — EXTRA SLOW + SMOOTH
========================================================== */
const AUTO_SCROLL = (() => {
  const SPEED_PX_PER_SEC   = 1;     // ✅ SLOWER speed (was 4)
  const RESUME_DELAY_MS    = 1200;  // resume after manual interaction
  const MIN_ITEMS_FOR_AUTO = 7;

  const scrollers = new Set();
  let rafId = null;

  const prefersReduced = () =>
    window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function removeClones(ul){
    ul.querySelectorAll('[data-autoscroll-clone="1"]').forEach(n => n.remove());
  }

  function countOriginalItems(ul){
    return ul.querySelectorAll('li:not([data-autoscroll-clone="1"])').length;
  }

  function buildClones(ul){
    const originals = Array.from(ul.children)
      .filter(el => el.nodeType === 1 && el.getAttribute('data-autoscroll-clone') !== '1');

    originals.forEach(li => {
      const clone = li.cloneNode(true);
      clone.setAttribute('data-autoscroll-clone','1');
      clone.setAttribute('aria-hidden','true');
      ul.appendChild(clone);
    });
  }

  function destroy(viewport){
    const st = viewport?.__autoScrollState;
    if(!st) return;

    try{
      st._handlers.forEach(([evt, fn]) => st.viewport.removeEventListener(evt, fn));
    }catch(e){}

    delete viewport.__autoScrollState;
    scrollers.delete(st);
  }

  function ensure(viewport, ul){
    if(!viewport || !ul) return;

    destroy(viewport);

    if(prefersReduced()) return;

    viewport.style.overflowY = 'auto';
    ul.classList.remove('autoscroll', 'scrolling-upwards');

    removeClones(ul);

    const originalCount = countOriginalItems(ul);
    if(originalCount <= MIN_ITEMS_FOR_AUTO) return;

    const viewportH = viewport.clientHeight || 260;
    const originalHeight = ul.scrollHeight;

    if(originalHeight <= viewportH + 8) return;

    buildClones(ul);

    const st = {
      viewport,
      ul,
      originalHeight: Math.max(1, originalHeight),
      speed: SPEED_PX_PER_SEC,
      hovering: false,
      pausedUntil: 0,
      last: performance.now(),
      _handlers: []
    };

    const pause = (ms = RESUME_DELAY_MS) => {
      st.pausedUntil = performance.now() + ms;
    };

    const onEnter = () => { st.hovering = true; };
    const onLeave = () => { st.hovering = false; pause(RESUME_DELAY_MS); };
    const onWheel = () => pause(RESUME_DELAY_MS);
    const onPointerDown = () => pause(RESUME_DELAY_MS);
    const onTouchStart = () => pause(RESUME_DELAY_MS);
    const onKey = () => pause(RESUME_DELAY_MS);

    viewport.addEventListener('mouseenter', onEnter, { passive:true });
    viewport.addEventListener('mouseleave', onLeave, { passive:true });
    viewport.addEventListener('wheel', onWheel, { passive:true });
    viewport.addEventListener('pointerdown', onPointerDown, { passive:true });
    viewport.addEventListener('touchstart', onTouchStart, { passive:true });
    viewport.addEventListener('keydown', onKey, { passive:true });

    st._handlers.push(['mouseenter', onEnter]);
    st._handlers.push(['mouseleave', onLeave]);
    st._handlers.push(['wheel', onWheel]);
    st._handlers.push(['pointerdown', onPointerDown]);
    st._handlers.push(['touchstart', onTouchStart]);
    st._handlers.push(['keydown', onKey]);

    // ✅ START FROM BOTTOM OF ORIGINAL LIST (BOTTOM → TOP UPWARD)
    requestAnimationFrame(() => {
      const bottomOfOriginal = Math.max(0, st.originalHeight - viewport.clientHeight);
      viewport.scrollTop = bottomOfOriginal;
    });

    viewport.__autoScrollState = st;
    scrollers.add(st);

    startLoop();
  }

  function startLoop(){
    if(rafId != null) return;
    rafId = requestAnimationFrame(tick);
  }

  function tick(now){
    rafId = requestAnimationFrame(tick);

    if(!scrollers.size) return;

    scrollers.forEach(st => {
      const vp = st.viewport;
      const ul = st.ul;

      if(!vp || !ul || !document.body.contains(vp)){
        destroy(vp);
        return;
      }

      // ✅ smoother & consistent (prevents random speed spikes)
      let dt = now - st.last;
      if(dt < 0) dt = 0;
      if(dt > 50) dt = 50; // clamp large gaps
      st.last = now;

      if(st.hovering) return;
      if(now < st.pausedUntil) return;

      const delta = (st.speed * dt) / 1000;

      // ✅ BOTTOM → TOP (scroll UP)
      vp.scrollTop -= delta;

      // ✅ WRAP UPWARD seamlessly:
      // When reaching top, jump down by 1 originalHeight (keeps flow continuous)
      if(vp.scrollTop <= 1) {
        vp.scrollTop = st.originalHeight + vp.scrollTop;
      }
    });
  }

  function bindUl(ul){
    if(!ul) return;
    const viewport = ul.closest('.info-ul-viewport') || ul.closest('.nva-body');
    ensure(viewport, ul);
  }

  function refreshAll(){
    document.querySelectorAll('.info-ul-viewport ul, .nva-body ul').forEach(bindUl);
  }

  return { bindUl, refreshAll, destroy };
})();


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
const showDots = Number(settings.show_dots ?? 1) === 1;

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
const mobile = String(it.mobile_image_url ?? '').trim();
const alt = String(it.alt_text ?? '').trim();
const overlayHtml = safeInlineHtml(it.overlay_text ?? '');

const hasKicker = Boolean(alt);
const hasTitle = Boolean(String(overlayHtml || '').trim());

/* ✅ UPDATED (ONLY):
   - If no data inserted/null: don't render .hero-kicker / .hero-title
   - Dark overlay ("shadow") only if kicker/title exists */
const hasOverlay = hasKicker || hasTitle;

const bgStyle = (desktop || mobile)
? `background-image:url('${esc(desktop || mobile)}');`
: `background-image:linear-gradient(135deg, rgba(158,54,58,.95), rgba(107,37,40,.92));`;

return `
<div class="carousel-item ${i===0?'active':''}">
<div class="hero-slide ${hasOverlay ? 'has-overlay' : ''}"
data-hero-desktop="${esc(desktop)}"
data-hero-mobile="${esc(mobile)}"
style="${bgStyle}">
<div class="hero-inner">
${hasKicker ? `
<div class="hero-kicker">
<i class="fa-solid fa-graduation-cap"></i>
<span>${esc(alt)}</span>
</div>` : ``}
${hasTitle ? `<div class="hero-title">${overlayHtml}</div>` : ``}
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

// ✅ remove older clones from previous run
el.querySelectorAll('[data-autoscroll-clone="1"]').forEach(n => n.remove());

const arr = Array.isArray(items) ? items : [];
const max = Number(opts.max ?? 50);

if(!arr.length){
el.classList.remove('autoscroll', 'scrolling-upwards');
el.innerHTML = `<li><i class="${esc(iconClass)}"></i> <span>${esc(emptyText || 'No items available')}</span></li>`;

// ✅ refresh scroller state after rendering
setTimeout(() => AUTO_SCROLL.bindUl(el), 0);
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

// ✅ bind/refresh auto-scroll once DOM is ready
setTimeout(() => AUTO_SCROLL.bindUl(el), 60);
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
setTimeout(() => AUTO_SCROLL.bindUl(el), 80);
}
}, i * 45);
});
}

function renderCenterIframe(center){
const titleEl = document.getElementById('centerIframeTitle');
const videoEl = document.getElementById('mainVideoContainer');
const btnEl = document.getElementById('centerIframeButtons');

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
const rowEl = document.getElementById('statsRow');
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
const icon = it.icon_class ? String(it.icon_class) : 'fa-solid fa-chart-column';

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

const name = item.name || item.title || '—';
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
<span class="carousel-control-prev-icon"></span>
<span class="visually-hidden">Previous</span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#entrepreneursCarousel" data-bs-slide="next" style="${hasMulti ? '' : 'display:none'}">
<span class="carousel-control-next-icon"></span>
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
<span class="carousel-control-prev-icon"></span>
<span class="visually-hidden">Previous</span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#alumniCarousel" data-bs-slide="next" style="${hasMulti ? '' : 'display:none'}">
<span class="carousel-control-next-icon"></span>
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
const img = story.image_url || story.image || story.photo_url || PLACEHOLDERS.image;

const rawDesc = story.description || story.text || '';
const descHtml = normalizeRichText(rawDesc);

const name = story.name || story.title || '—';

/* ✅ UPDATED (ONLY): show department title instead of year */
const role = story.department_title || story.departmentTitle || story.department_name || story.role || story.subtitle || story.year || '';

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
const img = course.cover_image || course.image_url || course.image || PLACEHOLDERS.image;
const name = course.title || course.name || 'Course';
const desc = course.summary || course.blurb || course.description || '';

const baseUrl = safeHref(course.url || course.vision_link || course.dept_link || '#');

const links = Array.isArray(course.links) ? course.links : [
{ text: 'Vision & Mission', url: baseUrl },
{ text: 'PEO, PSO, PO', url: baseUrl },
{ text: 'Faculty', url: baseUrl },
{ text: 'Department', url: baseUrl },
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

/* ✅ NEW (ONLY): render UG courses (program_level === "ug") */
function renderUgCourses(arr){
const container = document.getElementById('ugCoursesContainer');
if(!container) return;

const items = Array.isArray(arr) ? arr : [];

const ug = items.filter(c => String(c?.program_level ?? '').toLowerCase() === 'ug');
if(!ug.length){
container.innerHTML = `<div class="col-12"><p class="muted-note" style="color:#fff;">No UG courses available.</p></div>`;
return;
}

container.innerHTML = ug.slice(0, 12).map(course => {
const img = course.cover_image || course.image_url || course.image || PLACEHOLDERS.image;
const title = course.title || course.name || 'UG Course';

const href = safeHref(course.url || (course.uuid ? `/courses/view/${course.uuid}` : '#'));
const uuid = String(course.uuid || '').trim();
const open = uuid ? `<a class="ugc-card" href="${esc(href)}">` : `<div class="ugc-card">`;
const close = uuid ? `</a>` : `</div>`;

return `
<div class="col-lg-3 col-md-6">
${open}
<img src="${esc(img)}" loading="lazy" alt="${esc(title)}" class="ugc-img">
<div class="ugc-title">${esc(title)}</div>
${close}
</div>
`;
}).join('');

container.querySelectorAll('img.ugc-img').forEach(img => attachImgFallback(img, 'image'));
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

setList('careerList', p.career_notices, 'fa-solid fa-chevron-right', 'No career notices.', { stagger:true, max: 60 });
setList('whyMsitList', p.why_us, 'fa-solid fa-check', 'No highlights.', { stagger:true, max: 60 });
setList('scholarshipList', p.scholarships, 'fa-solid fa-gift', 'No scholarships.', { stagger:true, max: 60 });
}catch(e){
console.warn(e);
showApiAlert(e);
}

LOADER.set(78, 'Loading notice board…');
try{
const p = await loadSection('nvaRow');
renderCenterIframe(p.center_iframe || p.centerIframe || p.center || null);

setList('noticeList', p.notices, 'fa-solid fa-caret-right', 'No notices.', { max: 80, stagger:true });
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
setList('achievementList', payload.achievements, 'fa-solid fa-medal', 'No achievements.', { stagger:true, max: 80 });
setList('activityList', payload.student_activities, 'fa-solid fa-calendar', 'No activities.', { stagger:true, max: 80 });

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
coursesUg: {
load: () => loadSection('courses'),
render: (payload) => renderUgCourses(payload.courses || payload.items || payload)
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
// Load header data for popup
loadHeaderDataForPopup().catch(() => {});

initRevealObservers();
await loadImmediateSections();
initLazySections();

// ✅ ensure auto-scroll is applied to any already-rendered lists
AUTO_SCROLL.refreshAll();

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
