<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Profile - Dr. Anirban Mukherjee</title>

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

/* ===== Sidebar ===== */
.profile-sidebar {
  background: var(--surface);
  border-radius: var(--radius-xl);
  padding: 24px;
  box-shadow: var(--shadow-2);
  border: 1px solid var(--line-strong);
  position: sticky;
  top: 24px;
  height: fit-content;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  overflow-x: hidden;
  scroll-behavior: smooth;
  padding-bottom: 44px; /* room for scroll hint */
}

/* subtle scrollbar */
.profile-sidebar::-webkit-scrollbar { width: 8px; }
.profile-sidebar::-webkit-scrollbar-thumb {
  background: rgba(100,116,139,.35);
  border-radius: 10px;
  border: 2px solid transparent;
  background-clip: content-box;
}
.profile-sidebar::-webkit-scrollbar-track { background: transparent; }

/* Scroll hint (down arrow) */
.scroll-hint {
  position: sticky;
  bottom: 10px;
  left: 0;
  right: 0;
  margin-top: 14px;
  display: none; /* toggled by JS */
  justify-content: center;
  pointer-events: none;
  z-index: 5;
}
.scroll-hint .hint-pill{
  pointer-events: none;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 999px;
  background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.75));
  border: 1px solid rgba(226,232,240,.9);
  box-shadow: 0 10px 24px rgba(0,0,0,.10);
  color: var(--muted-color);
  font-size: 12.5px;
  backdrop-filter: blur(8px);
}
html.theme-dark .scroll-hint .hint-pill{
  background: linear-gradient(180deg, rgba(30,41,59,.92), rgba(30,41,59,.72));
  border-color: rgba(148,163,184,.25);
  color: rgba(226,232,240,.85);
}
.scroll-hint i{
  animation: bounceDown 1.2s infinite;
  font-size: 14px;
}
@keyframes bounceDown{
  0%,100%{ transform: translateY(0); opacity: .85; }
  50%{ transform: translateY(4px); opacity: 1; }
}

