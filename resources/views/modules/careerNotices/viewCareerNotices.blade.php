{{-- resources/views/landing/viewCareerNotices.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Career Notice</title>

  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    html, body { height: 100%; margin: 0; }

    body{
      background: var(--bg-body);
      color: var(--ink);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      line-height: 1.6;
    }

    /* Container */
    .cn-container{
      max-width: 1280px;
      margin: 0 auto;
      padding: clamp(24px, 4vw, 48px) clamp(16px, 3vw, 24px);
    }

    /* Header */
    .cn-header{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
      margin-bottom: 32px;
      border-radius: 10px;
    }

    /* Title row with date pill at top-right */
    .cn-headbar{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .cn-title{
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
    .cn-meta{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      align-items:center;
      margin-bottom: 24px;
    }

    .meta-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 16px;
      border-radius: 999px;
      background: var(--surface-alt);
      border: 1px solid var(--line-strong);
      color: var(--ink);
      font-size: 14px;
      font-weight: 500;
      white-space: nowrap;
      max-width: 100%;
    }

    .meta-pill i{
      color: var(--primary-color);
      opacity: .8;
    }

    /* Date pill (top-right) */
    .meta-pill-date{
      margin-left: auto;
      flex: 0 0 auto;
    }

    /* Actions */
    .cn-actions{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      padding-top: 20px;
      border-top: 2px solid var(--line-light);
    }

    .action-btn{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 10px 20px;
      border-radius: 999px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      color: var(--ink);
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: all .3s ease;
      cursor: pointer;
    }

    .action-btn:hover{
      background: var(--primary-color);
      color: #fff;
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow-2);
    }

    .action-btn i{ font-size: 16px; }

    /* Cover */
    .cn-cover{
      margin-bottom: 32px;
      border-radius: var(--radius-xl);
      overflow: hidden;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-2);
    }

    .cn-cover img{
      width: 100%;
      height: auto;
      display: block;
      max-height: 500px;
      object-fit: cover;
    }

    /* Content */
    .cn-content{
      color: var(--ink);
      font-size: 16px;
      line-height: 1.85;
      overflow-wrap: anywhere;
      margin-bottom: 24px;
    }

    .cn-content p{ margin: 0 0 16px; }

    .cn-content h1,
    .cn-content h2,
    .cn-content h3,
    .cn-content h4{
      margin: 24px 0 12px;
      line-height: 1.3;
      letter-spacing: -0.02em;
      font-weight: 700;
      color: var(--ink);
    }

    .cn-content h1{ font-size: 2rem; }
    .cn-content h2{ font-size: 1.75rem; }
    .cn-content h3{ font-size: 1.5rem; }
    .cn-content h4{ font-size: 1.25rem; }

    .cn-content img{
      max-width: 100%;
      height: auto;
      border-radius: var(--radius-lg);
      margin: 20px 0;
      box-shadow: var(--shadow-1);
    }

    .cn-content a{
      color: var(--primary-color);
      text-decoration: underline;
      text-underline-offset: 3px;
      transition: color .2s ease;
    }
    .cn-content a:hover{ color: var(--accent-color); }

    .cn-content blockquote{
      margin: 20px 0;
      padding: 16px 20px;
      border-left: 5px solid var(--primary-color);
      background: var(--surface-alt);
      border-radius: var(--radius-md);
      font-style: italic;
    }

    .cn-content pre{
      padding: 16px;
      border-radius: var(--radius-md);
      border: 1px solid var(--line-strong);
      background: var(--surface-alt);
      overflow: auto;
      font-family: 'Courier New', monospace;
      font-size: 14px;
    }

    .cn-content ul,
    .cn-content ol{
      padding-left: 24px;
      margin: 16px 0;
    }

    .cn-content li{ margin-bottom: 8px; }

    /* Attachments */
    .cn-attachments{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
    }

    .attachments-title{
      display:flex;
      align-items:center;
      gap:12px;
      font-weight:700;
      font-size: 1.25rem;
      margin: 0 0 20px;
      letter-spacing: -0.01em;
      color: var(--ink);
    }

    .attachments-title i{
      background: var(--primary-light);
      color: var(--primary-color);
      width: 40px;
      height: 40px;
      border-radius: var(--radius-md);
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .attachments-list{ display:grid; gap: 12px; }

    .attachment-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 16px;
      padding: 16px 20px;
      border-radius: var(--radius-lg);
      border: 1px solid var(--line-strong);
      background: var(--surface-alt);
      text-decoration: none;
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
      gap: 16px;
      min-width: 0;
      flex: 1;
    }

    .attachment-icon{
      width: 48px;
      height: 48px;
      border-radius: var(--radius-md);
      background: var(--primary-light);
      color: var(--primary-color);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size: 20px;
      flex-shrink: 0;
    }

    .attachment-info{ min-width:0; flex:1; }

    .attachment-name{
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 4px;
    }

    .attachment-meta{
      font-size: 13px;
      color: var(--muted-color);
    }

    .attachment-number{
      font-size: 13px;
      color: var(--muted-color);
      white-space: nowrap;
      font-weight: 500;
    }

    /* Loading */
    .loading-container{
      display:grid;
      gap: 16px;
      max-width: 100%;
      padding: 40px 0;
    }

    .loading-bar{
      height: 16px;
      border-radius: 999px;
      background: var(--surface-alt);
      overflow: hidden;
      position: relative;
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
      padding: 24px;
      color: #c00;
      line-height: 1.6;
      margin: 40px 0;
    }

    .error-container i{
      font-size: 24px;
      margin-bottom: 12px;
      display:block;
    }

    /* Responsive */
    @media (max-width:768px){
      .cn-meta{ gap: 8px; }
      .meta-pill{ font-size: 13px; padding: 6px 12px; }
      .action-btn{ font-size: 13px; padding: 8px 16px; }
      .attachment-item{ padding: 12px 16px; }
      .attachment-icon{ width: 40px; height: 40px; font-size: 18px; }
      .cn-headbar{ gap: 10px; }
    }
  </style>
</head>

<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <main class="cn-container">
    <!-- Header -->
    <header class="cn-header">
      <div class="cn-headbar">
        <h1 class="cn-title" id="cnTitle">Career Notice</h1>

        <!-- Date pill (top-right) -->
        <span class="meta-pill meta-pill-date" id="metaDate" style="display:none">
          <i class="fa-regular fa-calendar"></i>
          <span></span>
        </span>
      </div>

      <div class="cn-meta" id="cnMeta" style="display:none">
        <!-- Featured -->
        <span class="meta-pill" id="metaFeatured" style="display:none">
          <i class="fa-solid fa-star"></i>
          <span>Featured</span>
        </span>
      </div>

      <!-- Content inside header -->
      <article id="cnContent" class="cn-content" style="display:none"></article>

      <div class="cn-actions">
        <button class="action-btn" id="copyLinkBtn" type="button">
          <i class="fa-solid fa-link"></i>
          Copy Link
        </button>

        <button class="action-btn" id="shareBtn" type="button" style="display:none">
          <i class="fa-solid fa-share-nodes"></i>
          Share
        </button>
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
    <figure id="coverSection" class="cn-cover" style="display:none">
      <img id="coverImage" alt="Cover image" loading="lazy"/>
    </figure>

    <!-- Attachments -->
    <section class="cn-attachments" id="attachmentsSection" style="display:none">
      <h3 class="attachments-title">
        <i class="fa-solid fa-paperclip"></i>
        Attachments
      </h3>
      <div class="attachments-list" id="attachmentsList"></div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function () {
      const $ = (id) => document.getElementById(id);

      function getIdentifierFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        return parts[parts.length - 1] || '';
      }

      function findNoticeObject(payload) {
        if (!payload) return null;

        if (payload.data && typeof payload.data === 'object') return payload.data;
        if (payload.career_notice && typeof payload.career_notice === 'object') return payload.career_notice;
        if (payload.notice && typeof payload.notice === 'object') return payload.notice;
        if (payload.item && typeof payload.item === 'object') return payload.item;
        if (payload.data && payload.data.data && typeof payload.data.data === 'object') return payload.data.data;

        // sometimes APIs return the object at root
        if (typeof payload === 'object' && (payload.title || payload.body || payload.uuid || payload.slug)) return payload;

        // sometimes APIs return array
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
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
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

      function renderPage(n) {
        const title = n.title || 'Career Notice';
        $('cnTitle').textContent = title;
        document.title = title;

        // Date pill (top-right)
        const date = formatDate(n.publish_at || n.created_at || n.updated_at);
        if (date) {
          $('metaDate').style.display = '';
          $('metaDate').querySelector('span').textContent = date;
        } else {
          $('metaDate').style.display = 'none';
          $('metaDate').querySelector('span').textContent = '';
        }

        // Meta row (featured only)
        let hasMeta = false;
        const featured = (n.is_featured_home === 1 || n.is_featured_home === true || String(n.is_featured_home) === '1');
        $('metaFeatured').style.display = featured ? '' : 'none';
        if (featured) hasMeta = true;

        $('cnMeta').style.display = hasMeta ? '' : 'none';

        $('cnContent').innerHTML = n.body || '';
        $('cnContent').style.display = '';

        const cover = resolveUrl(n.cover_image);
        if (cover) {
          $('coverSection').style.display = '';
          $('coverImage').src = cover;
        } else {
          $('coverSection').style.display = 'none';
          $('coverImage').removeAttribute('src');
        }

        renderAttachments(n.attachments_json);

        if (navigator.share) $('shareBtn').style.display = '';
      }

      async function fetchJson(url) {
        const res = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
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

        // Public show (primary): GET /public/career-notices/{identifier}
        // Also try /api/public/... and /api/... patterns.
        const candidates = [
          `/public/career-notices/${encodeURIComponent(identifier)}`,
          `/api/public/career-notices/${encodeURIComponent(identifier)}`,
          `/api/career-notices/${encodeURIComponent(identifier)}`
        ];

        for (const url of candidates) {
          try {
            const { res, data } = await fetchJson(url);
            if (!res || !res.ok) continue;

            const n = findNoticeObject(data);
            if (n && (n.title || n.body || n.uuid || n.slug)) {
              setLoading(false);
              renderPage(n);
              return;
            }
          } catch (e) {
            // try next
          }
        }

        setLoading(false);
        setError('Career Notice not found or API endpoint is not reachable. Please verify your public show route URL (expected: /public/career-notices/{identifier}).');
      }

      // Copy Link
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

      // Init
      load();
    })();
  </script>
</body>
</html>
