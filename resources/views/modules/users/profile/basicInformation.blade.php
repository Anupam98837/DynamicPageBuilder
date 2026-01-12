{{-- resources/views/modules/user/manageBasicInformation.blade.php --}}
@section('title','Basic Information')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Basic Information (Modern + Theme)
 * - Avatar on top (hover -> Change)
 * - After pick: Cancel + Save Photo
 * - No UUID / Time chips
 * - No image path section
 * ========================= */

.bi-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}

/* Header card */
.bi-hero{
  position:relative;
  border-radius:22px;
  padding:22px 22px;
  color:#fff;
  overflow:hidden;
  box-shadow:var(--shadow-3);
  background:linear-gradient(135deg,
    var(--primary-color) 0%,
    color-mix(in oklab, var(--primary-color) 70%, #7c3aed) 100%);
  border:1px solid color-mix(in oklab, #fff 15%, transparent);
}
.bi-hero::before{
  content:'';
  position:absolute;right:-80px;top:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle, rgba(255,255,255,.14) 0%, rgba(255,255,255,0) 70%);
}
.bi-hero-inner{position:relative;z-index:1;display:flex;gap:18px;align-items:center;flex-wrap:wrap}
.bi-hero-title{font-size:26px;font-weight:800;letter-spacing:-.2px;margin:0}
.bi-hero-sub{margin:6px 0 0;font-size:14px;opacity:.9}
.bi-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 12px;border-radius:999px;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.16);
  font-size:13px;
}

/* Avatar */
.bi-avatar-wrap{display:flex;flex-direction:column;gap:10px;align-items:flex-start}
.bi-avatar{
  width:108px;height:108px;border-radius:18px;
  border:3px solid rgba(255,255,255,.28);
  background:rgba(255,255,255,.12);
  backdrop-filter: blur(10px);
  overflow:hidden;
  position:relative;
  flex-shrink:0;
  box-shadow:0 10px 26px rgba(0,0,0,.18);
}
.bi-avatar img{width:100%;height:100%;object-fit:cover;display:none}
.bi-avatar .ph{
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  font-size:40px;color:rgba(255,255,255,.92);
}
.bi-avatar-overlay{
  position:absolute;inset:0;
  display:flex;align-items:flex-end;justify-content:center;
  padding:10px;
  opacity:0;transform:translateY(6px);
  transition:all .2s ease;
  background:linear-gradient(to top, rgba(0,0,0,.55), rgba(0,0,0,0));
}
.bi-avatar:hover .bi-avatar-overlay{opacity:1;transform:translateY(0)}
.bi-avatar-overlay .btn{
  border-radius:12px;
  padding:8px 12px;
  font-weight:700;
  font-size:12.5px;
}
.bi-avatar-actions{display:none;gap:10px;flex-wrap:wrap}
.bi-avatar-actions .btn{border-radius:12px;font-weight:800}

