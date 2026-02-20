{{-- resources/views/modules/users/userBasicProfile.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Basic Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

<style>
:root {
  --surface-alt: #f1f5f9;
  --ink: #1e293b;
  --muted-color: #64748b;
  --line-strong: #e2e8f0;
  --line-light: #f1f5f9;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --shadow-1: 0 1px 3px rgba(0,0,0,0.1);
  --shadow-2: 0 4px 6px -1px rgba(0,0,0,0.1);
  --shadow-3: 0 10px 15px -3px rgba(0,0,0,0.1);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
}

body {
  background: var(--bg-body);
  color: var(--ink);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  line-height: 1.6;
  min-height: 100vh;
}

/* ===== Layout ===== */
.profile-layout {
  max-width: 1280px;
  margin: 0 auto;
  padding: 24px;
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 32px;
  min-height: calc(100vh - 48px);
}
@media (max-width: 992px) {
  .profile-layout { grid-template-columns: 1fr; gap: 24px; }
}
@media (max-width: 768px) {
  .profile-layout { padding: 16px; }
}

/* ===== Sidebar (image to social only) ===== */
.profile-sidebar {
  background: var(--surface);
  border-radius: var(--radius-xl);
  padding: 24px;
  box-shadow: var(--shadow-2);
  border: 1px solid var(--line-strong);
  position: sticky;
  top: 24px;
  height: fit-content;
}

.profile-avatar-container { position: relative; width: 140px; height: 140px; margin: 0 auto 20px; }
.profile-avatar {
  width: 100%; height: 100%;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: linear-gradient(135deg, var(--primary-light, #eef2ff), #e0f2fe);
  display: flex; align-items: center; justify-content: center;
  font-size: 48px; color: var(--primary-color, #9E363A);
  border: 4px solid white;
  box-shadow: var(--shadow-3);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }

.profile-badge {
  position: absolute;
  bottom: -5px; right: -5px;
  background: var(--primary-color, #9E363A);
  color: white;
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  border: 3px solid white;
}

.profile-name {
  font-weight: 700;
  font-size: 1.25rem;
  text-align: center;
  margin-bottom: 6px;
  word-break: break-word;
}
.profile-role {
  font-size: 0.9rem;
  color: var(--primary-color, #9E363A);
  text-align: center;
  font-weight: 700;
  background: var(--primary-light, #fdf2f8);
  padding: 5px 12px;
  border-radius: 999px;
  display: inline-block;
  margin: 0 auto 16px;
}
.profile-role-wrap { text-align: center; }

.profile-contact {
  background: var(--surface-alt);
  padding: 16px;
  border-radius: var(--radius-md);
  margin-bottom: 18px;
  border: 1px solid var(--line-strong);
}
.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 12px;
  font-size: 0.9rem;
}
.contact-item:last-child { margin-bottom: 0; }
.contact-item i {
  color: var(--primary-color, #9E363A);
  width: 20px;
  margin-top: 2px;
}
.contact-item span {
  min-width: 0;
  word-break: break-word;
}

.profile-social {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 6px;
  flex-wrap: wrap;
}
.profile-social a {
  width: 42px; height: 42px;
  border-radius: var(--radius-md);
  background: var(--surface-alt);
  display: flex; align-items: center; justify-content: center;
  color: var(--ink);
  transition: all 0.25s ease;
  border: 1px solid var(--line-strong);
  text-decoration: none;
}
.profile-social a:hover {
  background: var(--primary-color, #9E363A);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-2);
}

/* ===== Content Area ===== */
.profile-content {
  position: relative;
  min-height: 420px;
}

.loading-indicator {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: var(--muted-color);
}
.loading-spinner {
  width: 40px; height: 40px;
  border: 3px solid var(--line-strong);
  border-top-color: var(--primary-color, #9E363A);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.content-stack {
  display: grid;
  gap: 20px;
}

/* ===== Section Card ===== */
.profile-card {
  background: var(--surface);
  border-radius: var(--radius-xl);
  padding: 28px;
  box-shadow: var(--shadow-2);
  border: 1px solid var(--line-strong);
  animation: fadeIn .25s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.profile-card h5 {
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid var(--line-light);
  color: var(--primary-color, #9E363A);
}
.profile-card h5 i {
  background: var(--primary-light, #fdf2f8);
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

/* ===== KV ===== */
.kv {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 14px 22px;
  font-size: 0.95rem;
}
.kv .k {
  color: var(--muted-color);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .2px;
}
.kv .v {
  font-weight: 400;
  line-height: 1.7;
  word-break: break-word;
}
.kv .v ul { padding-left: 20px; margin: 8px 0; }
.kv .v li { margin-bottom: 4px; }

.kv-divider{
  grid-column: 1 / -1;
  height: 1px;
  border-radius: 999px;
  background: linear-gradient(90deg,
    transparent,
    rgba(148,163,184,.35),
    rgba(148,163,184,.50),
    rgba(148,163,184,.35),
    transparent
  );
  margin: 4px 0 6px;
}
html.theme-dark .kv-divider{
  background: linear-gradient(90deg,
    transparent,
    rgba(148,163,184,.18),
    rgba(148,163,184,.30),
    rgba(148,163,184,.18),
    transparent
  );
}

@media (max-width: 768px) {
  .kv { grid-template-columns: 1fr; gap: 10px; }
  .kv .k { color: var(--ink); }
}

/* ===== States ===== */
.empty {
  color: var(--muted-color);
  text-align: center;
  padding: 36px 18px;
  font-size: 1rem;
}
.empty i {
  font-size: 2rem;
  margin-bottom: 12px;
  display: block;
  color: var(--line-strong);
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  padding: 4px 12px;
  border-radius: 999px;
  background: #dcfce7;
  color: #166534;
  font-weight: 600;
}
.status-indicator::before {
  content: '';
  width: 8px; height: 8px;
  background: #22c55e;
  border-radius: 50%;
}

.qualification-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.qualification-tag {
  background: var(--primary-light, #fdf2f8);
  color: var(--primary-color, #9E363A);
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.88rem;
  font-weight: 600;
  border: 1px solid var(--line-strong);
}
</style>
</head>

<body>

@include('landing.components.header')
@include('landing.components.headerMenu')

<div class="profile-layout">

  <!-- LEFT: image to social -->
  <aside class="profile-sidebar">
    <div class="profile-avatar-container">
      <div class="profile-avatar" id="avatar">
        <i class="fa fa-user"></i>
      </div>
      <div class="profile-badge"><i class="fa fa-check"></i></div>
    </div>

    <div class="profile-name" id="name">—</div>

    <div class="profile-role-wrap">
      <div class="profile-role" id="role">—</div>
    </div>

    <div class="profile-contact">
      <div class="contact-item">
        <i class="fa fa-envelope"></i>
        <span id="email">—</span>
      </div>
      <div class="contact-item">
        <i class="fa fa-phone"></i>
        <span id="phone">—</span>
      </div>
      <div class="contact-item">
        <i class="fa fa-map-marker-alt"></i>
        <span id="address">—</span>
      </div>
    </div>

    <div class="profile-social" id="socialIcons"></div>
  </aside>

  <!-- RIGHT: basic + personal -->
  <main class="profile-content">
    <div class="loading-indicator" id="loadingIndicator">
      <div class="loading-spinner"></div>
      <div>Loading profile...</div>
    </div>

    <div id="dynamicContent" style="display:none;"></div>
  </main>

</div>

<script>
let profileData = null;

function escHtml(v){
  return String(v ?? '').replace(/[&<>"']/g, (m) => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

function normalizeUrl(url){
  const u = String(url || '').trim();
  if (!u) return '';
  if (/^(https?:\/\/|data:|blob:)/i.test(u)) return u;
  if (u.startsWith('/')) return window.location.origin + u;
  return window.location.origin + '/' + u;
}

function safeLink(url){
  const u = String(url || '').trim();
  if (!u) return '';
  if (/^javascript:/i.test(u)) return '';
  return normalizeUrl(u);
}

function showLoading(show){
  const loader = document.getElementById('loadingIndicator');
  const content = document.getElementById('dynamicContent');
  if (show){
    loader.style.display = 'block';
    content.style.display = 'none';
  } else {
    loader.style.display = 'none';
    content.style.display = 'block';
  }
}

function showError(message){
  const content = document.getElementById('dynamicContent');
  content.innerHTML = `
    <div class="profile-card">
      <div class="empty">
        <i class="fa fa-exclamation-triangle"></i>
        <div>${escHtml(message || 'Failed to load profile')}</div>
      </div>
    </div>
  `;
  showLoading(false);
}

function formatText(text){
  if (!text) return '—';
  return String(text)
    .replace(/<br\s*\/?>/gi, '<br>')
    .replace(/<br data-start="\d+" data-end="\d+"\s*\/?>/g, '<br>')
    .replace(/<p[^>]*>/g, '<p>')
    .replace(/<\/p>/g, '</p>')
    .replace(/<strong[^>]*>/g, '<strong>')
    .replace(/<\/strong>/g, '</strong>')
    .replace(/<ul[^>]*>/g, '<ul>')
    .replace(/<\/ul>/g, '</ul>')
    .replace(/<li[^>]*>/g, '<li>')
    .replace(/<\/li>/g, '</li>');
}

function initSidebar(){
  const d = profileData?.basic || {};

  document.getElementById('name').textContent = d.name || '—';
  document.getElementById('role').textContent = ((d.role || '').toString().toUpperCase()) || '—';
  document.getElementById('email').textContent = d.email || '—';
  document.getElementById('phone').textContent = d.phone_number || '—';
  document.getElementById('address').textContent = d.address ? String(d.address).replace(/\n/g, ', ') : '—';

  const avatar = document.getElementById('avatar');
  const imgUrl = safeLink(d.image || '');
  if (imgUrl) {
    avatar.innerHTML = `<img src="${escHtml(imgUrl)}" alt="${escHtml(d.name || 'User')}">`;
  } else {
    avatar.innerHTML = `<i class="fa fa-user"></i>`;
  }

  const socialIconsMap = {
    'linkedin': 'fa-brands fa-linkedin',
    'github': 'fa-brands fa-github',
    'orcid': 'fa-brands fa-orcid',
    'google scholar': 'fa fa-graduation-cap',
    'researchgate': 'fa-brands fa-researchgate',
    'twitter': 'fa-brands fa-twitter',
    'x': 'fa-brands fa-x-twitter',
    'facebook': 'fa-brands fa-facebook',
    'instagram': 'fa-brands fa-instagram'
  };

  const socialIcons = document.getElementById('socialIcons');
  socialIcons.innerHTML = '';

  (profileData?.social_media || []).forEach(s => {
    const href = safeLink(s.link);
    if (!href) return;

    const platform = (s.platform || '').toLowerCase().trim();
    const iconClass = socialIconsMap[platform] || 'fa fa-link';

    socialIcons.insertAdjacentHTML('beforeend', `
      <a href="${escHtml(href)}" target="_blank" rel="noopener noreferrer" title="${escHtml(s.platform || 'Link')}">
        <i class="${escHtml(iconClass)}"></i>
      </a>
    `);
  });
}

function renderBasicCard(){
  const d = profileData?.basic || {};

  const basicFields = [
    ['Email', d.email || '—'],
    ['Phone', d.phone_number || '—'],
    ['Alternative Email', d.alternative_email || '—'],
    ['Alternative Phone', d.alternative_phone_number || '—'],
    ['WhatsApp', d.whatsapp_number || '—'],
    ['Address', d.address ? escHtml(String(d.address)).replace(/\n/g, '<br>') : '—'],
    ['Role', escHtml(d.role || '—')],
    ['Status', `<span class="status-indicator">${escHtml(d.status || '—')}</span>`],
    ['Member Since', d.created_at
      ? escHtml(new Date(d.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }))
      : '—'
    ],
  ];

  const kvHTML = basicFields.map(([k, v]) => `
    <div class="k">${escHtml(String(k))}</div>
    <div class="v">${v}</div>
  `).join('');

  return `
    <section class="profile-card">
      <h5><i class="fa fa-user"></i> Basic Details</h5>
      <div class="kv">${kvHTML}</div>
    </section>
  `;
}

function renderPersonalCard(){
  const d = profileData?.personal || {};

  let qualificationHTML = '—';
  if (Array.isArray(d.qualification) && d.qualification.length){
    qualificationHTML = `
      <div class="qualification-list">
        ${d.qualification.map(q => `<span class="qualification-tag">${escHtml(q)}</span>`).join('')}
      </div>
    `;
  } else if (typeof d.qualification === 'string' && d.qualification.trim()){
    qualificationHTML = escHtml(d.qualification);
  }

  const personalItems = [
    ['Qualifications', qualificationHTML],
    ['Affiliation', formatText(d.affiliation || '—')],
    ['Specification', formatText(d.specification || '—')],
    ['Experience', formatText(d.experience || '—')],
    ['Research Interests', formatText(d.interest || '—')],
    ['Administration', formatText(d.administration || '—')],
    ['Research Projects', formatText(d.research_project || '—')],
  ];

  const kvHTML = personalItems.map(([k, v], idx) => `
    <div class="k">${escHtml(String(k))}</div>
    <div class="v">${v}</div>
    ${idx < personalItems.length - 1 ? `<div class="kv-divider" aria-hidden="true"></div>` : ``}
  `).join('');

  return `
    <section class="profile-card">
      <h5><i class="fa fa-id-card"></i> Personal Information</h5>
      <div class="kv">${kvHTML}</div>
    </section>
  `;
}

function renderPage(){
  const content = document.getElementById('dynamicContent');
  content.innerHTML = `
    <div class="content-stack">
      ${renderBasicCard()}
      ${renderPersonalCard()}
    </div>
  `;
}

async function initApp(){
  const uuid = window.location.pathname.split('/').filter(Boolean).pop();

  if (!uuid){
    showError('Invalid profile URL');
    return;
  }

  try{
    showLoading(true);

    const res = await fetch(`/api/users/${encodeURIComponent(uuid)}/profile`, {
      headers: { 'Accept': 'application/json' }
    });

    const json = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(json?.message || json?.error || 'Failed to load profile');

    profileData = json.data || {};
    initSidebar();
    renderPage();
    showLoading(false);

  } catch (e){
    console.error(e);
    showError(e.message || 'Failed to load profile');
  }
}

document.addEventListener('DOMContentLoaded', initApp);
</script>

</body>
</html>