.profile-avatar-container { position: relative; width: 140px; height: 140px; margin: 0 auto 20px; }
.profile-avatar {
  width: 100%; height: 100%;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: linear-gradient(135deg, var(--primary-light), #e0f2fe);
  display: flex; align-items: center; justify-content: center;
  font-size: 48px; color: var(--primary-color);
  border: 4px solid white;
  box-shadow: var(--shadow-3);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }

.profile-badge {
  position: absolute;
  bottom: -5px; right: -5px;
  background: var(--primary-color);
  color: white;
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  border: 3px solid white;
}

.profile-name { font-weight: 700; font-size: 1.5rem; text-align: center; margin-bottom: 4px; word-break: break-word; }
.profile-role {
  font-size: 0.9rem;
  color: var(--primary-color);
  text-align: center;
  font-weight: 600;
  background: var(--primary-light);
  padding: 4px 12px;
  border-radius: 20px;
  display: inline-block;
  margin: 0 auto 16px;
}

.profile-contact {
  background: var(--surface-alt);
  padding: 16px;
  border-radius: var(--radius-md);
  margin-bottom: 20px;
}
.contact-item { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 0.9rem; }
.contact-item:last-child { margin-bottom: 0; }
.contact-item i { color: var(--primary-color); width: 20px; }

.profile-social { display: flex; justify-content: center; gap: 12px; margin: 20px 0; }
.profile-social a {
  width: 44px; height: 44px;
  border-radius: var(--radius-md);
  background: var(--surface-alt);
  display: flex; align-items: center; justify-content: center;
  color: var(--ink);
  transition: all 0.3s ease;
  border: 1px solid var(--line-strong);
}
.profile-social a:hover { background: var(--primary-color); color: white; transform: translateY(-3px); box-shadow: var(--shadow-3); }

/* Nav */
.profile-nav { margin-top: 24px; display: grid; gap: 8px; }
.profile-nav button {
  border: none;
  background: transparent;
  text-align: left;
  padding: 12px 16px;
  border-radius: var(--radius-md);
  color: var(--ink);
  font-size: 0.95rem;
  display: flex; align-items: center; gap: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
}
.profile-nav button i { width: 20px; color: var(--muted-color); }
.profile-nav button:hover { background: var(--primary-light); color: var(--primary-color); transform: translateX(5px); }
.profile-nav button.active { background: var(--primary-color); color: white; }
.profile-nav button.active i { color: white; }

/* ===== Content Area ===== */
.profile-content { position: relative; min-height: 600px; }

/* Loading Indicator */
.loading-indicator {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: var(--muted-color);
}
.loading-spinner {
  width: 40px; height: 40px;
  border: 3px solid var(--line-strong);
  border-top-color: var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ===== Section Styles ===== */
.profile-section { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.profile-card {
  background: var(--surface);
  border-radius: var(--radius-xl);
  padding: 28px;
  box-shadow: var(--shadow-2);
  border: 1px solid var(--line-strong);
}

.profile-card h5 {
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid var(--line-light);
  color: var(--primary-color);
}
.profile-card h5 i {
  background: var(--primary-light);
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

/* ✅ Smooth separators inside Personal Information (and any section that uses it) */
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
  margin: 6px 0 6px;
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

/* ===== KV ===== */
.kv {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 16px 24px;
  font-size: 0.95rem;
}
.kv .k { color: var(--muted-color); font-weight: 500; }
.kv .v { font-weight: 400; line-height: 1.7; }
.kv .v ul { padding-left: 20px; margin: 8px 0; }
.kv .v li { margin-bottom: 4px; }

@media (max-width: 768px) {
  .kv { grid-template-columns: 1fr; gap: 12px; }
  .kv .k { font-weight: 600; color: var(--ink); }
}

/* ===== Content Cards ===== */
.content-grid { display: grid; gap: 20px; }
.content-card {
  background: var(--surface);
  border: 1px solid var(--line-strong);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 20px;
  transition: all 0.3s ease;
}
.content-card:hover { border-color: var(--primary-color); box-shadow: var(--shadow-2); }

.card-image {
  width: 100px; height: 120px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--surface-alt);
  display: flex; align-items: center; justify-content: center;
  color: var(--muted-color);
  font-size: 32px;
}
.card-image img { width: 100%; height: 100%; object-fit: cover; }

.card-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 8px; color: var(--ink); }
.card-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.85rem; color: var(--muted-color); margin-bottom: 12px; }
.card-meta-item { display: flex; align-items: center; gap: 6px; }
.card-desc { font-size: 0.95rem; line-height: 1.6; color: var(--ink); }

.card-badge {
  background: var(--primary-light);
  color: var(--primary-color);
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-block;
  margin-top: 10px;
}
.card-link { margin-top: 12px; }
.card-link a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.card-link a:hover { text-decoration: underline; }

@media (max-width: 768px) {
  .content-card { grid-template-columns: 1fr; gap: 16px; }
  .card-image { width: 100%; height: 180px; }
}

/* ===== Tags ===== */
.tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
.tag { background: var(--surface-alt); color: var(--muted-color); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; border: 1px solid var(--line-strong); }

/* ===== Empty State ===== */
.empty { color: var(--muted-color); text-align: center; padding: 40px 20px; font-size: 1rem; }
.empty i { font-size: 2rem; margin-bottom: 16px; display: block; color: var(--line-strong); }

/* ===== Status Indicator ===== */
.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  padding: 4px 12px;
  border-radius: 20px;
  background: #dcfce7;
  color: #166534;
  margin-left: 12px;
}
.status-indicator::before {
  content: '';
  width: 8px; height: 8px;
  background: #22c55e;
  border-radius: 50%;
}

/* ===== Qualification List ===== */
.qualification-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.qualification-tag {
  background: var(--primary-light);
  color: var(--primary-color);
  padding: 6px 14px;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  font-weight: 500;
}

/* ===== Active Section Indicator ===== */
.section-indicator {
  position: fixed;
  bottom: 20px; right: 20px;
  background: var(--primary-color);
  color: white;
  padding: 8px 16px;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  box-shadow: var(--shadow-3);
  z-index: 100;
  display: none;
}
@media (max-width: 768px) {
  .section-indicator { bottom: 10px; right: 10px; font-size: 0.8rem; }
}
</style>
</head>

<body>

    @include('modules.header.header')

<div class="profile-layout">