/* Main card */
.bi-card{
  margin-top:16px;
  border:1px solid var(--line-strong);
  border-radius:18px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.bi-card .bi-card-head{
  padding:14px 16px;
  border-bottom:1px solid var(--line-soft);
  display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.bi-card .bi-head-title{
  display:flex;align-items:center;gap:10px;
  font-weight:800;color:var(--ink);
}
.bi-card .bi-head-sub{font-size:12.5px;color:var(--muted-color);margin-top:3px}
.bi-card .bi-body{padding:16px}
.bi-card .bi-foot{
  padding:14px 16px;
  border-top:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
}

/* Inputs (theme-ish) */
.bi-label{font-weight:700;color:var(--ink);font-size:13.5px;margin-bottom:7px}
.bi-help{font-size:12.5px;color:var(--muted-color);margin-top:7px}
.bi-input.form-control{
  border-radius:14px;
  border:1px solid var(--line-soft);
  padding:12px 14px;
}
.bi-input.form-control:focus{
  border-color:var(--primary-color);
  box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 20%, transparent);
}
.bi-input[readonly], .bi-input:disabled{
  background:color-mix(in oklab, var(--surface) 94%, #000);
}

/* Buttons */
.bi-btns{display:flex;gap:10px;flex-wrap:wrap}
.bi-btns .btn{border-radius:14px;font-weight:800}
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Loader overlay */
.inline-loader{
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  display:none;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.inline-loader.show{display:flex}

/* Toasts */
.toast-container{z-index:99999}

@media (max-width: 768px){
  .bi-hero{padding:18px}
  .bi-hero-title{font-size:22px}
  .bi-avatar{width:96px;height:96px;border-radius:16px}
  .bi-card .bi-body{padding:14px}
}
</style>
@endpush

@section('content')
<div class="bi-wrap">

  {{-- overlay loader --}}
  <div id="inlineLoader" class="inline-loader">
    @include('partials.overlay')
  </div>

  {{-- HERO --}}
  <div class="bi-hero">
    <div class="bi-hero-inner">
      <div class="bi-avatar-wrap">
        <div class="bi-avatar" id="avatarBox" role="button" tabindex="0" title="Change profile image">
          <img id="avatarImg" alt="Profile image">
          <div class="ph" id="avatarPh"><i class="fa-solid fa-user"></i></div>

          <div class="bi-avatar-overlay">
            <button type="button" class="btn btn-light btn-sm" id="btnPickAvatar">
              <i class="fa-solid fa-camera me-1"></i> Change Image
            </button>
          </div>
        </div>

        <input type="file" id="avatarFile" accept="image/*" hidden>

        <div class="bi-avatar-actions" id="avatarActions">
          <button type="button" class="btn btn-light btn-sm" id="btnAvatarCancel">
            <i class="fa-solid fa-xmark me-1"></i> Cancel
          </button>
          <button type="button" class="btn btn-primary btn-sm" id="btnAvatarSave">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Photo
          </button>
        </div>
      </div>

      <div style="flex:1;min-width:240px">
        <h1 class="bi-hero-title" id="profileName">Loading…</h1>
        <div class="bi-hero-sub" id="profileEmail">—</div>

        <div class="mt-3">
          <span class="bi-chip">
            <i class="fa-solid fa-id-card"></i> <span id="profileRole">—</span>
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- FORM CARD --}}
  <div class="bi-card">
    <div class="bi-card-head">
      <div>
        <div class="bi-head-title"><i class="fa-solid fa-user-pen"></i> Basic Information</div>
        <div class="bi-head-sub">Update your personal details.</div>
      </div>
      <div class="small text-muted">
        Loads from <code>GET /api/users/me</code> • Saves via <code>PATCH /api/users/me</code>
      </div>
    </div>

    <div class="bi-body">
      <form id="basicForm" autocomplete="off">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <label class="bi-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control bi-input" id="name" placeholder="Enter your full name" required>
            <div class="bi-help">Your display name across the platform</div>
          </div>

          <div class="col-12 col-lg-6">
            <label class="bi-label">Phone Number</label>
            <input type="tel" class="form-control bi-input" id="phone_number" placeholder="+91 XXXXX XXXXX">
            <div class="bi-help">Primary contact number</div>
          </div>

          <div class="col-12 col-lg-6">
            <label class="bi-label">Alternative Email</label>
            <input type="email" class="form-control bi-input" id="alternative_email" placeholder="backup@example.com">
          </div>

          <div class="col-12 col-lg-6">
            <label class="bi-label">Alternative Phone</label>
            <input type="tel" class="form-control bi-input" id="alternative_phone_number" placeholder="+91 XXXXX XXXXX">
          </div>

          <div class="col-12 col-lg-6">
            <label class="bi-label">WhatsApp Number</label>
            <input type="tel" class="form-control bi-input" id="whatsapp_number" placeholder="+91 XXXXX XXXXX">
          </div>

          <div class="col-12 col-lg-6">
            <label class="bi-label">Address</label>
            <input type="text" class="form-control bi-input" id="address" placeholder="Enter your address">
          </div>
        </div>
      </form>
    </div>

    <div class="bi-foot">
      <div class="small text-muted">
        <i class="fa-regular fa-circle-info me-1"></i>
        Reset will restore last loaded server values.
      </div>
      <div class="bi-btns">
        <button type="button" class="btn btn-light" id="btnReset">
          <i class="fa-solid fa-rotate-left me-1"></i> Reset
        </button>
        <button type="submit" class="btn btn-primary" id="btnSave" form="basicForm">
          <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile
        </button>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastSuccessText">Done</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastErrorText">Something went wrong</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  if (window.__BASIC_INFO_INIT__) return;
  window.__BASIC_INFO_INIT__ = true;

  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const inlineLoader = document.getElementById('inlineLoader');
  const showInlineLoading = (show) => inlineLoader?.classList.toggle('show', !!show);

  // Toasts
  const toastOk = bootstrap.Toast ? new bootstrap.Toast(document.getElementById('toastSuccess')) : null;
  const toastEr = bootstrap.Toast ? new bootstrap.Toast(document.getElementById('toastError')) : null;
  const ok = (m)=>{ document.getElementById('toastSuccessText').textContent = m || 'Done'; toastOk?.show(); };
  const err = (m)=>{ document.getElementById('toastErrorText').textContent = m || 'Something went wrong'; toastEr?.show(); };

  function authHeaders(extra = {}){ return Object.assign({ 'Authorization': 'Bearer ' + token, 'Accept':'application/json' }, extra); }
  const SITE_ORIGIN = window.location.origin;

  function toAbsoluteUrl(path) {
    const p = (path || '').toString().trim();
    if (!p) return '';
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith('//')) return p;
    if (p.startsWith('/')) return SITE_ORIGIN + p;
    return SITE_ORIGIN + '/' + p.replace(/^\//, '');
  }

  // Header DOM
  const profileName = document.getElementById('profileName');
  const profileEmail = document.getElementById('profileEmail');
  const profileRole = document.getElementById('profileRole');

  // Avatar DOM
  const avatarBox = document.getElementById('avatarBox');
  const avatarImg = document.getElementById('avatarImg');
  const avatarPh = document.getElementById('avatarPh');
  const btnPickAvatar = document.getElementById('btnPickAvatar');
  const avatarFile = document.getElementById('avatarFile');
  const avatarActions = document.getElementById('avatarActions');
  const btnAvatarCancel = document.getElementById('btnAvatarCancel');
  const btnAvatarSave = document.getElementById('btnAvatarSave');

  // Form DOM
  const form = document.getElementById('basicForm');
  const btnReset = document.getElementById('btnReset');
  const btnSave = document.getElementById('btnSave');

  const nameEl = document.getElementById('name');
  const phoneEl = document.getElementById('phone_number');
  const altEmailEl = document.getElementById('alternative_email');
  const altPhoneEl = document.getElementById('alternative_phone_number');
  const waEl = document.getElementById('whatsapp_number');
  const addressEl = document.getElementById('address');

  // State
  let currentUser = null;
  let lastServerData = null;
  let saving = false;

  // Avatar preview temp URL
  let pendingAvatarObjectUrl = '';
  function revokePendingUrl(){
    if (pendingAvatarObjectUrl) {
      try{ URL.revokeObjectURL(pendingAvatarObjectUrl); }catch(_){}
      pendingAvatarObjectUrl = '';
    }
  }

  function setBtnLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  function updateHeader(user){
    profileName.textContent = user?.name || 'No name set';
    profileEmail.textContent = user?.email || '—';
    profileRole.textContent = (user?.role || 'user').toString().toUpperCase();
  }

  function setAvatarFromPath(path){
    const abs = toAbsoluteUrl(path);
    if (abs) {
      avatarImg.src = abs;
      avatarImg.style.display = 'block';
      avatarPh.style.display = 'none';
    } else {
      avatarImg.src = '';
      avatarImg.style.display = 'none';
      avatarPh.style.display = 'flex';
    }
  }

  function showAvatarActions(show){
    avatarActions.style.display = show ? 'flex' : 'none';
  }

  async function loadUserData(){
    showInlineLoading(true);
    try{
      const res = await fetch('/api/users/me', { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load profile');

      currentUser = js.data || {};
      lastServerData = JSON.parse(JSON.stringify(currentUser));

      // Fill form
      nameEl.value = currentUser.name || '';
      phoneEl.value = currentUser.phone_number || '';
      altEmailEl.value = currentUser.alternative_email || '';
      altPhoneEl.value = currentUser.alternative_phone_number || '';
      waEl.value = currentUser.whatsapp_number || '';
      addressEl.value = currentUser.address || '';

      updateHeader(currentUser);
      setAvatarFromPath(currentUser.image);

      showAvatarActions(false);
      if (avatarFile) avatarFile.value = '';
      revokePendingUrl();

      ok('Profile loaded');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to load profile');
    }finally{
      showInlineLoading(false);
    }
  }

  // Pick avatar (button + clicking avatar)
  function openAvatarPicker(){ avatarFile?.click(); }
  btnPickAvatar?.addEventListener('click', (e)=>{ e.preventDefault(); openAvatarPicker(); });
  avatarBox?.addEventListener('click', (e)=>{
    if (e.target.closest('#btnPickAvatar')) return;
    openAvatarPicker();
  });
  avatarBox?.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openAvatarPicker(); }
  });

  // On file selected -> show preview + actions
  avatarFile?.addEventListener('change', ()=>{
    const f = avatarFile.files && avatarFile.files[0] ? avatarFile.files[0] : null;
    if(!f){
      revokePendingUrl();
      setAvatarFromPath(lastServerData?.image || '');
      showAvatarActions(false);
      return;
    }
    revokePendingUrl();
    pendingAvatarObjectUrl = URL.createObjectURL(f);
    avatarImg.src = pendingAvatarObjectUrl;
    avatarImg.style.display = 'block';
    avatarPh.style.display = 'none';
    showAvatarActions(true);
  });

  // Cancel avatar change
  btnAvatarCancel?.addEventListener('click', ()=>{
    if (avatarFile) avatarFile.value = '';
    revokePendingUrl();
    setAvatarFromPath(lastServerData?.image || '');
    showAvatarActions(false);
    ok('Cancelled');
  });

  // Save ONLY avatar photo
  btnAvatarSave?.addEventListener('click', async ()=>{
    const f = avatarFile?.files && avatarFile.files[0] ? avatarFile.files[0] : null;
    if(!f){ err('Please choose an image first'); return; }

    showInlineLoading(true);
    setBtnLoading(btnAvatarSave, true);
    try{
      const fd = new FormData();
      fd.append('image_file', f);

      const res = await fetch('/api/users/me', {
        method: 'PATCH',
        headers: { 'Authorization': 'Bearer ' + token },
        body: fd
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to update image');

      currentUser = js.data || currentUser;
      lastServerData = JSON.parse(JSON.stringify(currentUser));

      // reset file state
      if (avatarFile) avatarFile.value = '';
      revokePendingUrl();
      setAvatarFromPath(currentUser.image);

      updateHeader(currentUser);
      showAvatarActions(false);
      ok('Photo updated');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to update image');
    }finally{
      setBtnLoading(btnAvatarSave, false);
      showInlineLoading(false);
    }
  });

  // Save profile fields (no image path, but if avatar file is selected we also send it)
  form?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (saving) return;

    const nm = (nameEl.value || '').trim();
    if(!nm){ nameEl.focus(); return; }

    saving = true;
    showInlineLoading(true);
    setBtnLoading(btnSave, true);

    try{
      const fd = new FormData();
      fd.append('name', nm);

      const v = (x)=> (x || '').toString().trim();
      if (v(phoneEl.value)) fd.append('phone_number', v(phoneEl.value));
      if (v(altEmailEl.value)) fd.append('alternative_email', v(altEmailEl.value));
      if (v(altPhoneEl.value)) fd.append('alternative_phone_number', v(altPhoneEl.value));
      if (v(waEl.value)) fd.append('whatsapp_number', v(waEl.value));
      if (v(addressEl.value)) fd.append('address', v(addressEl.value));

      // If user selected a photo, patch it along with profile save (still ok)
      const f = avatarFile?.files && avatarFile.files[0] ? avatarFile.files[0] : null;
      if (f) fd.append('image_file', f);

      const res = await fetch('/api/users/me', {
        method: 'PATCH',
        headers: { 'Authorization': 'Bearer ' + token },
        body: fd
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to save profile');

      currentUser = js.data || currentUser;
      lastServerData = JSON.parse(JSON.stringify(currentUser));

      updateHeader(currentUser);
      setAvatarFromPath(currentUser.image);

      // clear avatar pending state
      if (avatarFile) avatarFile.value = '';
      revokePendingUrl();
      showAvatarActions(false);

      ok('Profile updated');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to save profile');
    }finally{
      saving = false;
      setBtnLoading(btnSave, false);
      showInlineLoading(false);
    }
  });

  // Reset
  btnReset?.addEventListener('click', ()=>{
    if(!lastServerData) return;

    nameEl.value = lastServerData.name || '';
    phoneEl.value = lastServerData.phone_number || '';
    altEmailEl.value = lastServerData.alternative_email || '';
    altPhoneEl.value = lastServerData.alternative_phone_number || '';
    waEl.value = lastServerData.whatsapp_number || '';
    addressEl.value = lastServerData.address || '';

    // cancel avatar pending
    if (avatarFile) avatarFile.value = '';
    revokePendingUrl();
    setAvatarFromPath(lastServerData.image || '');
    showAvatarActions(false);

    ok('Reset');
  });

  // Init
  loadUserData();
})();
</script>
@endpush
