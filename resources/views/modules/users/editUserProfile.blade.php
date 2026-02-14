{{-- resources/views/modules/user/editUserProfile.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit User Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

<style>:root{--surface-alt:#f1f5f9;--ink:#1e293b;--muted-color:#64748b;--line-strong:#e2e8f0;--line-light:#f1f5f9;--success:#10b981;--warning:#f59e0b;--danger:#ef4444;--shadow-1:0 1px 3px rgba(0,0,0,0.1);--shadow-2:0 4px 6px -1px rgba(0,0,0,0.1);--shadow-3:0 10px 15px -3px rgba(0,0,0,0.1);--shadow-sm:0 1px 2px 0 rgb(0 0 0 / 0.05);--shadow-md:0 4px 6px -1px rgb(0 0 0 / 0.1),0 2px 4px -2px rgb(0 0 0 / 0.1);--shadow-lg:0 10px 15px -3px rgb(0 0 0 / 0.1),0 4px 6px -4px rgb(0 0 0 / 0.1);--shadow-focus:0 0 0 4px var(--primary-light-transparent);--radius-sm:6px;--radius-md:10px;--radius-lg:16px;--radius-xl:24px}body{background-color:var(--bg-body);color:var(--ink);font-family:'Inter',system-ui,-apple-system,sans-serif;line-height:1.6;min-height:100vh;-webkit-font-smoothing:antialiased}.profile-layout{max-width:1400px;margin:0 auto;padding:30px;display:grid;grid-template-columns:340px 1fr;gap:40px;min-height:calc(100vh - 48px);position:relative}@media(max-width:1024px){.profile-layout{grid-template-columns:300px 1fr;gap:24px}}@media(max-width:992px){.profile-layout{grid-template-columns:1fr;padding:20px}}.profile-sidebar{background:var(--surface);border-radius:var(--radius-xl);padding:32px 24px;position:sticky;top:24px;height:fit-content;max-height:calc(100vh - 48px);overflow-y:auto;border:1px solid var(--line-strong);box-shadow:var(--shadow-lg);display:flex;flex-direction:column}.profile-sidebar::-webkit-scrollbar{width:6px}.profile-sidebar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}.profile-sidebar::-webkit-scrollbar-track{background:transparent}.profile-avatar-container{position:relative;width:120px;height:120px;margin:0 auto 16px}.profile-avatar{width:100%;height:100%;border-radius:50%;overflow:hidden;background:var(--surface-alt);display:flex;align-items:center;justify-content:center;font-size:40px;color:var(--primary-color);border:4px solid var(--surface);box-shadow:0 0 0 2px var(--line-strong);transition:transform 0.3s ease}.profile-avatar img{width:100%;height:100%;object-fit:cover}.profile-avatar:hover{transform:scale(1.02)}.profile-badge{position:absolute;bottom:0;right:0;background:var(--primary-color);color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;border:3px solid var(--surface);box-shadow:var(--shadow-sm)}.profile-name{font-weight:700;font-size:1.25rem;color:var(--ink);text-align:center;margin-bottom:4px;letter-spacing:-0.02em}.profile-role{font-size:0.75rem;font-weight:600;text-transform:uppercase;color:var(--primary-color);background:var(--primary-light);padding:4px 12px;border-radius:99px;display:table;margin:0 auto 24px;letter-spacing:0.05em}.profile-contact{background:var(--surface-alt);padding:20px;border-radius:var(--radius-lg);margin-bottom:24px;border:1px solid var(--line-light)}.contact-item{display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:0.9rem;color:var(--ink-light)}.contact-item:last-child{margin-bottom:0}.contact-item i{color:var(--muted-color);width:18px;text-align:center}.profile-nav{display:flex;flex-direction:column;gap:6px;margin-top:10px}.profile-nav button{border:none;background:transparent;text-align:left;padding:12px 16px;border-radius:var(--radius-md);color:var(--muted-color);font-weight:500;font-size:0.95rem;display:flex;align-items:center;gap:14px;transition:all 0.2s ease;cursor:pointer}.profile-nav button i{width:20px;text-align:center;transition:transform 0.2s}.profile-nav button:hover{background:var(--surface-alt);color:var(--ink)}.profile-nav button:hover i{transform:translateX(2px);color:var(--primary-color)}.profile-nav button.active{background:var(--primary-color);color:white;box-shadow:0 4px 12px var(--primary-light-transparent)}.profile-nav button.active i{color:white}.profile-social{display:flex;justify-content:center;gap:10px;margin-bottom:20px;flex-wrap:wrap}.profile-social a{width:36px;height:36px;border-radius:50%;background:var(--surface);border:1px solid var(--line-strong);display:flex;align-items:center;justify-content:center;color:var(--muted-color);transition:all 0.2s;font-size:14px;overflow:hidden}.profile-social a:hover{border-color:var(--primary-color);color:var(--primary-color);transform:translateY(-2px);box-shadow:var(--shadow-sm)}.profile-social a img{width:100%;height:100%;object-fit:cover;display:block}.profile-content{position:relative;min-height:600px}.content-topbar{position:sticky;top:24px;z-index:40;margin-bottom:24px;background:rgba(255,255,255,0.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-radius:var(--radius-lg);padding:16px 24px;border:1px solid rgba(255,255,255,0.4);box-shadow:var(--shadow-md);display:flex;align-items:center;justify-content:space-between}.content-topbar .title{font-weight:800;font-size:1.15rem;color:var(--ink);letter-spacing:-0.01em}.content-topbar .sub{font-size:0.85rem;color:var(--muted-color);font-weight:500}.profile-card{background:var(--surface);border-radius:var(--radius-xl);padding:40px;box-shadow:var(--shadow-sm);border:1px solid var(--line-strong);animation:slideUpFade 0.4s cubic-bezier(0.16,1,0.3,1)}@keyframes slideUpFade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}.profile-card h5{font-size:1.25rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:12px;margin-bottom:32px;padding-bottom:20px;border-bottom:1px solid var(--line-light)}.profile-card h5 i{color:var(--primary-color);background:var(--primary-light);width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}.form-label{font-size:0.85rem;font-weight:600;color:var(--ink-light);margin-bottom:8px}.form-control,.form-select{background-color:var(--surface-alt);border:1px solid transparent;border-radius:var(--radius-md)!important;padding:12px 16px;font-size:0.95rem;color:var(--ink);transition:all 0.2s ease}.form-control::placeholder{color:#94a3b8}.form-control:focus,.form-select:focus{background-color:var(--surface);border-color:var(--primary-color)!important;box-shadow:var(--shadow-focus)}.form-text{font-size:0.8rem;color:var(--muted-color);margin-top:6px}.editor-list{display:grid;gap:20px}.editor-row{background:#ffffff;border:1px solid var(--line-strong);border-radius:var(--radius-lg);padding:24px;transition:border-color 0.2s}.editor-row:hover{border-color:#cbd5e1}.editor-row .row-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px dashed var(--line-strong)}.editor-row .title{font-weight:600;font-size:0.95rem;color:var(--ink)}.editor-row .title .pill{font-size:0.7rem;font-weight:700;background:var(--surface-alt);color:var(--muted-color);padding:2px 8px;border-radius:6px;margin-left:8px;vertical-align:middle}.btn{padding:10px 20px;font-weight:500;border-radius:var(--radius-md);transition:all 0.2s;display:inline-flex;align-items:center;justify-content:center;gap:8px}.btn-primary{background-color:var(--primary-color);border-color:var(--primary-color);box-shadow:0 4px 6px rgba(79,70,229,0.2)}.btn-primary:hover{background-color:var(--primary-hover);border-color:var(--primary-hover);transform:translateY(-1px);box-shadow:0 6px 12px rgba(79,70,229,0.25)}.btn-light{background:white;border:1px solid var(--line-strong);color:var(--ink-light)}.btn-light:hover{background:var(--surface-alt);color:var(--ink);border-color:#cbd5e1}.btn-soft{background:var(--surface-alt);color:var(--ink);border:1px solid transparent}.btn-soft:hover{background:#e2e8f0;color:var(--ink)}.btn-danger-soft{background:var(--danger-bg);color:var(--danger);border:1px solid transparent}.btn-danger-soft:hover{background:#fee2e2}.loading-indicator{position:absolute;top:40%;left:50%;transform:translate(-50%,-50%);text-align:center;color:var(--muted-color)}.loading-spinner{width:48px;height:48px;border:4px solid var(--line-light);border-top-color:var(--primary-color);border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 20px}@keyframes spin{100%{transform:rotate(360deg)}}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.7);backdrop-filter:blur(4px);z-index:9999;display:flex;justify-content:center;align-items:center}.toast{border-radius:12px;box-shadow:var(--shadow-lg);font-weight:500;border:none}.scroll-hint{position:absolute;bottom:20px;left:0;right:0;display:flex;justify-content:center;pointer-events:none;opacity:0;transition:opacity 0.3s}.profile-sidebar:hover .scroll-hint{opacity:1}.scroll-hint .hint-pill{background:rgba(0,0,0,0.6);color:white;padding:6px 14px;border-radius:20px;font-size:12px;backdrop-filter:blur(4px)}.icon-preview-pill{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--line-strong);background:var(--surface-alt);border-radius:14px}.icon-preview-pill .box{width:36px;height:36px;border-radius:10px;background:#fff;border:1px solid var(--line-strong);display:flex;align-items:center;justify-content:center;overflow:hidden}.icon-preview-pill .box img{width:100%;height:100%;object-fit:cover}.icon-preview-pill .meta{font-size:.82rem;color:var(--muted-color);line-height:1.2}.tags-box{border:1px solid var(--line-strong);border-radius:14px;padding:10px 10px;background:color-mix(in oklab,var(--surface) 92%,transparent)}.tag-input-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.tag-input{flex:1;min-width:240px}.tags{margin-top:10px;display:flex;flex-wrap:wrap;gap:8px}.tag{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid var(--line-soft);background:color-mix(in oklab,var(--primary-color) 10%,transparent);color:var(--ink);font-size:12.5px}.tag .x{border:0;background:transparent;color:var(--muted-color);cursor:pointer;padding:0 2px}.tag .x:hover{color:var(--danger-color)}.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}.rte-row{margin-bottom:16px}.rte-wrap{border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;background:var(--surface)}.rte-toolbar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:8px;border-bottom:1px solid var(--line-strong);background:color-mix(in oklab,var(--surface) 92%,transparent)}.rte-btn{border:1px solid var(--line-soft);background:transparent;color:var(--ink);padding:7px 9px;border-radius:10px;line-height:1;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;user-select:none}.rte-btn:hover{background:var(--page-hover)}.rte-btn.active{background:color-mix(in oklab,var(--primary-color) 14%,transparent);border-color:color-mix(in oklab,var(--primary-color) 35%,var(--line-soft))}.rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}.rte-tabs{margin-left:auto;display:flex;border:1px solid var(--line-soft);border-radius:0;overflow:hidden}.rte-tabs .tab{border:0;border-right:1px solid var(--line-soft);border-radius:0;padding:7px 12px;font-size:12px;cursor:pointer;background:transparent;color:var(--ink);line-height:1;user-select:none}.rte-tabs .tab:last-child{border-right:0}.rte-tabs .tab.active{background:color-mix(in oklab,var(--primary-color) 12%,transparent);font-weight:700}.rte-area{position:relative}.rte-editor{min-height:180px;padding:12px 12px;outline:none}.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}.rte-editor b,.rte-editor strong{font-weight:800}.rte-editor i,.rte-editor em{font-style:italic}.rte-editor u{text-decoration:underline}.rte-editor h1{font-size:20px;margin:8px 0}.rte-editor h2{font-size:18px;margin:8px 0}.rte-editor h3{font-size:16px;margin:8px 0}.rte-editor ul,.rte-editor ol{padding-left:22px}.rte-editor p{margin:0 0 10px}.rte-editor a{color:var(--primary-color);text-decoration:underline}.rte-editor code{padding:2px 6px;border-radius:0;background:color-mix(in oklab,var(--muted-color) 14%,transparent);border:1px solid var(--line-soft);font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:12.5px}.rte-editor pre{padding:10px 12px;border-radius:0;background:color-mix(in oklab,var(--muted-color) 10%,transparent);border:1px solid var(--line-soft);overflow:auto;margin:8px 0}.rte-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre}.rte-code{display:none;width:100%;min-height:180px;padding:12px 12px;border:0;outline:none;resize:vertical;background:transparent;color:var(--ink);font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:12.5px;line-height:1.45}.rte-wrap.mode-code .rte-editor{display:none}.rte-wrap.mode-code .rte-code{display:block}</style>

</head>

<body>

{{-- Global Loading Overlay --}}
<div id="globalLoading" class="loading-overlay" style="display:none;">
  <div class="loading-spinner"></div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-4" style="z-index:1080">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body px-4 py-3" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body px-4 py-3" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<div class="profile-layout">

  <aside class="profile-sidebar" id="profileSidebar">
    <div class="profile-avatar-container">
      <div class="profile-avatar" id="avatar">
        <i class="fa fa-user-graduate"></i>
      </div>
      <div class="profile-badge">
        <i class="fa fa-pen"></i>
      </div>
    </div>

    <div class="profile-name" id="name">...</div>
    <div class="profile-role" id="role">...</div>

    <div class="profile-contact">
      <div class="contact-item">
        <i class="fa fa-envelope"></i>
        <span id="email" class="text-truncate">...</span>
      </div>
      <div class="contact-item">
        <i class="fa fa-phone"></i>
        <span id="phone">...</span>
      </div>
      <div class="contact-item">
        <i class="fa fa-map-marker-alt"></i>
        <span id="address">...</span>
      </div>
    </div>

    <div class="profile-social" id="socialIcons"></div>

    <div class="d-grid gap-2 mb-4">
      <button id="btnSaveAllSidebar" class="btn btn-primary" type="button">
        <i class="fa fa-check-circle"></i> Save All Changes
      </button>
      <a href="/user/manage" class="btn btn-light" data-manage-link="1">
        <i class="fa fa-arrow-left"></i> Back to List
      </a>
    </div>

    <div class="profile-nav" id="profileNav">
      <button class="active" data-section="basic">
        <i class="fa fa-user"></i> <span>Basic Details</span>
      </button>
      <button data-section="personal">
        <i class="fa fa-id-card"></i> <span>Personal Info</span>
      </button>
      <button data-section="social">
        <i class="fa fa-share-nodes"></i> <span>Social Links</span>
      </button>
      <button data-section="education">
        <i class="fa fa-graduation-cap"></i> <span>Education</span>
      </button>
      <button data-section="honors">
        <i class="fa fa-award"></i> <span>Honors & Awards</span>
      </button>
      <button data-section="journals">
        <i class="fa fa-book"></i> <span>Journal Publications</span>
      </button>
      <button data-section="conferences">
        <i class="fa fa-microphone"></i> <span>Conferences</span>
      </button>
      <button data-section="teaching">
        <i class="fa fa-chalkboard-teacher"></i> <span>Teaching</span>
      </button>
    </div>

    <div class="scroll-hint" id="scrollHint" aria-hidden="true">
      <div class="hint-pill">Scroll for more <i class="fa fa-arrow-down ms-1"></i></div>
    </div>
  </aside>

  <main class="profile-content" id="contentArea">

    <div class="content-topbar">
      <div>
        <div class="title">Edit Profile</div>
        <div class="sub" id="topbarSub">Loading user...</div>
      </div>
      <div class="d-flex gap-2">
        <button id="btnSaveAllTop" class="btn btn-primary" type="button">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </div>

    <div class="loading-indicator" id="loadingIndicator">
      <div class="loading-spinner"></div>
      <div>Fetching profile data...</div>
    </div>

    <div id="dynamicContent"></div>
  </main>

</div>

<div class="section-indicator" id="sectionIndicator" style="display:none; position:fixed; bottom:20px; right:20px; background:var(--ink); color:white; padding:10px 20px; border-radius:30px; z-index:100; font-size:0.85rem; box-shadow:var(--shadow-lg);">
  Viewing: <span id="currentSectionName">Basic Details</span>
</div>

{{-- ✅ Removed extra Bootstrap modal for image upload.
     Now every image/file field uses the native file chooser and uploads directly. --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* =========================
   Editable Profile Page Logic
========================= */

const state = {
  uuid: null,
  token: '',
  profile: null,
  departments: [],
  departmentsLoaded: false,
  currentSection: 'basic',
  isLoading: false,

  // ✅ Personal Info enhancements
  personalQualification: [],
  personalRTE: {},            // { key: { wrap, editor, code, mode } }
  personalSavedRange: {},     // selection memory per key
  activePersonalRTE: null
};

const sections = {
  basic: { title:'Basic Details', icon:'fa-user', render: renderBasicSection },
  personal: { title:'Personal Information', icon:'fa-id-card', render: renderPersonalSection },
  social: { title:'Social Links', icon:'fa-share-nodes', render: renderSocialSection },
  education: { title:'Education', icon:'fa-graduation-cap', render: renderEducationSection },
  honors: { title:'Honors & Awards', icon:'fa-award', render: renderHonorsSection },
  journals: { title:'Journal Publications', icon:'fa-book', render: renderJournalsSection },
  conferences: { title:'Conference Publications', icon:'fa-microphone', render: renderConferencesSection },
  teaching: { title:'Teaching Engagements', icon:'fa-chalkboard-teacher', render: renderTeachingSection }
};

const PERSONAL_RTE_KEYS = ['affiliation','specification','experience','interest','administration','research_project'];

function $(id){ return document.getElementById(id); }

function showGlobalLoading(show){
  const el = $('globalLoading');
  if (!el) return;
  el.style.display = show ? 'flex' : 'none';
}

function showLoading(show){
  const li = $('loadingIndicator');
  const dc = $('dynamicContent');
  if (!li || !dc) return;
  li.style.display = show ? 'block' : 'none';
  dc.style.display = show ? 'none' : 'block';
}

function escapeHtml(str){
  return (str ?? '').toString().replace(/[&<>"']/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]));
}
function escapeAttr(str){ return escapeHtml(str); }

function ok(msg){
  $('toastSuccessText').textContent = msg || 'Done';
  bootstrap.Toast.getOrCreateInstance($('toastSuccess')).show();
}
function err(msg){
  $('toastErrorText').textContent = msg || 'Something went wrong';
  bootstrap.Toast.getOrCreateInstance($('toastError')).show();
}

function authHeaders(extra = {}){
  return Object.assign({
    'Authorization': 'Bearer ' + state.token,
    'Accept': 'application/json'
  }, extra);
}

function syncTokenAcrossStorages(token){
  try{
    if (!token) return;
    sessionStorage.setItem('token', token);
    localStorage.setItem('token', token);
  }catch(e){}
}

function handleAuthStatus(res){
  if (res.status === 401){
    window.location.href = '/';
    return true;
  }
  return false;
}

function parseUuidFromPath(){
  const parts = window.location.pathname.split('/').filter(Boolean);
  return parts[parts.length - 1] || null;
}

function getManageUsersUrl(){
  const parts = (window.location.pathname || '').split('/').filter(Boolean);
  const rolePrefix = ['admin','examiner','student'].includes(parts[0]) ? parts[0] : null;
  if (rolePrefix) return `/${rolePrefix}/user/manage`;
  if (parts[0] === 'user') return '/user/manage';
  return '/user/manage';
}

function applyManageLinks(){
  const url = getManageUsersUrl();
  document.querySelectorAll('a[data-manage-link]').forEach(a => a.setAttribute('href', url));
}

/* =========================
   ✅ Native File Chooser + Direct Upload (NO modals)
   ✅ FIXED: Upload now uses SAME API as Media Manager:
       POST /api/media  (FormData { file })
========================= */
const MEDIA_UPLOAD_ENDPOINT = '/api/media';

function isProbablyImagePath(v){
  const s = (v || '').toString().trim().toLowerCase();
  if (!s) return false;
  if (s.startsWith('data:image/')) return true;
  if (s.startsWith('http://') || s.startsWith('https://')) return true;
  if (s.includes('/')) return true;
  return (/\.(png|jpg|jpeg|webp|gif|svg)$/i).test(s);
}
function isProbablyPdf(v){
  const s = (v || '').toString().trim().toLowerCase();
  return s.endsWith('.pdf');
}
function isProbablyFAClass(v){
  const s = (v || '').toString().trim();
  if (!s) return false;
  if (s.includes('fa-')) return true;
  if (s.startsWith('fa ') || s.startsWith('fa-') || s.startsWith('fa-solid') || s.startsWith('fa-brands')) return true;
  return false;
}

function pickUploadUrlFromResponse(js){
  if (!js) return '';

  // direct fields
  const direct = js.url || js.path || js.location || js.file_url || js.fileUrl || js.source_url || js.sourceUrl;
  if (direct) return String(direct);

  // media manager typical: { status:'success', data:{ id, url, ... } }
  const d = js.data ?? js.result ?? js.payload ?? null;

  if (Array.isArray(d) && d[0]) {
    const vArr = d[0].url || d[0].path || d[0].location || d[0].file_url || d[0].fileUrl || d[0].source_url || d[0].sourceUrl;
    if (vArr) return String(vArr);
  }

  if (d && typeof d === 'object'){
    const v = d.url || d.path || d.location || d.file_url || d.fileUrl || d.source_url || d.sourceUrl || d.full_url || d.fullUrl;
    if (v) return String(v);

    const w = d.attachment || d.media || d.item || d.file || null;
    if (w){
      const v2 = w.url || w.source_url || w.sourceUrl || w.path || w.guid || (w.guid && w.guid.rendered);
      if (v2) return String(v2);
    }
  }

  return '';
}

// ✅ Exact same response handling style as Media Manager (JSON OR text)
async function uploadFileToMedia(file){
  const fd = new FormData();
  fd.append('file', file); // IMPORTANT: Media Manager uses only "file"

  const res = await fetch(MEDIA_UPLOAD_ENDPOINT, {
    method: 'POST',
    headers: { ...authHeaders() }, // keep Authorization + Accept; don't set Content-Type
    body: fd
  });

  if (handleAuthStatus(res)) return '';

  const ct = (res.headers.get('content-type') || '').toLowerCase();
  const body = ct.includes('application/json')
    ? await res.json().catch(() => ({}))
    : await res.text();

  if (!res.ok){
    const msg =
      (typeof body === 'object' && body && (body.message || body.error)) ? (body.message || body.error) :
      (typeof body === 'string' && body) ? body :
      `Upload failed (${res.status})`;
    throw new Error(msg);
  }

  // validate "success" like Media Manager
  if (typeof body === 'object' && body){
    const ok = (body.status === 'success') || (body.success === true);
    if (!ok){
      throw new Error(body.message || body.error || 'Upload failed');
    }
    const url = pickUploadUrlFromResponse(body);
    if (!url) throw new Error('Upload succeeded but no URL/path returned');
    return url;
  }

  throw new Error('Unexpected upload response');
}

// kept name to avoid touching other logic
async function uploadFileToAnyEndpoint(file){
  return uploadFileToMedia(file);
}

function setInputValueAndTrigger(inp, val){
  if (!inp) return;
  inp.value = val || '';
  try{ inp.dispatchEvent(new Event('input', { bubbles:true })); }catch(e){}
  try{ inp.dispatchEvent(new Event('change', { bubbles:true })); }catch(e){}
}

function resolveTargetInputFromFileInput(fileInput){
  const directSel = fileInput.getAttribute('data-target-input');
  if (directSel){
    const t = document.querySelector(directSel);
    return t || null;
  }

  const field = fileInput.getAttribute('data-target-field');
  if (field){
    const row = fileInput.closest('.editor-row');
    if (row){
      return row.querySelector(`[data-field="${field}"]`);
    }
    return document.querySelector(`[data-field="${field}"]`);
  }

  const wrap = fileInput.closest('.input-group') || fileInput.closest('.mb-3') || fileInput.parentElement;
  if (wrap){
    const cand = wrap.querySelector('[data-field]');
    if (cand) return cand;
  }
  return null;
}

function resolveEchoInput(fileInput){
  const echoSel = fileInput.getAttribute('data-echo-input');
  return echoSel ? (document.querySelector(echoSel) || null) : null;
}

/* =========================
   ✅ Personal Info: Tags helpers
========================= */
function sanitizeTag(s){ return (s ?? '').toString().replace(/\s+/g,' ').trim(); }
function uniqLower(arr){
  const seen = new Set();
  const out = [];
  for (const x of (arr || [])){
    const t = sanitizeTag(x);
    const key = (t || '').toLowerCase();
    if (!key || seen.has(key)) continue;
    seen.add(key);
    out.push(t);
  }
  return out;
}
function renderPersonalTags(){
  const wrap = $('pf_qualTags');
  if (!wrap) return;
  wrap.innerHTML = '';

  if (!state.personalQualification.length){
    wrap.innerHTML = '<span class="text-muted small">No qualifications added.</span>';
    return;
  }

  state.personalQualification.forEach((t, idx) => {
    const span = document.createElement('span');
    span.className = 'tag';
    span.innerHTML = `
      <span>${escapeHtml(t)}</span>
      <button type="button" class="x" title="Remove" data-qual-idx="${idx}"><i class="fa fa-xmark"></i></button>
    `;
    wrap.appendChild(span);
  });
}
function initPersonalTagsFromProfile(){
  const d = (state.profile?.personal || {});
  let q = d.qualification ?? d.qualifications ?? [];
  if (typeof q === 'string'){
    try{
      const parsed = JSON.parse(q);
      q = parsed;
    }catch(_e){
      q = q.split(',').map(s=>s.trim()).filter(Boolean);
    }
  }
  state.personalQualification = Array.isArray(q) ? uniqLower(q) : [];
  renderPersonalTags();
}
function addPersonalTag(raw){
  const t = sanitizeTag(raw);
  if (!t) return;
  state.personalQualification = uniqLower([...(state.personalQualification || []), t]);
  renderPersonalTags();

  const inp = $('pf_qualInput');
  if (inp){
    inp.value = '';
    try{ inp.focus({ preventScroll:true }); } catch(_e){ try{ inp.focus(); } catch(__){} }
  }
}

/* =========================
   ✅ Personal Info: RTE helpers (FIXED)
========================= */
function htmlOrEmpty(v){
  const s = (v ?? '').toString().trim();
  return s ? s : '';
}
function ensureWrappedInPreCode(html){
  return (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner)=>{
    if(/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
    return `<pre><code>${inner}</code></pre>`;
  });
}
function safeFocus(el){
  if(!el) return;
  try{ el.focus({ preventScroll:true }); }
  catch(_){ try{ el.focus(); }catch(__){} }
}
function selectionInside(el){
  const sel = window.getSelection();
  if(!sel || sel.rangeCount === 0) return false;
  const r = sel.getRangeAt(0);
  return !!(el && r && el.contains(r.commonAncestorContainer));
}
function placeCaretAtEnd(el){
  if(!el) return;
  safeFocus(el);
  const range = document.createRange();
  range.selectNodeContents(el);
  range.collapse(false);
  const sel = window.getSelection();
  if(sel){
    sel.removeAllRanges();
    sel.addRange(range);
  }
}
function normalizePreInEditor(editor){
  if(!editor) return;
  editor.querySelectorAll('pre').forEach(pre=>{
    if(pre.querySelector('code')) return;
    const code = document.createElement('code');
    while(pre.firstChild) code.appendChild(pre.firstChild);
    pre.appendChild(code);
  });
}

function saveSelectionFor(key){
  const o = state.personalRTE[key];
  if(!o || o.mode !== 'text') return;
  const sel = window.getSelection();
  if(!sel || sel.rangeCount === 0) return;

  const range = sel.getRangeAt(0);
  if(!o.editor.contains(range.commonAncestorContainer)) return;

  try{
    state.personalSavedRange[key] = range.cloneRange();
  }catch(_){}
}

function restoreSelectionFor(key){
  const o = state.personalRTE[key];
  if(!o || o.mode !== 'text') return false;
  const saved = state.personalSavedRange[key];
  if(!saved) return false;

  const sel = window.getSelection();
  if(!sel) return false;

  try{
    safeFocus(o.editor);
    sel.removeAllRanges();
    sel.addRange(saved);
    return true;
  }catch(_e){
    return false;
  }
}

function syncCodeFromEditor(key){
  const o = state.personalRTE[key];
  if(!o) return;
  o.code.value = ensureWrappedInPreCode(o.editor.innerHTML || '');
}

function updateToolbarActive(key){
  const o = state.personalRTE[key];
  if(!o || o.mode !== 'text') return;

  const tb = o.wrap.querySelector('.rte-toolbar');
  if(!tb) return;

  const setActive = (cmd, on)=>{
    const b = tb.querySelector(`.rte-btn[data-cmd="${cmd}"]`);
    if(b) b.classList.toggle('active', !!on);
  };

  if(!selectionInside(o.editor)){
    setActive('bold', false);
    setActive('italic', false);
    setActive('underline', false);
    setActive('insertUnorderedList', false);
    setActive('insertOrderedList', false);
    return;
  }

  try{
    setActive('bold', document.queryCommandState('bold'));
    setActive('italic', document.queryCommandState('italic'));
    setActive('underline', document.queryCommandState('underline'));
    setActive('insertUnorderedList', document.queryCommandState('insertUnorderedList'));
    setActive('insertOrderedList', document.queryCommandState('insertOrderedList'));
  }catch(_){}
}

function runRTECommand(key, cmd, val=null){
  const o = state.personalRTE[key];
  if(!o || o.mode !== 'text') return;

  const restored = restoreSelectionFor(key);
  if(!restored){
    safeFocus(o.editor);
    if(!selectionInside(o.editor)) placeCaretAtEnd(o.editor);
  }

  try{
    if(cmd === 'formatBlock'){
      const v = (val ?? '').toString().trim();
      const fmt = v ? (v.startsWith('<') ? v : `<${v}>`) : null;
      document.execCommand('formatBlock', false, fmt);
    }
    else if(cmd === 'insertHTML'){
      document.execCommand('insertHTML', false, (val ?? '').toString());
    }
    else{
      document.execCommand(cmd, false, val);
    }
  }catch(ex){
    console.error('execCommand failed:', cmd, ex);
  }

  syncCodeFromEditor(key);
  saveSelectionFor(key);
  setTimeout(()=> updateToolbarActive(key), 0);
}

function setPersonalRTEMode(key, mode, opts = {}){
  const o = state.personalRTE[key];
  if(!o) return;

  const focus = (opts.focus ?? true);
  const nextMode = (mode === 'code') ? 'code' : 'text';

  if(nextMode === o.mode){
    if(focus){
      if(o.mode === 'code') safeFocus(o.code);
      else safeFocus(o.editor);
    }
    return;
  }

  if(o.mode === 'text') saveSelectionFor(key);

  o.mode = nextMode;
  o.wrap.classList.toggle('mode-code', o.mode === 'code');

  o.wrap.querySelectorAll('.rte-tabs .tab').forEach(t=>{
    t.classList.toggle('active', t.dataset.mode === o.mode);
  });

  o.wrap.querySelectorAll('.rte-toolbar .rte-btn').forEach(btn=>{
    btn.disabled = (o.mode === 'code');
    btn.style.opacity = (o.mode === 'code') ? '0.55' : '';
    btn.style.pointerEvents = (o.mode === 'code') ? 'none' : '';
  });

  if(o.mode === 'code'){
    syncCodeFromEditor(key);
    if(focus) setTimeout(()=> safeFocus(o.code), 0);
  }else{
    o.editor.innerHTML = ensureWrappedInPreCode(o.code.value || '');
    normalizePreInEditor(o.editor);

    if(focus) setTimeout(()=>{
      placeCaretAtEnd(o.editor);
      saveSelectionFor(key);
      updateToolbarActive(key);
    }, 0);
  }
}

function registerPersonalRTE(key, initialHtml){
  const wrap = document.getElementById('pi_'+key+'Wrap');
  const editor = document.getElementById('pi_'+key+'Editor');
  const code = document.getElementById('pi_'+key+'Code');
  if(!wrap || !editor || !code) return;

  state.personalRTE[key] = { wrap, editor, code, mode:'text' };

  editor.addEventListener('focus', ()=> { state.activePersonalRTE = key; });

  ['click','mouseup','keyup','input'].forEach(ev=>{
    editor.addEventListener(ev, ()=>{
      saveSelectionFor(key);
      updateToolbarActive(key);
    });
  });
  editor.addEventListener('blur', ()=> saveSelectionFor(key));

  editor.innerHTML = ensureWrappedInPreCode(htmlOrEmpty(initialHtml));
  normalizePreInEditor(editor);
  code.value = ensureWrappedInPreCode(editor.innerHTML || '');

  setPersonalRTEMode(key, 'text', { focus:false });
  updateToolbarActive(key);

  editor.addEventListener('input', ()=> {
    if(state.personalRTE[key]?.mode === 'text') syncCodeFromEditor(key);
  });
}

function initPersonalRTEFromProfile(){
  state.personalRTE = {};
  state.personalSavedRange = {};
  state.activePersonalRTE = null;

  const d = (state.profile?.personal || {});
  PERSONAL_RTE_KEYS.forEach(k => registerPersonalRTE(k, d?.[k]));
}

function getPersonalRTEHtml(key){
  const o = state.personalRTE[key];
  if(!o) return '';
  const html = (o.mode === 'code') ? (o.code.value || '') : (o.editor.innerHTML || '');
  return (ensureWrappedInPreCode(html) || '').trim();
}

/* ===== Sidebar init ===== */
function initSidebar(){
  const d = (state.profile?.basic || {});
  $('name').textContent = d.name || 'No Name';
  $('role').textContent = ((d.role || 'User').toUpperCase());
  $('email').textContent = d.email || '—';
  $('phone').textContent = d.phone_number || d.phone || '—';
  $('address').textContent = (d.address ? String(d.address).replace(/\n/g, ', ') : '—');

  const avatar = $('avatar');
  if (d.image){
    avatar.innerHTML = `<img src="${escapeAttr(d.image)}" alt="avatar">`;
  } else {
    avatar.innerHTML = `<i class="fa fa-user-graduate"></i>`;
  }

  renderSocialIcons(state.profile?.social_media || []);
  $('topbarSub').textContent = state.uuid ? `UUID: ${state.uuid}` : '—';
}

function renderSocialIcons(arr){
  const socialIconsMap = {
    'linkedin': 'fa-brands fa-linkedin',
    'github': 'fa-brands fa-github',
    'orcid': 'fa-brands fa-orcid',
    'google scholar': 'fa fa-graduation-cap',
    'researchgate': 'fa-brands fa-researchgate',
    'twitter': 'fa-brands fa-twitter',
    'x': 'fa-brands fa-x-twitter',
    'facebook': 'fa-brands fa-facebook',
    'instagram': 'fa-brands fa-instagram',
    'youtube': 'fa-brands fa-youtube',
    'website': 'fa fa-globe'
  };

  const el = $('socialIcons');
  if (!el) return;
  el.innerHTML = '';

  (arr || [])
    .filter(s => {
      const a = s?.active;
      if (a === undefined || a === null || a === '') return true;
      const v = String(a).toLowerCase();
      return (v === '1' || v === 'true' || v === 'yes');
    })
    .sort((a,b) => {
      const sa = Number(a?.sort_order ?? 0);
      const sb = Number(b?.sort_order ?? 0);
      if (Number.isFinite(sa) && Number.isFinite(sb) && sa !== sb) return sa - sb;
      return 0;
    })
    .forEach(s => {
      const platform = (s?.platform || '').toLowerCase();
      const customIcon = (s?.icon || '').toString().trim();
      if (!s?.link) return;

      if (customIcon && isProbablyImagePath(customIcon) && !isProbablyPdf(customIcon)){
        el.insertAdjacentHTML('beforeend', `
          <a href="${escapeAttr(s.link)}" target="_blank" title="${escapeAttr(s.platform || 'Link')}" rel="noopener noreferrer">
            <img src="${escapeAttr(customIcon)}" alt="${escapeAttr(s.platform || 'icon')}">
          </a>
        `);
        return;
      }

      const iconClass = (customIcon && isProbablyFAClass(customIcon))
        ? customIcon
        : (socialIconsMap[platform] || 'fa fa-link');

      el.insertAdjacentHTML('beforeend', `
        <a href="${escapeAttr(s.link)}" target="_blank" title="${escapeAttr(s.platform || 'Link')}" rel="noopener noreferrer">
          <i class="${escapeAttr(iconClass)}"></i>
        </a>
      `);
    });
}

function setupSidebarScrollHint(){
  const sidebar = $('profileSidebar');
  const hint = $('scrollHint');
  if(!sidebar || !hint) return;

  const canScroll = () => sidebar.scrollHeight > sidebar.clientHeight + 2;

  const updateHint = () => {
    if(!canScroll()){
      hint.style.display = 'none';
      return;
    }
    const atBottom = (sidebar.scrollTop + sidebar.clientHeight) >= (sidebar.scrollHeight - 4);
    hint.style.display = atBottom ? 'none' : 'flex';
  };

  requestAnimationFrame(updateHint);
  setTimeout(updateHint, 250);

  sidebar.addEventListener('scroll', updateHint, { passive:true });
  window.addEventListener('resize', updateHint);

  const mo = new MutationObserver(() => setTimeout(updateHint, 60));
  mo.observe(sidebar, { childList:true, subtree:true });

  sidebar.querySelectorAll('img').forEach(img => img.addEventListener('load', updateHint));
}

/* ===== Navigation ===== */
function setupNavigation(){
  document.querySelectorAll('.profile-nav button[data-section]').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (state.isLoading) return;
      const sectionId = btn.dataset.section;
      if (!sections[sectionId] || sectionId === state.currentSection) return;

      document.querySelectorAll('.profile-nav button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      await loadSection(sectionId);
      history.pushState({ section: sectionId }, '', `#${sectionId}`);
    });
  });

  window.addEventListener('popstate', async (event) => {
    if (event.state && event.state.section && sections[event.state.section]){
      const sec = event.state.section;
      const btn = document.querySelector(`.profile-nav button[data-section="${sec}"]`);
      if (btn){
        document.querySelectorAll('.profile-nav button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      }
      await loadSection(sec);
    }
  });

  if (window.location.hash){
    const hash = window.location.hash.substring(1);
    if (sections[hash]){
      const btn = document.querySelector(`.profile-nav button[data-section="${hash}"]`);
      if (btn) btn.click();
    }
  }
}

function updateSectionIndicator(sectionName){
  $('currentSectionName').textContent = sectionName;
  const indicator = $('sectionIndicator');
  indicator.style.display = 'block';
  setTimeout(() => { indicator.style.display = 'none'; }, 2000);
}

/* ===== Fetch data ===== */
async function loadDepartments(){
  try{
    const res = await fetch('/api/departments', { headers: authHeaders() });
    if (handleAuthStatus(res)) return;

    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load departments');

    let arr = [];
    if (Array.isArray(js.data)) arr = js.data;
    else if (Array.isArray(js?.data?.data)) arr = js.data.data;
    else if (Array.isArray(js.departments)) arr = js.departments;
    else if (Array.isArray(js)) arr = js;

    state.departments = arr;
    state.departmentsLoaded = true;
  } catch(e){
    state.departments = [];
    state.departmentsLoaded = false;
  }
}

function deptName(d){
  return d?.name || d?.title || d?.department_name || d?.dept_name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
}
function deptId(d){
  return d?.id ?? d?.value ?? d?.department_id ?? null;
}

async function fetchProfile(){
  const res = await fetch(`/api/users/${encodeURIComponent(state.uuid)}/profile`, { headers: authHeaders() });
  if (handleAuthStatus(res)) return null;

  const js = await res.json().catch(() => ({}));
  if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load profile');
  return js.data || {};
}

async function fetchUserCore(){
  try{
    const res = await fetch(`/api/users/${encodeURIComponent(state.uuid)}`, { headers: authHeaders() });
    if (handleAuthStatus(res)) return null;

    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) return null;

    let data = js.data ?? js.user ?? js?.data?.user ?? js?.data?.data ?? null;
    if (!data && js && typeof js === 'object') data = js;
    if (!data || typeof data !== 'object') return null;
    return data;
  } catch(e){
    return null;
  }
}

function mergeUserCoreIntoProfile(core){
  if (!core || typeof core !== 'object') return;
  state.profile = state.profile || {};
  state.profile.basic = state.profile.basic || {};

  const depId = core.department_id ?? core.dept_id ?? core.departmentId ?? core?.department?.id ?? null;
  if (depId !== null && depId !== undefined && String(depId) !== '') {
    state.profile.basic.department_id = depId;
  }

  const keys = [
    'name','email','phone_number','alternative_email','alternative_phone_number',
    'whatsapp_number','image','address','role','status','slug','uuid'
  ];
  keys.forEach(k => {
    if (core[k] !== undefined && core[k] !== null) state.profile.basic[k] = core[k];
  });
}

/* ===== Load section ===== */
async function loadSection(sectionId){
  if (state.isLoading || !sections[sectionId]) return;
  state.isLoading = true;
  state.currentSection = sectionId;

  try{
    showLoading(true);
    updateSectionIndicator(sections[sectionId].title);

    const dc = $('dynamicContent');
    dc.innerHTML = '';

    await new Promise(r => setTimeout(r, 200));

    dc.innerHTML = sections[sectionId].render();
    showLoading(false);

    applyManageLinks();

    if (sectionId === 'basic'){
      bindBasicLiveUpdates();
      bindAvatarPreview();
    }
    if (sectionId === 'personal'){
      initPersonalTagsFromProfile();
      initPersonalRTEFromProfile();
    }
    if (sectionId === 'social'){
      bindSocialLiveUpdates();
      updateAllSocialRowPreviews();
    }
    if (sectionId === 'education'){
      updateAllEducationRowPreviews();
    }

  } catch(e){
    showLoading(false);
    $('dynamicContent').innerHTML = `
      <div class="profile-card profile-section">
        <h5><i class="fa fa-triangle-exclamation"></i> Error</h5>
        <div class="text-muted">${escapeHtml(e.message || 'Failed to load section')}</div>
      </div>
    `;
  } finally {
    state.isLoading = false;
  }
}

/* =========================
   SECTION RENDERERS
========================= */
function renderBasicSection(){
  const d = (state.profile?.basic || {});
  const depCurrent = d.department_id ?? d.dept_id ?? d.departmentId ?? d?.department?.id ?? '';

  const deptOptions = (() => {
    if (!state.departmentsLoaded) return `<option value="">(Departments not loaded)</option>`;
    let html = `<option value="">Select Department (optional)</option>`;
    (state.departments || []).forEach(dep => {
      const id = deptId(dep);
      if (id === null || id === undefined || id === '') return;
      const sel = String(id) === String(depCurrent) ? 'selected' : '';
      html += `<option value="${escapeAttr(String(id))}" ${sel}>${escapeHtml(deptName(dep))}</option>`;
    });
    return html;
  })();

  const roleVal = (d.role || '').toLowerCase();
  const statusVal = (d.status || 'active').toLowerCase();

  return `
    <section id="basic" class="profile-card">
      <h5><i class="fa fa-user"></i> Basic Details</h5>

      <form id="basicForm">
        <div class="row g-4">
          <div class="col-12">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input class="form-control" id="bf_name" value="${escapeAttr(d.name || '')}" placeholder="e.g. John Doe" required maxlength="190">
          </div>

          <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="bf_email" value="${escapeAttr(d.email || '')}" placeholder="john@example.com" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" id="bf_phone" value="${escapeAttr(d.phone_number || d.phone || '')}" placeholder="+91 ...">
          </div>

          <div class="col-md-6">
            <label class="form-label">Alternative Email</label>
            <input type="email" class="form-control" id="bf_alt_email" value="${escapeAttr(d.alternative_email || '')}" placeholder="alt@example.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">Alternative Phone</label>
            <input class="form-control" id="bf_alt_phone" value="${escapeAttr(d.alternative_phone_number || '')}" placeholder="+91 ...">
          </div>

          <div class="col-md-6">
            <label class="form-label">WhatsApp</label>
            <input class="form-control" id="bf_whatsapp" value="${escapeAttr(d.whatsapp_number || '')}" placeholder="+91 ...">
          </div>

          <div class="col-md-6">
            <label class="form-label">Department</label>
            <select class="form-select" id="bf_department">
              ${deptOptions}
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" id="bf_role">
              ${renderRoleOptions(roleVal)}
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" id="bf_status">
              <option value="active" ${statusVal==='active'?'selected':''}>Active</option>
              <option value="inactive" ${statusVal==='inactive'?'selected':''}>Inactive</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Address</label>
            <textarea class="form-control" id="bf_address" rows="3" placeholder="Street, City, State, ZIP">${escapeHtml(d.address || '')}</textarea>
          </div>

          <!-- ✅ Avatar: Native file chooser (no modal) -->
          <div class="col-12">
            <label class="form-label">Avatar</label>

            <!-- Actual saved value -->
            <input type="hidden" class="form-control" id="bf_image" value="${escapeAttr(d.image || '')}">

            <div class="input-group">
              <span class="input-group-text bg-light border-0"><i class="fa fa-image text-muted"></i></span>
              <input class="form-control" id="bf_image_display" value="${escapeAttr(d.image || '')}" placeholder="No image selected" readonly>
              <button class="btn btn-soft" type="button" data-file-browse="1" data-file-input="#bf_image_file">
                <i class="fa fa-upload"></i> Choose File
              </button>
              <button class="btn btn-light" type="button" id="bf_image_clear_btn">
                <i class="fa fa-trash"></i> Remove
              </button>

              <!-- hidden native chooser -->
              <input type="file"
                     id="bf_image_file"
                     style="display:none;"
                     accept="image/*"
                     data-uploader="1"
                     data-target-input="#bf_image"
                     data-echo-input="#bf_image_display">
            </div>

            <div class="mt-3 d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
              <img id="bf_image_preview" alt="Preview"
                   style="width:48px;height:48px;border-radius:50%;object-fit:cover;display:${d.image ? 'block':'none'};box-shadow:var(--shadow-sm);"
                   src="${d.image ? escapeAttr(d.image) : ''}">
              <div class="small text-muted" style="line-height:1.4;">
                <strong>Preview:</strong> updates automatically after upload.
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mt-5 pt-3 border-top">
          <button type="button" class="btn btn-primary px-4" data-save="all">
            <i class="fa fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </section>
  `;
}

function renderRoleOptions(current){
  const roles = [
    ['admin','Admin'],
    ['director','Director'],
    ['principal','Principal'],
    ['hod','Head of Department'],
    ['faculty','Faculty'],
    ['technical_assistant','Technical Assistant'],
    ['it_person','IT Person'],
    ['placement_officer','Placement Officer'],
    ['student','Student']
  ];
  let html = `<option value="">Select Role</option>`;
  roles.forEach(([v,l]) => {
    html += `<option value="${escapeAttr(v)}" ${String(current)===String(v)?'selected':''}>${escapeHtml(l)}</option>`;
  });
  return html;
}

function renderPersonalSection(){
  const d = (state.profile?.personal || {});
  const q = (() => {
    let v = d.qualification ?? d.qualifications ?? [];
    if (typeof v === 'string'){
      try{
        const parsed = JSON.parse(v);
        v = parsed;
      }catch(_e){
        v = v.split(',').map(s=>s.trim()).filter(Boolean);
      }
    }
    return Array.isArray(v) ? v : [];
  })();

  const blocks = [
    { key:'affiliation',      label:'Affiliation',      ph:'Write affiliation…' },
    { key:'specification',    label:'Specification',    ph:'Write specification…' },
    { key:'experience',       label:'Experience',       ph:'Write experience…' },
    { key:'interest',         label:'Interests',        ph:'Write interests…' },
    { key:'administration',   label:'Administration',   ph:'Write administration…' },
    { key:'research_project', label:'Research Projects', ph:'Write research projects…' },
  ];

  const rteHTML = blocks.map(r => `
    <div class="rte-row" data-rte="${escapeAttr(r.key)}">
      <label class="form-label">${escapeHtml(r.label)}</label>

      <div class="rte-wrap" id="pi_${escapeAttr(r.key)}Wrap">
        <div class="rte-toolbar" data-for="${escapeAttr(r.key)}">
          <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
          <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
          <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

          <span class="rte-sep"></span>

          <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
          <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

          <span class="rte-sep"></span>

          <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
          <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
          <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>

          <span class="rte-sep"></span>

          <button type="button" class="rte-btn" data-cmd="formatBlock" data-val="pre" title="Code Block"><i class="fa fa-code"></i></button>
          <button type="button" class="rte-btn" data-cmd="insertHTML" data-val="<code>code</code>" title="Inline Code"><i class="fa fa-terminal"></i></button>

          <span class="rte-sep"></span>

          <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

          <div class="rte-tabs">
            <button type="button" class="tab active" data-mode="text">Text</button>
            <button type="button" class="tab" data-mode="code">Code</button>
          </div>
        </div>

        <div class="rte-area">
          <div id="pi_${escapeAttr(r.key)}Editor" class="rte-editor" contenteditable="true" data-placeholder="${escapeAttr(r.ph)}"></div>
          <textarea id="pi_${escapeAttr(r.key)}Code" class="rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
            placeholder="HTML code…"></textarea>
        </div>
      </div>
    </div>
  `).join('');

  return `
    <section id="personal" class="profile-card">
      <h5><i class="fa fa-id-card"></i> Personal Information</h5>

      <form id="personalForm" autocomplete="off">
        <div class="row g-4">

          <!-- ✅ Qualifications as Tags (NO RTE) -->
          <div class="col-12">
            <label class="form-label">Qualifications (Tags)</label>
            <div class="tags-box">
              <div class="tag-input-row">
                <input id="pf_qualInput" class="form-control tag-input" placeholder="Type qualification and press Enter (e.g., B.Tech, M.Tech, PhD)">
                <button type="button" class="btn btn-soft" id="btnAddPfQual">
                  <i class="fa fa-plus me-1"></i> Add
                </button>
              </div>
              <div class="tags" id="pf_qualTags"></div>
              <div class="rte-help">Tip: Press <b>Enter</b> to add. Click × to remove.</div>
            </div>

            <div class="form-text mt-2">${q && q.length ? `Saved: ${escapeHtml(q.join(', '))}` : ''}</div>
          </div>

          <div class="col-12">
            ${rteHTML}
          </div>

        </div>

        <div class="d-flex gap-2 flex-wrap mt-5 pt-3 border-top">
          <button type="button" class="btn btn-primary px-4" data-save="all">
            <i class="fa fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </section>
  `;
}

/* Social Links (kept: icon can be FA class OR uploaded image) */
function renderSocialSection(){
  const rows = Array.isArray(state.profile?.social_media) ? state.profile.social_media : [];
  const list = rows.map((s, i) => socialRowHTML(s, i+1)).join('');

  return `
    <section id="social" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-share-nodes"></i> Social Links</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="social">
            <i class="fa fa-plus"></i> Add Link
          </button>
      </div>

      <div id="socialList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-link fa-2x mb-3 opacity-25"></i><br>No social links added yet.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function socialRowHTML(s, idx){
  const uuid = s?.uuid || '';
  const platform = s?.platform || '';
  const link = s?.link || '';
  const icon = s?.icon || '';
  const sortOrder = (s?.sort_order ?? 0);
  const activeRaw = (s?.active ?? true);
  const activeVal = (String(activeRaw).toLowerCase() === '0' || String(activeRaw).toLowerCase() === 'false') ? '0' : '1';

  return `
    <div class="editor-row" data-row="social">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">

      <div class="row-head">
        <div class="title"><i class="fa fa-link text-muted me-2"></i> Link <span class="pill">#${idx}</span></div>
        <div class="editor-actions d-flex gap-2">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Platform</label>
          <input class="form-control" data-field="platform" value="${escapeAttr(platform)}" placeholder="e.g. LinkedIn">
        </div>

        <div class="col-md-8">
          <label class="form-label">URL</label>
          <input class="form-control" data-field="link" value="${escapeAttr(link)}" placeholder="https://...">
        </div>

        <div class="col-md-6">
          <label class="form-label">Icon (FA class OR Upload Image)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="fa fa-icons text-muted"></i></span>
            <input class="form-control" data-field="icon" value="${escapeAttr(icon)}" placeholder="fa-brands fa-linkedin OR (upload an icon)">
            <button class="btn btn-soft" type="button" data-file-browse="1">
              <i class="fa fa-upload"></i> Choose File
            </button>
            <input type="file"
                   style="display:none;"
                   accept="image/*"
                   data-uploader="1"
                   data-target-field="icon">
          </div>
          <div class="form-text">If you type a FontAwesome class, it renders as an icon. If you upload/select an image, it saves the URL/path.</div>

          <div class="icon-preview-pill mt-2">
            <div class="box" data-preview="social_icon_preview"></div>
            <div class="meta">
              <div class="fw-semibold text-dark">Preview</div>
              <div>Auto-updates as you type / upload</div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Sort Order</label>
          <input type="number" class="form-control" data-field="sort_order" value="${escapeAttr(String(sortOrder))}" placeholder="0" min="0" step="1">
        </div>

        <div class="col-md-3">
          <label class="form-label">Active</label>
          <select class="form-select" data-field="active">
            <option value="1" ${activeVal==='1'?'selected':''}>Yes</option>
            <option value="0" ${activeVal==='0'?'selected':''}>No</option>
          </select>
        </div>
      </div>
    </div>
  `;
}

/* Education */
function renderEducationSection(){
  const educations = Array.isArray(state.profile?.educations) ? state.profile.educations : [];
  const list = educations.map((e, i) => educationRowHTML(e, i+1)).join('');

  return `
    <section id="education" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-graduation-cap"></i> Education</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="education">
            <i class="fa fa-plus"></i> Add Education
          </button>
      </div>

      <div id="eduList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-graduation-cap fa-2x mb-3 opacity-25"></i><br>No education history added yet.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function educationRowHTML(edu, idx){
  const uuid = edu?.uuid || '';
  const educationLevel = edu?.education_level || '';
  const degree = edu?.degree_title || '';
  const fos = edu?.field_of_study || '';
  const inst = edu?.institution_name || '';
  const uni = edu?.university_name || '';
  const enroll = edu?.enrollment_year || '';
  const pass = edu?.passing_year || '';
  const loc = edu?.location || '';
  const gradeType = edu?.grade_type || '';
  const gradeVal = edu?.grade_value || '';
  const cert = edu?.certificate || '';
  const desc = edu?.description || '';

  return `
    <div class="editor-row" data-row="education">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">

      <div class="row-head">
        <div class="title"><i class="fa fa-university text-muted me-2"></i> Education <span class="pill">#${idx}</span></div>
        <div class="editor-actions">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row"><i class="fa fa-trash"></i></button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Education Level</label>
          <input class="form-control" data-field="education_level" value="${escapeAttr(educationLevel)}" placeholder="School / UG / PG / PhD">
        </div>

        <div class="col-md-4">
          <label class="form-label">Enrollment Year</label>
          <input type="number" class="form-control" data-field="enrollment_year" value="${escapeAttr(enroll)}" placeholder="YYYY" min="1900" max="2100" step="1">
        </div>

        <div class="col-md-4">
          <label class="form-label">Passing Year</label>
          <input type="number" class="form-control" data-field="passing_year" value="${escapeAttr(pass)}" placeholder="YYYY" min="1900" max="2100" step="1">
        </div>

        <div class="col-md-6">
          <label class="form-label">Degree Title</label>
          <input class="form-control" data-field="degree_title" value="${escapeAttr(degree)}" placeholder="e.g. B.Tech">
        </div>

        <div class="col-md-6">
          <label class="form-label">Field of Study</label>
          <input class="form-control" data-field="field_of_study" value="${escapeAttr(fos)}" placeholder="e.g. Computer Science">
        </div>

        <div class="col-md-6">
          <label class="form-label">Institution Name</label>
          <input class="form-control" data-field="institution_name" value="${escapeAttr(inst)}" placeholder="Institute / College">
        </div>

        <div class="col-md-6">
          <label class="form-label">University Name</label>
          <input class="form-control" data-field="university_name" value="${escapeAttr(uni)}" placeholder="University (optional)">
        </div>

        <div class="col-md-4">
          <label class="form-label">Location</label>
          <input class="form-control" data-field="location" value="${escapeAttr(loc)}" placeholder="City, Country">
        </div>

        <div class="col-md-4">
          <label class="form-label">Grade Type</label>
          <input class="form-control" data-field="grade_type" value="${escapeAttr(gradeType)}" placeholder="CGPA / %">
        </div>

        <div class="col-md-4">
          <label class="form-label">Grade Value</label>
          <input class="form-control" data-field="grade_value" value="${escapeAttr(gradeVal)}" placeholder="e.g. 9.5 / 78%">
        </div>

        <div class="col-12">
          <label class="form-label">Certificate (Upload)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="fa fa-file-lines text-muted"></i></span>
            <input class="form-control" data-field="certificate" value="${escapeAttr(cert)}" placeholder="Upload a file (PDF/Image) or paste a path">
            <button class="btn btn-soft" type="button" data-file-browse="1">
              <i class="fa fa-upload"></i> Choose File
            </button>
            <input type="file"
                   style="display:none;"
                   accept="image/*,application/pdf"
                   data-uploader="1"
                   data-target-field="certificate">
          </div>

          <div class="icon-preview-pill mt-2">
            <div class="box" data-preview="edu_cert_preview"></div>
            <div class="meta">
              <div class="fw-semibold text-dark">Preview</div>
              <div>Image renders, PDF shows icon</div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" data-field="description" rows="2" placeholder="Optional details...">${escapeHtml(desc)}</textarea>
        </div>
      </div>
    </div>
  `;
}

/* Honors */
function renderHonorsSection(){
  const honors = Array.isArray(state.profile?.honors) ? state.profile.honors : [];
  const list = honors.map((h, i) => honorsRowHTML(h, i+1)).join('');

  return `
    <section id="honors" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-award"></i> Honors & Awards</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="honors">
            <i class="fa fa-plus"></i> Add Honor
          </button>
      </div>

      <div id="honorsList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-trophy fa-2x mb-3 opacity-25"></i><br>No honors or awards added yet.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function honorsRowHTML(h, idx){
  const uuid = h?.uuid || '';
  return `
    <div class="editor-row" data-row="honors">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">
      <div class="row-head">
        <div class="title"><i class="fa fa-award text-muted me-2"></i> Honor <span class="pill">#${idx}</span></div>
        <div class="editor-actions">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row"><i class="fa fa-trash"></i></button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" data-field="title" value="${escapeAttr(h?.title || '')}" placeholder="Award Title">
        </div>
        <div class="col-md-6">
          <label class="form-label">Organization</label>
          <input class="form-control" data-field="honouring_organization" value="${escapeAttr(h?.honouring_organization || '')}" placeholder="Issuer">
        </div>

        <div class="col-md-4">
          <label class="form-label">Year</label>
          <input class="form-control" data-field="honor_year" value="${escapeAttr(h?.honor_year || '')}" placeholder="YYYY">
        </div>
        <div class="col-md-4">
          <label class="form-label">Type</label>
          <input class="form-control" data-field="honor_type" value="${escapeAttr(h?.honor_type || '')}" placeholder="e.g. International">
        </div>
        <div class="col-md-4">
          <label class="form-label">Image (Upload)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="fa fa-image text-muted"></i></span>
            <input class="form-control" data-field="image" value="${escapeAttr(h?.image || '')}" placeholder="Upload an image or paste a path">
            <button class="btn btn-soft" type="button" data-file-browse="1">
              <i class="fa fa-upload"></i> Choose File
            </button>
            <input type="file"
                   style="display:none;"
                   accept="image/*"
                   data-uploader="1"
                   data-target-field="image">
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" data-field="description" rows="2">${escapeHtml(h?.description || '')}</textarea>
        </div>
      </div>
    </div>
  `;
}

/* Journals */
function renderJournalsSection(){
  const journals = Array.isArray(state.profile?.journals) ? state.profile.journals : [];
  const list = journals.map((j, i) => journalRowHTML(j, i+1)).join('');

  return `
    <section id="journals" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-book"></i> Journal Publications</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="journals">
            <i class="fa fa-plus"></i> Add Journal
          </button>
      </div>

      <div id="journalsList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-book-open fa-2x mb-3 opacity-25"></i><br>No journal publications added yet.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function journalRowHTML(j, idx){
  const uuid = j?.uuid || '';
  const sortOrder = (j?.sort_order ?? 0);
  return `
    <div class="editor-row" data-row="journals">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">
      <div class="row-head">
        <div class="title"><i class="fa fa-newspaper text-muted me-2"></i> Journal <span class="pill">#${idx}</span></div>
        <div class="editor-actions">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row"><i class="fa fa-trash"></i></button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" data-field="title" value="${escapeAttr(j?.title || '')}" placeholder="Paper Title">
        </div>
        <div class="col-md-6">
          <label class="form-label">Publisher</label>
          <input class="form-control" data-field="publication_organization" value="${escapeAttr(j?.publication_organization || '')}" placeholder="Journal/Org Name">
        </div>

        <div class="col-md-3">
          <label class="form-label">Year</label>
          <input class="form-control" data-field="publication_year" value="${escapeAttr(j?.publication_year || '')}" placeholder="YYYY">
        </div>

        <div class="col-md-3">
          <label class="form-label">Sort Order</label>
          <input type="number" class="form-control" data-field="sort_order" value="${escapeAttr(String(sortOrder))}" placeholder="0" min="0" step="1">
        </div>

        <div class="col-md-6">
          <label class="form-label">URL</label>
          <input class="form-control" data-field="url" value="${escapeAttr(j?.url || '')}" placeholder="https://...">
        </div>

        <div class="col-md-12">
          <label class="form-label">Image (Upload)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="fa fa-image text-muted"></i></span>
            <input class="form-control" data-field="image" value="${escapeAttr(j?.image || '')}" placeholder="Upload an image or paste a path">
            <button class="btn btn-soft" type="button" data-file-browse="1">
              <i class="fa fa-upload"></i> Choose File
            </button>
            <input type="file"
                   style="display:none;"
                   accept="image/*"
                   data-uploader="1"
                   data-target-field="image">
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" data-field="description" rows="2">${escapeHtml(j?.description || '')}</textarea>
        </div>
      </div>
    </div>
  `;
}

/* Conferences */
function renderConferencesSection(){
  const conferences = Array.isArray(state.profile?.conference_publications) ? state.profile.conference_publications : [];
  const list = conferences.map((c, i) => confRowHTML(c, i+1)).join('');

  return `
    <section id="conferences" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-microphone"></i> Conference Publications</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="conferences">
            <i class="fa fa-plus"></i> Add Conference
          </button>
      </div>

      <div id="conferencesList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-users fa-2x mb-3 opacity-25"></i><br>No conference papers added yet.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function confRowHTML(c, idx){
  const uuid = c?.uuid || '';
  return `
    <div class="editor-row" data-row="conferences">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">
      <div class="row-head">
        <div class="title"><i class="fa fa-microphone text-muted me-2"></i> Conference <span class="pill">#${idx}</span></div>
        <div class="editor-actions">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row"><i class="fa fa-trash"></i></button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" data-field="title" value="${escapeAttr(c?.title || '')}" placeholder="Paper Title">
        </div>
        <div class="col-md-6">
          <label class="form-label">Conference Name</label>
          <input class="form-control" data-field="conference_name" value="${escapeAttr(c?.conference_name || '')}" placeholder="Conference Name">
        </div>

        <div class="col-md-6">
          <label class="form-label">Publication Organization</label>
          <input class="form-control" data-field="publication_organization" value="${escapeAttr(c?.publication_organization || '')}" placeholder="Publisher / Organization">
        </div>
        <div class="col-md-3">
          <label class="form-label">Year</label>
          <input class="form-control" data-field="publication_year" value="${escapeAttr(c?.publication_year || '')}" placeholder="YYYY">
        </div>
        <div class="col-md-3">
          <label class="form-label">Location</label>
          <input class="form-control" data-field="location" value="${escapeAttr(c?.location || '')}" placeholder="City">
        </div>

        <div class="col-md-3">
          <label class="form-label">Type</label>
          <input class="form-control" data-field="publication_type" value="${escapeAttr(c?.publication_type || '')}" placeholder="Paper/Poster/Talk">
        </div>
        <div class="col-md-3">
          <label class="form-label">Domain</label>
          <input class="form-control" data-field="domain" value="${escapeAttr(c?.domain || '')}" placeholder="e.g. AI">
        </div>
        <div class="col-md-6">
          <label class="form-label">URL</label>
          <input class="form-control" data-field="url" value="${escapeAttr(c?.url || '')}" placeholder="https://...">
        </div>

        <div class="col-12">
          <label class="form-label">Image (Upload)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="fa fa-image text-muted"></i></span>
            <input class="form-control" data-field="image" value="${escapeAttr(c?.image || '')}" placeholder="Upload an image or paste a path">
            <button class="btn btn-soft" type="button" data-file-browse="1">
              <i class="fa fa-upload"></i> Choose File
            </button>
            <input type="file"
                   style="display:none;"
                   accept="image/*"
                   data-uploader="1"
                   data-target-field="image">
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" data-field="description" rows="2">${escapeHtml(c?.description || '')}</textarea>
        </div>
      </div>
    </div>
  `;
}

/* Teaching */
function renderTeachingSection(){
  const teaching = Array.isArray(state.profile?.teaching_engagements) ? state.profile.teaching_engagements : [];
  const list = teaching.map((t, i) => teachingRowHTML(t, i+1)).join('');

  return `
    <section id="teaching" class="profile-card">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
          <h5 class="mb-0 border-0 p-0"><i class="fa fa-chalkboard-teacher"></i> Teaching Engagements</h5>
          <button type="button" class="btn btn-sm btn-soft" data-add="teaching">
            <i class="fa fa-plus"></i> Add Teaching
          </button>
      </div>

      <div id="teachingList" class="editor-list">
        ${list || `<div class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
            <i class="fa fa-chalkboard fa-2x mb-3 opacity-25"></i><br>No teaching engagements added.
        </div>`}
      </div>

      <div class="d-flex gap-2 flex-wrap mt-4">
        <button type="button" class="btn btn-primary ms-auto" data-save="all">
          <i class="fa fa-save"></i> Save All
        </button>
      </div>
    </section>
  `;
}

function teachingRowHTML(t, idx){
  const uuid = t?.uuid || '';
  return `
    <div class="editor-row" data-row="teaching">
      <input type="hidden" data-field="uuid" value="${escapeAttr(uuid)}">
      <div class="row-head">
        <div class="title"><i class="fa fa-chalkboard-teacher text-muted me-2"></i> Teaching <span class="pill">#${idx}</span></div>
        <div class="editor-actions">
          <button type="button" class="btn btn-danger-soft btn-sm" data-remove="row"><i class="fa fa-trash"></i></button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Organization Name</label>
          <input class="form-control" data-field="organization_name" value="${escapeAttr(t?.organization_name || '')}" placeholder="Organization">
        </div>
        <div class="col-md-6">
          <label class="form-label">Domain</label>
          <input class="form-control" data-field="domain" value="${escapeAttr(t?.domain || '')}" placeholder="Subject/Topic">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" data-field="description" rows="2">${escapeHtml(t?.description || '')}</textarea>
        </div>
      </div>
    </div>
  `;
}

/* =========================
   Dynamic editor events
========================= */
document.addEventListener('click', async (e) => {
  const rmQual = e.target.closest('#pf_qualTags button.x[data-qual-idx]');
  if (rmQual){
    const idx = parseInt(rmQual.dataset.qualIdx, 10);
    if (!Number.isNaN(idx)){
      state.personalQualification.splice(idx, 1);
      renderPersonalTags();
    }
    return;
  }

  const addQual = e.target.closest('#btnAddPfQual');
  if (addQual){
    e.preventDefault();
    addPersonalTag($('pf_qualInput')?.value);
    return;
  }

  const tab = e.target.closest('.rte-tabs .tab');
  if (tab){
    const wrap = tab.closest('.rte-wrap');
    if(!wrap) return;
    const id = wrap.id || '';
    const key = id.replace('pi_','').replace('Wrap','');
    if (!PERSONAL_RTE_KEYS.includes(key)) return;
    setPersonalRTEMode(key, tab.dataset.mode, { focus:true });
    return;
  }

  const rteBtn = e.target.closest('.rte-toolbar .rte-btn');
  if (rteBtn){
    const tb = rteBtn.closest('.rte-toolbar');
    const key = tb?.getAttribute('data-for');
    if(!key || !state.personalRTE[key]) return;
    if(state.personalRTE[key].mode === 'code') return;

    const cmd = rteBtn.getAttribute('data-cmd');
    const val = rteBtn.getAttribute('data-val');
    const h   = rteBtn.getAttribute('data-h');

    if(h){
      runRTECommand(key, 'formatBlock', h.toUpperCase());
      return;
    }

    if(cmd === 'insertHTML' && val === '<code>code</code>'){
      restoreSelectionFor(key);
      const sel = window.getSelection();
      const selectedText = (sel && sel.rangeCount) ? sel.toString() : '';
      const safe = escapeHtml(selectedText.trim() ? selectedText : 'code');
      runRTECommand(key, 'insertHTML', `<code>${safe}</code>`);
      return;
    }

    if(cmd === 'formatBlock' && val === 'pre'){
      restoreSelectionFor(key);
      const sel = window.getSelection();
      const selectedText = (sel && sel.rangeCount) ? sel.toString() : '';
      const safe = escapeHtml(selectedText);
      const html = selectedText.trim()
        ? `<pre><code>${safe}</code></pre>`
        : `<pre><code></code></pre>`;
      runRTECommand(key, 'insertHTML', html);
      return;
    }

    if(cmd){
      runRTECommand(key, cmd, val);
      return;
    }
  }

  const saveBtn = e.target.closest('[data-save="all"], #btnSaveAllTop, #btnSaveAllSidebar');
  if (saveBtn){
    e.preventDefault();
    await saveAll();
    return;
  }

  const addBtn = e.target.closest('[data-add]');
  if (addBtn){
    e.preventDefault();
    const type = addBtn.dataset.add;
    addRow(type);
    return;
  }

  const removeBtn = e.target.closest('[data-remove="row"]');
  if (removeBtn){
    e.preventDefault();
    const row = removeBtn.closest('.editor-row');
    if (row) row.remove();
    if (state.currentSection === 'social') refreshSidebarSocialFromInputs();
    return;
  }

  const browseBtn = e.target.closest('[data-file-browse="1"]');
  if (browseBtn){
    e.preventDefault();
    const directFileSel = browseBtn.getAttribute('data-file-input');
    let fileInput = null;

    if (directFileSel){
      fileInput = document.querySelector(directFileSel);
    } else {
      const group = browseBtn.closest('.input-group') || browseBtn.closest('.col-12') || browseBtn.parentElement;
      if (group) fileInput = group.querySelector('input[type="file"][data-uploader="1"]');
    }

    if (fileInput) fileInput.click();
    return;
  }

  const clearAvatar = e.target.closest('#bf_image_clear_btn');
  if (clearAvatar){
    e.preventDefault();
    const bf = $('bf_image');
    const bd = $('bf_image_display');
    if (bf){
      bf.value = '';
      try{ bf.dispatchEvent(new Event('input', { bubbles:true })); }catch(err){}
    }
    if (bd) bd.value = '';
    return;
  }
});

document.addEventListener('keydown', (e) => {
  const inp = e.target.closest('#pf_qualInput');
  if (!inp) return;

  if (e.key === 'Enter'){
    e.preventDefault();
    e.stopPropagation();
    addPersonalTag(inp.value);
  }

  if (e.key === 'Backspace' && !inp.value && state.personalQualification.length){
    state.personalQualification.pop();
    renderPersonalTags();
  }
});

document.addEventListener('pointerdown', (e)=>{
  if(e.target.closest('.rte-toolbar button')) e.preventDefault();
});

document.addEventListener('selectionchange', ()=>{
  const key = state.activePersonalRTE;
  if(key && state.personalRTE[key] && state.personalRTE[key].mode === 'text'){
    saveSelectionFor(key);
    updateToolbarActive(key);
  }
});

// ✅ Native file input change => Upload => Set URL/path into target field
document.addEventListener('change', async (e) => {
  const fileInput = e.target && e.target.matches && e.target.matches('input[type="file"][data-uploader="1"]')
    ? e.target
    : null;
  if (!fileInput) return;

  const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
  if (!file) return;

  const target = resolveTargetInputFromFileInput(fileInput);
  const echo = resolveEchoInput(fileInput);

  showGlobalLoading(true);
  try{
    // ✅ NOW uses POST /api/media (same as Media Manager)
    const url = await uploadFileToAnyEndpoint(file);
    if (!url) throw new Error('Upload failed');

    if (target) setInputValueAndTrigger(target, url);
    if (echo) echo.value = url;

    const row = fileInput.closest('.editor-row');
    if (row && row.getAttribute('data-row') === 'social') {
      updateSocialRowPreview(row);
      refreshSidebarSocialFromInputs();
    }
    if (row && row.getAttribute('data-row') === 'education') {
      updateEducationRowPreview(row);
    }

    ok('File uploaded');
  } catch(ex){
    err(ex.message || 'Upload failed');
  } finally {
    showGlobalLoading(false);
    try{ fileInput.value = ''; }catch(_e){}
  }
});

function addRow(type){
  const cleanList = (id) => {
      const el = $(id);
      if(el && el.querySelector('.text-muted.bg-light')) el.innerHTML = '';
      return el;
  };

  if (type === 'social'){
    const list = cleanList('socialList');
    const idx = list.querySelectorAll('[data-row="social"]').length + 1;
    list.insertAdjacentHTML('beforeend', socialRowHTML({uuid:'', platform:'', link:'', icon:'', sort_order:0, active:true}, idx));
    updateAllSocialRowPreviews();
    refreshSidebarSocialFromInputs();
  }
  else if (type === 'education'){
    const list = cleanList('eduList');
    const idx = list.querySelectorAll('[data-row="education"]').length + 1;
    list.insertAdjacentHTML('beforeend', educationRowHTML({uuid:''}, idx));
    updateAllEducationRowPreviews();
  }
  else if (type === 'honors'){
    const list = cleanList('honorsList');
    const idx = list.querySelectorAll('[data-row="honors"]').length + 1;
    list.insertAdjacentHTML('beforeend', honorsRowHTML({uuid:''}, idx));
  }
  else if (type === 'journals'){
    const list = cleanList('journalsList');
    const idx = list.querySelectorAll('[data-row="journals"]').length + 1;
    list.insertAdjacentHTML('beforeend', journalRowHTML({uuid:'', sort_order:0}, idx));
  }
  else if (type === 'conferences'){
    const list = cleanList('conferencesList');
    const idx = list.querySelectorAll('[data-row="conferences"]').length + 1;
    list.insertAdjacentHTML('beforeend', confRowHTML({uuid:''}, idx));
  }
  else if (type === 'teaching'){
    const list = cleanList('teachingList');
    const idx = list.querySelectorAll('[data-row="teaching"]').length + 1;
    list.insertAdjacentHTML('beforeend', teachingRowHTML({uuid:''}, idx));
  }
}

/* ===== Live sidebar updates ===== */
function bindBasicLiveUpdates(){
  const name = $('bf_name');
  const role = $('bf_role');
  const email = $('bf_email');
  const phone = $('bf_phone');
  const address = $('bf_address');
  const image = $('bf_image');
  const imageDisplay = $('bf_image_display');

  if (name) name.addEventListener('input', () => $('name').textContent = name.value.trim() || '—');
  if (role) role.addEventListener('change', () => $('role').textContent = (role.value || '—').toUpperCase());
  if (email) email.addEventListener('input', () => $('email').textContent = email.value.trim() || '—');
  if (phone) phone.addEventListener('input', () => $('phone').textContent = phone.value.trim() || '—');
  if (address) address.addEventListener('input', () => $('address').textContent = address.value.trim().replace(/\n/g, ', ') || '—');

  if (image){
    image.addEventListener('input', () => {
      const val = image.value.trim();
      const avatar = $('avatar');
      const prev = $('bf_image_preview');
      if (imageDisplay) imageDisplay.value = val || '';
      if (prev){
        prev.src = val || '';
        prev.style.display = val ? 'block' : 'none';
      }
      if (val){
        avatar.innerHTML = `<img src="${escapeAttr(val)}" alt="avatar">`;
      } else {
        avatar.innerHTML = `<i class="fa fa-user-graduate"></i>`;
      }
    });
  }
}

function bindAvatarPreview(){
  const image = $('bf_image');
  const prev = $('bf_image_preview');
  if (!image || !prev) return;

  image.addEventListener('input', () => {
    const val = image.value.trim();
    prev.src = val || '';
    prev.style.display = val ? 'block' : 'none';
  });
}

function bindSocialLiveUpdates(){
  const dc = $('dynamicContent');
  if (!dc) return;

  const handler = (e) => {
    const row = e.target.closest('[data-row="social"]');
    if (!row) return;
    updateSocialRowPreview(row);
    refreshSidebarSocialFromInputs();
  };

  dc.addEventListener('input', handler, { passive:true });
  dc.addEventListener('change', handler, { passive:true });
}

function refreshSidebarSocialFromInputs(){
  const list = $('socialList');
  if (!list) return;
  const rows = Array.from(list.querySelectorAll('[data-row="social"]')).map(r => ({
    uuid: r.querySelector('[data-field="uuid"]')?.value?.trim() || '',
    platform: r.querySelector('[data-field="platform"]')?.value?.trim() || '',
    link: r.querySelector('[data-field="link"]')?.value?.trim() || '',
    icon: r.querySelector('[data-field="icon"]')?.value?.trim() || '',
    sort_order: r.querySelector('[data-field="sort_order"]')?.value?.trim() || '0',
    active: r.querySelector('[data-field="active"]')?.value?.trim() || '1'
  }));
  renderSocialIcons(rows);
}

function updateSocialRowPreview(row){
  if (!row) return;
  const iconVal = row.querySelector('[data-field="icon"]')?.value?.trim() || '';
  const box = row.querySelector('[data-preview="social_icon_preview"]');
  if (!box) return;

  box.innerHTML = '';
  if (!iconVal){
    box.innerHTML = `<i class="fa fa-link text-muted"></i>`;
    return;
  }

  if (isProbablyImagePath(iconVal) && !isProbablyPdf(iconVal)){
    box.innerHTML = `<img src="${escapeAttr(iconVal)}" alt="icon">`;
    return;
  }

  box.innerHTML = `<i class="${escapeAttr(iconVal)}"></i>`;
}

function updateAllSocialRowPreviews(){
  const list = $('socialList');
  if (!list) return;
  list.querySelectorAll('[data-row="social"]').forEach(r => updateSocialRowPreview(r));
}

function updateEducationRowPreview(row){
  if (!row) return;
  const certVal = row.querySelector('[data-field="certificate"]')?.value?.trim() || '';
  const box = row.querySelector('[data-preview="edu_cert_preview"]');
  if (!box) return;

  box.innerHTML = '';
  if (!certVal){
    box.innerHTML = `<i class="fa fa-file text-muted"></i>`;
    return;
  }

  if (isProbablyPdf(certVal)){
    box.innerHTML = `<i class="fa fa-file-pdf text-danger"></i>`;
    return;
  }

  if (isProbablyImagePath(certVal)){
    box.innerHTML = `<img src="${escapeAttr(certVal)}" alt="certificate">`;
    return;
  }

  box.innerHTML = `<i class="fa fa-file-lines text-muted"></i>`;
}

function updateAllEducationRowPreviews(){
  const list = $('eduList');
  if (!list) return;
  list.querySelectorAll('[data-row="education"]').forEach(r => updateEducationRowPreview(r));
}

/* =========================
   Collect form values
========================= */
function collectBasicPayload(){
  const name = $('bf_name')?.value?.trim() || '';
  const email = $('bf_email')?.value?.trim() || '';
  const phone = $('bf_phone')?.value?.trim() || '';
  const altEmail = $('bf_alt_email')?.value?.trim() || '';
  const altPhone = $('bf_alt_phone')?.value?.trim() || '';
  const whatsapp = $('bf_whatsapp')?.value?.trim() || '';
  const address = $('bf_address')?.value || '';
  const role = $('bf_role')?.value || '';
  const status = $('bf_status')?.value || 'active';
  const image = $('bf_image')?.value?.trim() || '';
  const dep = $('bf_department')?.value || '';

  const payload = { name, email, role, status, address };

  if (phone) payload.phone_number = phone;
  if (altEmail) payload.alternative_email = altEmail;
  if (altPhone) payload.alternative_phone_number = altPhone;
  if (whatsapp) payload.whatsapp_number = whatsapp;
  if (image) payload.image = image;

  if (dep){
    const n = Number(dep);
    const depVal = Number.isFinite(n) ? n : dep;
    payload.department_id = depVal;
    payload.dept_id = depVal;
    payload.departmentId = depVal;
  }

  return payload;
}

function collectPersonalPayload(){
  const qual = uniqLower((state.personalQualification || []).map(sanitizeTag).filter(Boolean));

  const payload = {
    qualification: qual,
    affiliation: getPersonalRTEHtml('affiliation'),
    specification: getPersonalRTEHtml('specification'),
    experience: getPersonalRTEHtml('experience'),
    interest: getPersonalRTEHtml('interest'),
    administration: getPersonalRTEHtml('administration'),
    research_project: getPersonalRTEHtml('research_project')
  };

  Object.keys(payload).forEach(k=>{
    if (typeof payload[k] === 'string'){
      const t = payload[k].replace(/<br\s*\/?>/gi,'').replace(/&nbsp;/gi,' ').trim();
      if (!t) payload[k] = '';
    }
  });

  return payload;
}

function collectList(containerId, rowType){
  const el = $(containerId);
  if (!el) return [];
  const rows = Array.from(el.querySelectorAll(`[data-row="${rowType}"]`));
  return rows.map(r => {
    const obj = {};
    r.querySelectorAll('[data-field]').forEach(inp => {
      const k = inp.getAttribute('data-field');
      const raw = (inp.value ?? '').toString();
      obj[k] = raw;
    });
    const hasAny = Object.values(obj).some(v => String(v || '').trim() !== '');
    return hasAny ? obj : null;
  }).filter(Boolean);
}

/* =========================
   Save logic
========================= */
async function saveAll(){
  try{
    showGlobalLoading(true);

    const basicFormInView = !!document.querySelector('#dynamicContent #basicForm')
      && !!document.querySelector('#dynamicContent #bf_name')
      && !!document.querySelector('#dynamicContent #bf_email');

    const shouldSaveBasic = (state.currentSection === 'basic') && basicFormInView;

    if (shouldSaveBasic){
      const basic = collectBasicPayload();
      if (!basic.name){ err('Name is required'); return; }
      if (!basic.email){ err('Email is required'); return; }

      const res1 = await fetch(`/api/users/${encodeURIComponent(state.uuid)}`, {
        method: 'PUT',
        headers: { ...authHeaders({ 'Content-Type':'application/json' }) },
        body: JSON.stringify(basic)
      });
      if (handleAuthStatus(res1)) return;

      const js1 = await res1.json().catch(() => ({}));
      if (!res1.ok || js1.success === false){
        let msg = js1.error || js1.message || 'Failed to save basic details';
        if (js1.errors){
          const k = Object.keys(js1.errors)[0];
          if (k && js1.errors[k] && js1.errors[k][0]) msg = js1.errors[k][0];
        }
        throw new Error(msg);
      }

      state.profile = state.profile || {};
      state.profile.basic = state.profile.basic || {};
      if (basic.department_id !== undefined) state.profile.basic.department_id = basic.department_id;

      const core = await fetchUserCore();
      if (core) mergeUserCoreIntoProfile(core);

      initSidebar();
    }

    const profilePayload = {};
    if ($('personalForm')) profilePayload.personal = collectPersonalPayload();
    if ($('socialList')) profilePayload.social_media = collectList('socialList', 'social');
    if ($('eduList')) profilePayload.educations = collectList('eduList', 'education');
    if ($('honorsList')) profilePayload.honors = collectList('honorsList', 'honors');
    if ($('journalsList')) profilePayload.journals = collectList('journalsList', 'journals');
    if ($('conferencesList')) profilePayload.conference_publications = collectList('conferencesList', 'conferences');
    if ($('teachingList')) profilePayload.teaching_engagements = collectList('teachingList', 'teaching');

    if (Object.keys(profilePayload).length){
      let res2 = await fetch(`/api/users/${encodeURIComponent(state.uuid)}/profile`, {
        method: 'PUT',
        headers: { ...authHeaders({ 'Content-Type':'application/json' }) },
        body: JSON.stringify(profilePayload)
      });

      if (res2.status === 404) {
        res2 = await fetch(`/api/users/${encodeURIComponent(state.uuid)}/profile`, {
          method: 'POST',
          headers: { ...authHeaders({ 'Content-Type':'application/json' }) },
          body: JSON.stringify(profilePayload)
        });
      }

      if (handleAuthStatus(res2)) return;

      const js2 = await res2.json().catch(() => ({}));
      if (!res2.ok || js2.success === false){
        let msg = js2.error || js2.message || 'Failed to save profile sections';
        const bag = js2.errors || js2.details || null;
        if (bag && typeof bag === 'object'){
          const k = Object.keys(bag)[0];
          if (k && bag[k] && bag[k][0]) msg = bag[k][0];
        }
        throw new Error(msg);
      }

      if (js2.data) state.profile = js2.data;

      initSidebar();
      applyManageLinks();
    }

    ok('Profile updated successfully');
    syncTokenAcrossStorages(state.token);

  } catch(e){
    console.error(e);
    err(e.message || 'Save failed');
  } finally {
    showGlobalLoading(false);
  }
}

/* =========================
   App init
========================= */
async function initApp(){
  state.token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!state.token){
    window.location.href = '/';
    return;
  }

  syncTokenAcrossStorages(state.token);
  applyManageLinks();

  state.uuid = parseUuidFromPath();
  if (!state.uuid){
    showLoading(false);
    $('dynamicContent').innerHTML = `
      <div class="profile-card profile-section">
        <h5><i class="fa fa-triangle-exclamation"></i> Missing UUID</h5>
        <div class="text-muted">No user UUID found in URL.</div>
        <div class="mt-3"><a href="${escapeAttr(getManageUsersUrl())}" class="btn btn-light" data-manage-link="1"><i class="fa fa-arrow-left me-1"></i> Back to Users</a></div>
      </div>
    `;
    applyManageLinks();
    return;
  }

  try{
    showLoading(true);
    await loadDepartments();
    state.profile = await fetchProfile();

    const core = await fetchUserCore();
    if (core) mergeUserCoreIntoProfile(core);

    initSidebar();
    setupNavigation();
    setupSidebarScrollHint();
    await loadSection('basic');
  } catch(e){
    console.error(e);
    showLoading(false);
    $('dynamicContent').innerHTML = `
      <div class="profile-card profile-section">
        <h5><i class="fa fa-triangle-exclamation"></i> Failed to load profile</h5>
        <div class="text-muted">${escapeHtml(e.message || 'Error')}</div>
        <div class="mt-3 d-flex gap-2 flex-wrap">
          <a href="${escapeAttr(getManageUsersUrl())}" class="btn btn-light" data-manage-link="1"><i class="fa fa-arrow-left me-1"></i> Back to Users</a>
          <button class="btn btn-primary" type="button" onclick="location.reload()"><i class="fa fa-rotate me-1"></i> Retry</button>
        </div>
      </div>
    `;
    applyManageLinks();
  } finally {
    showLoading(false);
  }
}

document.addEventListener('DOMContentLoaded', initApp);
</script>

</body>
</html>