<!-- ================= SIDEBAR ================= -->
<aside class="profile-sidebar" id="profileSidebar">
  <div class="profile-avatar-container">
    <div class="profile-avatar" id="avatar">
      <i class="fa fa-user-graduate"></i>
    </div>
    <div class="profile-badge">
      <i class="fa fa-check"></i>
    </div>
  </div>

  <div class="profile-name" id="name">—</div>
  <div class="profile-role" id="role">—</div>

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

  <div class="profile-nav" id="profileNav">
    <button class="active" data-target="basic" data-section="basic">
      <i class="fa fa-user"></i> Basic Details
    </button>
    <button data-target="personal" data-section="personal">
      <i class="fa fa-id-card"></i> Personal
    </button>
    <button data-target="education" data-section="education">
      <i class="fa fa-graduation-cap"></i> Education
    </button>
    <button data-target="honors" data-section="honors">
      <i class="fa fa-award"></i> Honors
    </button>
    <button data-target="journals" data-section="journals">
      <i class="fa fa-book"></i> Journals
    </button>
    <button data-target="conferences" data-section="conferences">
      <i class="fa fa-microphone"></i> Conferences
    </button>
    <button data-target="teaching" data-section="teaching">
      <i class="fa fa-chalkboard-teacher"></i> Teaching
    </button>
  </div>

  <!-- ✅ Scroll indicator -->
  <div class="scroll-hint" id="scrollHint" aria-hidden="true">
    <div class="hint-pill">

      <i class="fa fa-arrow-down"></i>
    </div>
  </div>
</aside>

<!-- ================= CONTENT AREA ================= -->
<main class="profile-content" id="contentArea">
  <!-- Loading indicator -->
  <div class="loading-indicator" id="loadingIndicator">
    <div class="loading-spinner"></div>
    <div>Loading section...</div>
  </div>

  <!-- Content will be dynamically loaded here -->
  <div id="dynamicContent"></div>
</main>

</div>

<!-- Active section indicator -->
<div class="section-indicator" id="sectionIndicator">
  Viewing: <span id="currentSectionName">Basic Details</span>
</div>

<script>
// Global variables
let profileData = null;
let currentSection = 'basic';
let isLoading = false;

// Section configuration
const sections = {
  basic: { title: 'Basic Details', icon: 'fa-user', render: renderBasicSection },
  personal: { title: 'Personal Information', icon: 'fa-id-card', render: renderPersonalSection },
  education: { title: 'Education', icon: 'fa-graduation-cap', render: renderEducationSection },
  honors: { title: 'Honors & Awards', icon: 'fa-award', render: renderHonorsSection },
  journals: { title: 'Journal Publications', icon: 'fa-book', render: renderJournalsSection },
  conferences: { title: 'Conference Publications', icon: 'fa-microphone', render: renderConferencesSection },
  teaching: { title: 'Teaching Engagements', icon: 'fa-chalkboard-teacher', render: renderTeachingSection }
};

// Initialize application
async function initApp() {
  const uuid = location.pathname.split('/').pop();

  try {
    showLoading(true);

    const res = await fetch(`/api/users/${uuid}/profile`);
    const json = await res.json();
    profileData = json.data || {};

    initSidebar();

    await loadSection('basic');

    setupNavigation();

    // ✅ setup sidebar scroll hint
    setupSidebarScrollHint();

  } catch (error) {
    console.error('Error loading profile:', error);
    showError('Failed to load profile data');
  } finally {
    showLoading(false);
  }
}

// Initialize sidebar
function initSidebar() {
  const d = profileData.basic || {};

  document.getElementById('name').textContent = d.name || '—';
  document.getElementById('role').textContent = (d.role || '').toUpperCase() || '—';
  document.getElementById('email').textContent = d.email || '—';
  document.getElementById('phone').textContent = d.phone_number || '—';
  document.getElementById('address').textContent = d.address?.replace(/\n/g, ', ') || '—';

  const avatar = document.getElementById('avatar');
  if (d.image) {
    avatar.innerHTML = `<img src="${d.image}" alt="avatar">`;
  } else {
    avatar.innerHTML = `<i class="fa fa-user-graduate"></i>`;
  }

  const socialIconsMap = {
    'linkedin': 'fa-brands fa-linkedin',
    'github': 'fa-brands fa-github',
    'orcid': 'fa-brands fa-orcid',
    'google scholar': 'fa fa-graduation-cap',
    'researchgate': 'fa-brands fa-researchgate',
    'twitter': 'fa-brands fa-twitter',
    'facebook': 'fa-brands fa-facebook'
  };

  const socialIcons = document.getElementById('socialIcons');
  socialIcons.innerHTML = '';

  (profileData.social_media || []).forEach(s => {
    const platform = (s.platform || '').toLowerCase();
    const iconClass = socialIconsMap[platform] || 'fa fa-link';
    if (!s.link) return;

    socialIcons.insertAdjacentHTML('beforeend', `
      <a href="${s.link}" target="_blank" title="${s.platform || 'Link'}" rel="noopener noreferrer">
        <i class="${iconClass}"></i>
      </a>
    `);
  });
}

