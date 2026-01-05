{{-- resources/views/landing/viewAnnouncements.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Announcement</title>

  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    html, body { height:100%; margin:0; }

    body{
      background: var(--bg-body);
      color: var(--ink);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      line-height: 1.6;
    }

    /* Container */
    .announcement-container{
      max-width: 1280px;
      margin: 0 auto;
      padding: clamp(24px, 4vw, 48px) clamp(16px, 3vw, 24px);
    }

    /* Header */
    .announcement-header{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
      margin-bottom: 32px;
      border-radius: 10px;
    }

    /* Title row with date pill at top-right */
    .announcement-headbar{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .announcement-title{
      margin: 0;
      font-weight: 900;
      letter-spacing: -0.03em;
      line-height: 1.1;
      font-size: clamp(28px, 5vw, 48px);
      color: var(--ink);
      flex: 1 1 520px;
      min-width: 260px;
    }

    /* Meta */
    .announcement-meta{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      align-items:center;
      margin-bottom:24px;
    }

    .meta-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:8px 16px;
      border-radius:999px;
      background: var(--surface-alt);
      border: 1px solid var(--line-strong);
      color: var(--ink);
      font-size:14px;
      font-weight:500;
      white-space:nowrap;
    }

    .meta-pill i{
      color: var(--primary-color);
      opacity: .8;
    }

    /* Date pill (same style, just placed in headbar) */
    .meta-pill-date{
      margin-left: auto;
      flex: 0 0 auto;
    }

    /* Actions */
    .announcement-actions{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      padding-top:20px;
      border-top:2px solid var(--line-light);
    }

    .action-btn{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 20px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      background: var(--surface);
      color: var(--ink);
      text-decoration:none;
      font-weight:600;
      font-size:14px;
      transition: all .3s ease;
      cursor:pointer;
    }

    .action-btn:hover{
      background: var(--primary-color);
      color:#fff;
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow-2);
    }

    .action-btn i{ font-size:16px; }

    /* Cover */
    .announcement-cover{
      margin-bottom:32px;
      border-radius: var(--radius-xl);
      overflow:hidden;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-2);
    }

    .announcement-cover img{
      width:100%;
      height:auto;
      display:block;
      max-height:500px;
      object-fit:cover;
    }

    /* Body (kept for parity if you ever want a separate block; currently content is inside header like your achievement page) */
    .announcement-body{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
      margin-bottom: 32px;
    }

    .announcement-content{
      color: var(--ink);
      font-size:16px;
      line-height:1.85;
      overflow-wrap:anywhere;
      margin-bottom:24px;
    }

    .announcement-content p{ margin:0 0 16px; }

    .announcement-content h1,
    .announcement-content h2,
    .announcement-content h3,
    .announcement-content h4{
      margin: 24px 0 12px;
      line-height: 1.3;
      letter-spacing: -0.02em;
      font-weight: 700;
      color: var(--ink);
    }

    .announcement-content h1{ font-size:2rem; }
    .announcement-content h2{ font-size:1.75rem; }
    .announcement-content h3{ font-size:1.5rem; }
    .announcement-content h4{ font-size:1.25rem; }

    .announcement-content img{
      max-width:100%;
      height:auto;
      border-radius: var(--radius-lg);
      margin: 20px 0;
      box-shadow: var(--shadow-1);
    }

    .announcement-content a{
      color: var(--primary-color);
      text-decoration: underline;
      text-underline-offset: 3px;
      transition: color .2s ease;
    }
    .announcement-content a:hover{ color: var(--accent-color); }

    .announcement-content blockquote{
      margin: 20px 0;
      padding: 16px 20px;
      border-left: 5px solid var(--primary-color);
      background: var(--surface-alt);
      border-radius: var(--radius-md);
      font-style: italic;
    }

    .announcement-content pre{
      padding:16px;
      border-radius: var(--radius-md);
      border:1px solid var(--line-strong);
      background: var(--surface-alt);
      overflow:auto;
      font-family: 'Courier New', monospace;
      font-size:14px;
    }

    .announcement-content ul,
    .announcement-content ol{
      padding-left:24px;
      margin:16px 0;
    }

    .announcement-content li{ margin-bottom:8px; }

    /* Attachments */
    .announcement-attachments{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border:1px solid var(--line-strong);
    }

    .attachments-title{
      display:flex;
      align-items:center;
      gap:12px;
      font-weight:700;
      font-size:1.25rem;
      margin:0 0 20px;
      letter-spacing:-0.01em;
      color: var(--ink);
    }

    .attachments-title i{
      background: var(--primary-light);
      color: var(--primary-color);
      width:40px;
      height:40px;
      border-radius: var(--radius-md);
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .attachments-list{ display:grid; gap:12px; }

    .attachment-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
      padding:16px 20px;
      border-radius: var(--radius-lg);
      border:1px solid var(--line-strong);
      background: var(--surface-alt);
      text-decoration:none;
      color: var(--ink);
      transition: all .3s ease;
    }

    .attachment-item:hover{
      border-color: var(--primary-color);
      background: var(--surface);
      transform: translateY(-2px);
      box-shadow: var(--shadow-2);
    }

    .attachment-left{
      display:flex;
      align-items:center;
      gap:16px;
      min-width:0;
      flex:1;
    }

    .attachment-icon{
      width:48px;
      height:48px;
      border-radius: var(--radius-md);
      background: var(--primary-light);
      color: var(--primary-color);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:20px;
      flex-shrink:0;
    }

    .attachment-info{ min-width:0; flex:1; }

    .attachment-name{
      font-weight:600;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      margin-bottom:4px;
    }

    .attachment-meta{
      font-size:13px;
      color: var(--muted-color);
    }

    .attachment-number{
      font-size:13px;
      color: var(--muted-color);
      white-space:nowrap;
      font-weight:500;
    }

    /* Loading */
    .loading-container{
      display:grid;
      gap:16px;
      max-width:100%;
      padding:40px 0;
    }

    .loading-bar{
      height:16px;
      border-radius:999px;
      background: var(--surface-alt);
      overflow:hidden;
      position:relative;
    }

    .loading-bar::after{
      content:"";
      position:absolute;
      inset:0;
      transform: translateX(-100%);
      background: linear-gradient(90deg, transparent, rgba(59,130,246,.3), transparent);
      animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer { to { transform: translateX(100%); } }

    /* Error */
    .error-container{
      background:#fee;
      border:1px solid #fcc;
      border-radius: var(--radius-lg);
      padding:24px;
      color:#c00;
      line-height:1.6;
      margin:40px 0;
    }

    .error-container i{
      font-size:24px;
      margin-bottom:12px;
      display:block;
    }

    /* Responsive */
    @media (max-width:768px){
      .announcement-meta{ gap:8px; }
      .meta-pill{ font-size:13px; padding:6px 12px; }
      .action-btn{ font-size:13px; padding:8px 16px; }
      .attachment-item{ padding:12px 16px; }
      .attachment-icon{ width:40px; height:40px; font-size:18px; }
      .announcement-headbar{ gap:10px; }
    }
  </style>
</head>

<body>
  {{-- Same includes as your Achievement page --}}
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <main class="announcement-container">
    <!-- Header -->
    <header class="announcement-header">
      <div class="announcement-headbar">
        <h1 class="announcement-title" id="announcementTitle">Announcement</h1>

        <!-- ✅ Date pill moved to top-right -->
        <span class="meta-pill meta-pill-date" id="metaDate" style="display:none">
          <i class="fa-regular fa-calendar"></i>
          <span></span>
        </span>
      </div>

      <div class="announcement-meta" id="announcementMeta" style="display:none">
        <span class="meta-pill" id="metaDept" style="display:none">
          <i class="fa-solid fa-building-columns"></i>
          <span></span>
        </span>

        <!-- ✅ Views pill removed -->

        <span class="meta-pill" id="metaFeatured" style="display:none">
          <i class="fa-solid fa-star"></i>
          <span>Featured</span>
        </span>
      </div>

      <!-- Content inside header (same as Achievement page) -->
      <article id="announcementContent" class="announcement-content" style="display:none"></article>

      <div class="announcement-actions">
        <button class="action-btn" id="copyLinkBtn">
          <i class="fa-solid fa-link"></i>
          Copy Link
        </button>
        <button class="action-btn" id="shareBtn" style="display:none">
          <i class="fa-solid fa-share-nodes"></i>
          Share
        </button>
        <!-- ✅ Print button removed -->
      </div>
    </header>

    <!-- Loading -->
    <section id="loadingSection" class="loading-container" aria-live="polite">
      <div class="loading-bar" style="width:65%"></div>
      <div class="loading-bar" style="width:92%"></div>
      <div class="loading-bar" style="width:78%"></div>
      <div class="loading-bar" style="width:85%"></div>
      <div class="loading-bar" style="width:58%"></div>
    </section>

    <!-- Error -->
    <div id="errorSection" class="error-container" style="display:none">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <div id="errorMessage"></div>
    </div>

    <!-- Cover -->
    <figure id="coverSection" class="announcement-cover" style="display:none">
      <img id="coverImage" alt="Cover image" loading="lazy"/>
    </figure>

    <!-- Attachments -->
    <section class="announcement-attachments" id="attachmentsSection" style="display:none">
      <h3 class="attachments-title">
        <i class="fa-solid fa-paperclip"></i>
        Attachments
      </h3>
      <div class="attachments-list" id="attachmentsList"></div>
    </section>
  </main>

  <script>
    (function () {
      const $ = (id) => document.getElementById(id);

      // identifier = last segment of URL by default
      function getIdentifierFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        return parts[parts.length - 1] || '';
      }

      // optional: if you ever mount this view at /departments/{dept}/announcements/{identifier}
      function getDepartmentFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        const deptIdx = parts.indexOf('departments');
        if (deptIdx !== -1 && parts[deptIdx + 1]) return parts[deptIdx + 1];
        return '';
      }

      // find announcement object in response
      function findAnnouncementObject(payload) {
        if (!payload) return null;

        if (payload.data && typeof payload.data === 'object') return payload.data;
        if (payload.announcement && typeof payload.announcement === 'object') return payload.announcement;
        if (payload.item && typeof payload.item === 'object') return payload.item;
        if (payload.data && payload.data.data && typeof payload.data.data === 'object') return payload.data.data;
        if (typeof payload === 'object' && (payload.title || payload.body || payload.uuid || payload.slug)) return payload;
        if (Array.isArray(payload) && payload.length && typeof payload[0] === 'object') return payload[0];

        return null;
      }

      function safeJson(v) {
        try {
          if (v == null) return null;
          if (typeof v === 'object') return v;
          const s = String(v).trim();
          if (!s) return null;
          return JSON.parse(s);
        } catch (e) {
          return null;
        }
      }

      function resolveUrl(path) {
        if (!path) return '';
        const p = String(path).trim();
        if (!p) return '';
        if (/^https?:\/\//i.test(p)) return p;
        return window.location.origin + '/' + p.replace(/^\/+/, '');
      }

      function formatDate(v) {
        if (!v) return '';
        const d = new Date(v);
        if (isNaN(d.getTime())) return String(v);
        return d.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
      }

      function setLoading(show) {
        $('loadingSection').style.display = show ? '' : 'none';
      }

      function setError(msg) {
        $('errorSection').style.display = msg ? '' : 'none';
        $('errorMessage').textContent = msg || '';
      }

      function renderAttachments(attachments_json) {
        const list = $('attachmentsList');
        list.innerHTML = '';

        const parsed = safeJson(attachments_json);
        const arr = Array.isArray(parsed)
          ? parsed
          : (parsed && Array.isArray(parsed.files) ? parsed.files : null);

        if (!arr || !arr.length) {
          $('attachmentsSection').style.display = 'none';
          return;
        }

        arr.forEach((item, idx) => {
          let url = '', name = '', meta = '';

          if (typeof item === 'string') {
            url = resolveUrl(item);
            name = item.split('/').pop() || `Attachment ${idx + 1}`;
          } else if (item && typeof item === 'object') {
            url = resolveUrl(item.url || item.path || item.file || '');
            name = item.name || (url ? url.split('/').pop() : `Attachment ${idx + 1}`);
            meta = item.type || item.mime || '';
            if (item.size) meta = meta ? `${meta} • ${item.size}` : String(item.size);
          }

          if (!url) return;

          const a = document.createElement('a');
          a.className = 'attachment-item';
          a.href = url;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';

          a.innerHTML = `
            <div class="attachment-left">
              <div class="attachment-icon">
                <i class="fa-solid fa-file-arrow-down"></i>
              </div>
              <div class="attachment-info">
                <div class="attachment-name" title="${String(name).replace(/"/g, '&quot;')}">${name}</div>
                <div class="attachment-meta">${meta || 'Click to open'}</div>
              </div>
            </div>
            <div class="attachment-number">#${idx + 1}</div>
          `;

          list.appendChild(a);
        });

        $('attachmentsSection').style.display = list.children.length ? '' : 'none';
      }

      function renderPage(an) {
        const title = an.title || 'Announcement';
        $('announcementTitle').textContent = title;
        document.title = title;

        // ✅ Date pill now independent (top-right)
        const date = formatDate(an.publish_at || an.created_at || an.updated_at);
        if (date) {
          $('metaDate').style.display = '';
          $('metaDate').querySelector('span').textContent = date;
        } else {
          $('metaDate').style.display = 'none';
          $('metaDate').querySelector('span').textContent = '';
        }

        // ✅ Meta row (dept + featured only)
        let hasMeta = false;

        // Department
        const dept =
          (an.department && (an.department.name || an.department.title))
            ? (an.department.name || an.department.title)
            : (an.department_name || '');

        if (dept) {
          $('metaDept').style.display = '';
          $('metaDept').querySelector('span').textContent = dept;
          hasMeta = true;
        } else {
          $('metaDept').style.display = 'none';
          $('metaDept').querySelector('span').textContent = '';
        }

        // Featured
        const featured = (an.is_featured_home === 1 || an.is_featured_home === true || String(an.is_featured_home) === '1');
        $('metaFeatured').style.display = featured ? '' : 'none';
        if (featured) hasMeta = true;

        $('announcementMeta').style.display = hasMeta ? '' : 'none';

        // Body
        $('announcementContent').innerHTML = an.body || '';
        $('announcementContent').style.display = '';

        // Cover
        const cover = resolveUrl(an.cover_image);
        if (cover) {
          $('coverSection').style.display = '';
          $('coverImage').src = cover;
        } else {
          $('coverSection').style.display = 'none';
          $('coverImage').removeAttribute('src');
        }

        // Attachments
        renderAttachments(an.attachments_json);

        // Share button
        if (navigator.share) $('shareBtn').style.display = '';
      }

      async function fetchJson(url) {
        const res = await fetch(url, { method:'GET', headers:{ 'Accept':'application/json' } });
        let data = null;
        try { data = await res.json(); } catch (e) {}
        return { res, data };
      }

      async function load() {
        const identifier = getIdentifierFromUrl();
        if (!identifier) {
          setLoading(false);
          setError('No identifier found in the URL.');
          return;
        }

        setLoading(true);
        setError('');

        // public show endpoints:
        const candidates = [
          `/api/public/announcements/${encodeURIComponent(identifier)}`,
          `/public/announcements/${encodeURIComponent(identifier)}`,
          `/api/announcements/${encodeURIComponent(identifier)}`
        ];

        // optional dept-aware candidates
        const dept = getDepartmentFromUrl();
        if (dept) {
          candidates.unshift(`/api/public/departments/${encodeURIComponent(dept)}/announcements/${encodeURIComponent(identifier)}`);
          candidates.unshift(`/api/departments/${encodeURIComponent(dept)}/announcements/${encodeURIComponent(identifier)}`);
        }

        for (const url of candidates) {
          try {
            const { res, data } = await fetchJson(url);
            if (!res || !res.ok) continue;

            const an = findAnnouncementObject(data);
            if (an && (an.title || an.body)) {
              setLoading(false);
              renderPage(an);
              return;
            }
          } catch (e) {
            // try next
          }
        }

        setLoading(false);
        setError('Announcement not found or API endpoint is not reachable. Please verify your public show route URL (expected: /api/public/announcements/{identifier}).');
      }

      // Copy link
      $('copyLinkBtn').addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(window.location.href);
          const btn = $('copyLinkBtn');
          const originalHTML = btn.innerHTML;
          btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
          setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
        } catch (e) {
          alert('Copy failed. Please copy the URL from the address bar.');
        }
      });

      // Share
      $('shareBtn').addEventListener('click', async () => {
        try {
          await navigator.share({ title: document.title, url: window.location.href });
        } catch (e) {
          console.log('Share cancelled or failed');
        }
      });

      // ✅ Print code removed

      // Init
      load();
    })();
  </script>
</body>
</html>