// Setup navigation
function setupNavigation() {
  document.querySelectorAll('.profile-nav button').forEach(button => {
    button.addEventListener('click', async () => {
      if (isLoading) return;

      const sectionId = button.dataset.section;
      if (sectionId === currentSection) return;

      document.querySelectorAll('.profile-nav button').forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');

      await loadSection(sectionId);

      history.pushState({ section: sectionId }, '', `#${sectionId}`);
    });
  });

  window.addEventListener('popstate', async (event) => {
    if (event.state && event.state.section) {
      // sync active button
      const btn = document.querySelector(`.profile-nav button[data-section="${event.state.section}"]`);
      if (btn) {
        document.querySelectorAll('.profile-nav button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      }
      await loadSection(event.state.section);
    }
  });

  if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    if (sections[hash]) {
      const button = document.querySelector(`.profile-nav button[data-section="${hash}"]`);
      if (button) button.click();
    }
  }
}

// ✅ Sidebar scroll hint logic
function setupSidebarScrollHint(){
  const sidebar = document.getElementById('profileSidebar');
  const hint = document.getElementById('scrollHint');
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

  // initial (after layout)
  requestAnimationFrame(updateHint);
  setTimeout(updateHint, 250);

  sidebar.addEventListener('scroll', updateHint, { passive:true });
  window.addEventListener('resize', updateHint);

  // also update when content changes (e.g., different fonts/images load)
  const mo = new MutationObserver(() => setTimeout(updateHint, 60));
  mo.observe(sidebar, { childList:true, subtree:true });

  // image load might change height
  sidebar.querySelectorAll('img').forEach(img => img.addEventListener('load', updateHint));
}

// Load section dynamically
async function loadSection(sectionId) {
  if (isLoading || !sections[sectionId]) return;

  try {
    isLoading = true;
    currentSection = sectionId;

    showLoading(true);

    updateSectionIndicator(sections[sectionId].title);

    const dynamicContent = document.getElementById('dynamicContent');
    dynamicContent.innerHTML = '';

    // keep your UX delay
    await new Promise(resolve => setTimeout(resolve, 300));

    const sectionHTML = sections[sectionId].render();
    dynamicContent.innerHTML = sectionHTML;

    showLoading(false);

  } catch (error) {
    console.error(`Error loading section ${sectionId}:`, error);
    showError('Failed to load section');
    showLoading(false);
  } finally {
    isLoading = false;
  }
}

// Show/hide loading indicator
function showLoading(show) {
  const loadingIndicator = document.getElementById('loadingIndicator');
  const dynamicContent = document.getElementById('dynamicContent');

  if (show) {
    loadingIndicator.style.display = 'block';
    dynamicContent.style.display = 'none';
  } else {
    loadingIndicator.style.display = 'none';
    dynamicContent.style.display = 'block';
  }
}

// Show error message
function showError(message) {
  const dynamicContent = document.getElementById('dynamicContent');
  dynamicContent.innerHTML = `
    <div class="profile-card">
      <div class="empty">
        <i class="fa fa-exclamation-triangle"></i>
        <div>${message}</div>
      </div>
    </div>
  `;
}

// Update section indicator
function updateSectionIndicator(sectionName) {
  const indicator = document.getElementById('sectionIndicator');
  const sectionNameEl = document.getElementById('currentSectionName');

  sectionNameEl.textContent = sectionName;
  indicator.style.display = 'block';

  setTimeout(() => { indicator.style.display = 'none'; }, 3000);
}

// Helper function to format text
function formatText(text) {
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

// ===== SECTION RENDERERS =====

function renderBasicSection() {
  const d = profileData.basic || {};

  const basicFields = {
    'Email': d.email || '—',
    'Phone': d.phone_number || '—',
    'Alternative Email': d.alternative_email || '—',
    'Alternative Phone': d.alternative_phone_number || '—',
    'WhatsApp': d.whatsapp_number || '—',
    'Address': d.address?.replace(/\n/g, '<br>') || '—',
    'Role': d.role || '—',
    'Status': `<span class="status-indicator">${d.status || '—'}</span>`,
    'Member Since': d.created_at ?
      new Date(d.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'
  };

  const kvHTML = Object.entries(basicFields)
    .map(([k, v]) => `
      <div class="k">${k.replace(/_/g, ' ').toUpperCase()}</div>
      <div class="v">${v}</div>
    `).join('');

  return `
    <section id="basic" class="profile-card profile-section">
      <h5><i class="fa fa-user"></i> Basic Details</h5>
      <div class="kv">${kvHTML}</div>
    </section>
  `;
}

function renderPersonalSection() {
  const d = profileData.personal || {};

  // ✅ order + separators for readability
  const personalItems = [
    ['Qualifications', d.qualification ?
      `<div class="qualification-list">
        ${d.qualification.map(q => `<span class="qualification-tag">${q}</span>`).join('')}
      </div>` : '—'
    ],
    ['Affiliation', formatText(d.affiliation || '—')],
    ['Specification', formatText(d.specification || '—')],
    ['Experience', formatText(d.experience || '—')],
    ['Research Interests', formatText(d.interest || '—')],
    ['Administration', formatText(d.administration || '—')],
    ['Research Projects', formatText(d.research_project || '—')]
  ];

  const kvHTML = personalItems.map(([k, v], idx) => `
      <div class="k">${String(k).replace(/_/g, ' ').toUpperCase()}</div>
      <div class="v">${v}</div>
      ${idx < personalItems.length - 1 ? `<div class="kv-divider" aria-hidden="true"></div>` : ``}
  `).join('');

  return `
    <section id="personal" class="profile-card profile-section">
      <h5><i class="fa fa-id-card"></i> Personal Information</h5>
      <div class="kv">${kvHTML}</div>
    </section>
  `;
}

function renderEducationSection() {
  const educations = profileData.educations || [];

  if (!educations.length) {
    return `
      <section id="education" class="profile-card profile-section">
        <h5><i class="fa fa-graduation-cap"></i> Education</h5>
        <div class="empty">
          <i class="fa fa-graduation-cap"></i>
          No education records found
        </div>
      </section>
    `;
  }

  const educationHTML = educations.map(edu => `
    <div class="content-card">
      <div class="card-image">
        <i class="fa fa-university"></i>
      </div>
      <div class="card-content">
        <div class="card-title">${edu.degree_title || edu.education_level || '—'}</div>
        <div class="card-meta">
          <div class="card-meta-item">
            <i class="fa fa-university"></i>
            <span>${edu.institution_name || edu.university_name || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-map-marker-alt"></i>
            <span>${edu.location || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-calendar"></i>
            <span>${edu.passing_year || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-chart-line"></i>
            <span>${edu.grade_type || 'Grade'}: ${edu.grade_value || '—'}</span>
          </div>
        </div>
        ${edu.field_of_study ? `<div class="card-badge">${edu.field_of_study}</div>` : ''}
        ${edu.description ? `<div class="card-desc">${edu.description}</div>` : ''}
      </div>
    </div>
  `).join('');

  return `
    <section id="education" class="profile-card profile-section">
      <h5><i class="fa fa-graduation-cap"></i> Education</h5>
      <div class="content-grid">${educationHTML}</div>
    </section>
  `;
}

function renderHonorsSection() {
  const honors = profileData.honors || [];

  if (!honors.length) {
    return `
      <section id="honors" class="profile-card profile-section">
        <h5><i class="fa fa-award"></i> Honors & Awards</h5>
        <div class="empty">
          <i class="fa fa-award"></i>
          No honors records found
        </div>
      </section>
    `;
  }

  const honorsHTML = honors.map(honor => `
    <div class="content-card">
      <div class="card-image">
        ${honor.image ? `<img src="${honor.image}" alt="${honor.title || 'Honor'}" loading="lazy">` : '<i class="fa fa-award"></i>'}
      </div>
      <div class="card-content">
        <div class="card-title">${honor.title || '—'}</div>
        <div class="card-meta">
          <div class="card-meta-item">
            <i class="fa fa-building"></i>
            <span>${honor.honouring_organization || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-calendar"></i>
            <span>${honor.honor_year || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-tag"></i>
            <span>${honor.honor_type || 'Award'}</span>
          </div>
        </div>
        ${honor.description ? `<div class="card-desc">${honor.description}</div>` : ''}
      </div>
    </div>
  `).join('');

  return `
    <section id="honors" class="profile-card profile-section">
      <h5><i class="fa fa-award"></i> Honors & Awards</h5>
      <div class="content-grid">${honorsHTML}</div>
    </section>
  `;
}

function renderJournalsSection() {
  const journals = profileData.journals || [];

  if (!journals.length) {
    return `
      <section id="journals" class="profile-card profile-section">
        <h5><i class="fa fa-book"></i> Journal Publications</h5>
        <div class="empty">
          <i class="fa fa-book"></i>
          No journal publications found
        </div>
      </section>
    `;
  }

  const journalsHTML = journals.map(journal => `
    <div class="content-card">
      <div class="card-image">
        ${journal.image ? `<img src="${journal.image}" alt="${journal.title || 'Journal'}" loading="lazy">` : '<i class="fa fa-newspaper"></i>'}
      </div>
      <div class="card-content">
        <div class="card-title">${journal.title || '—'}</div>
        <div class="card-meta">
          <div class="card-meta-item">
            <i class="fa fa-building"></i>
            <span>${journal.publication_organization || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-calendar"></i>
            <span>${journal.publication_year || '—'}</span>
          </div>
        </div>
        ${journal.description ? `<div class="card-desc">${journal.description}</div>` : ''}
        ${journal.url ? `
          <div class="card-link">
            <a href="${journal.url}" target="_blank" rel="noopener noreferrer">
              <i class="fa fa-external-link-alt"></i> View Publication
            </a>
          </div>
        ` : ''}
      </div>
    </div>
  `).join('');

  return `
    <section id="journals" class="profile-card profile-section">
      <h5><i class="fa fa-book"></i> Journal Publications</h5>
      <div class="content-grid">${journalsHTML}</div>
    </section>
  `;
}

function renderConferencesSection() {
  const conferences = profileData.conference_publications || [];

  if (!conferences.length) {
    return `
      <section id="conferences" class="profile-card profile-section">
        <h5><i class="fa fa-microphone"></i> Conference Publications</h5>
        <div class="empty">
          <i class="fa fa-microphone"></i>
          No conference publications found
        </div>
      </section>
    `;
  }

  const conferencesHTML = conferences.map(conf => `
    <div class="content-card">
      <div class="card-image">
        ${conf.image ? `<img src="${conf.image}" alt="${conf.title || 'Conference'}" loading="lazy">` : '<i class="fa fa-microphone-alt"></i>'}
      </div>
      <div class="card-content">
        <div class="card-title">${conf.title || '—'}</div>
        <div class="card-meta">
          <div class="card-meta-item">
            <i class="fa fa-calendar"></i>
            <span>${conf.publication_year || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-map-marker-alt"></i>
            <span>${conf.location || '—'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-tag"></i>
            <span>${conf.publication_type || 'Paper'}</span>
          </div>
          <div class="card-meta-item">
            <i class="fa fa-building"></i>
            <span>${conf.conference_name || '—'}</span>
          </div>
        </div>
        ${conf.domain ? `<div class="card-badge">${conf.domain}</div>` : ''}
        ${conf.description ? `<div class="card-desc">${conf.description}</div>` : ''}
        ${conf.url ? `
          <div class="card-link">
            <a href="${conf.url}" target="_blank" rel="noopener noreferrer">
              <i class="fa fa-external-link-alt"></i> View Details
            </a>
          </div>
        ` : ''}
      </div>
    </div>
  `).join('');

  return `
    <section id="conferences" class="profile-card profile-section">
      <h5><i class="fa fa-microphone"></i> Conference Publications</h5>
      <div class="content-grid">${conferencesHTML}</div>
    </section>
  `;
}

function renderTeachingSection() {
  const teaching = profileData.teaching_engagements || [];

  if (!teaching.length) {
    return `
      <section id="teaching" class="profile-card profile-section">
        <h5><i class="fa fa-chalkboard-teacher"></i> Teaching Engagements</h5>
        <div class="empty">
          <i class="fa fa-chalkboard-teacher"></i>
          No teaching engagements found
        </div>
      </section>
    `;
  }

  const teachingHTML = teaching.map(teach => `
    <div class="content-card">
      <div class="card-image">
        <i class="fa fa-chalkboard-teacher"></i>
      </div>
      <div class="card-content">
        <div class="card-title">${teach.organization_name || '—'}</div>
        <div class="card-meta">
          <div class="card-meta-item">
            <i class="fa fa-tag"></i>
            <span>${teach.domain || '—'}</span>
          </div>
        </div>
        ${teach.description ? `<div class="card-desc">${teach.description}</div>` : ''}
      </div>
    </div>
  `).join('');

  return `
    <section id="teaching" class="profile-card profile-section">
      <h5><i class="fa fa-chalkboard-teacher"></i> Teaching Engagements</h5>
      <div class="content-grid">${teachingHTML}</div>
    </section>
  `;
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', initApp);
</script>

</body>
</html>
