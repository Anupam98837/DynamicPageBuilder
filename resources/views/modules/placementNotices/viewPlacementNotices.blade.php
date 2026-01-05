{{-- resources/views/landing/viewPlacementNotices.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Placement Notice</title>

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
    .pn-container{
      max-width: 1280px;
      margin: 0 auto;
      padding: clamp(24px, 4vw, 48px) clamp(16px, 3vw, 24px);
    }

    /* Header */
    .pn-header{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
      margin-bottom: 32px;
      border-radius: 10px;
    }

    /* Title row with date pill at top-right */
    .pn-headbar{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .pn-title{
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
    .pn-meta{
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

    /* Date pill (kept same style, just placed in headbar) */
    .meta-pill-date{
      margin-left: auto;
      flex: 0 0 auto;
    }

    /* Actions */
    .pn-actions{
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
    .pn-cover{
      margin-bottom: 32px;
      border-radius: var(--radius-xl);
      overflow: hidden;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-2);
    }

    .pn-cover img{
      width: 100%;
      height: auto;
      display: block;
      max-height: 500px;
      object-fit: cover;
    }

    /* Content */
    .pn-content{
      color: var(--ink);
      font-size: 16px;
      line-height: 1.85;
      overflow-wrap: anywhere;
      margin-bottom: 24px;
    }

    .pn-content p{ margin: 0 0 16px; }

    .pn-content h1,
    .pn-content h2,
    .pn-content h3,
    .pn-content h4{
      margin: 24px 0 12px;
      line-height: 1.3;
      letter-spacing: -0.02em;
      font-weight: 700;
      color: var(--ink);
    }

    .pn-content h1{ font-size: 2rem; }
    .pn-content h2{ font-size: 1.75rem; }
    .pn-content h3{ font-size: 1.5rem; }
    .pn-content h4{ font-size: 1.25rem; }

    .pn-content img{
      max-width: 100%;
      height: auto;
      border-radius: var(--radius-lg);
      margin: 20px 0;
      box-shadow: var(--shadow-1);
    }

    .pn-content a{
      color: var(--primary-color);
      text-decoration: underline;
      text-underline-offset: 3px;
      transition: color .2s ease;
    }
    .pn-content a:hover{ color: var(--accent-color); }

    .pn-content blockquote{
      margin: 20px 0;
      padding: 16px 20px;
      border-left: 5px solid var(--primary-color);
      background: var(--surface-alt);
      border-radius: var(--radius-md);
      font-style: italic;
    }

    .pn-content pre{
      padding: 16px;
      border-radius: var(--radius-md);
      border: 1px solid var(--line-strong);
      background: var(--surface-alt);
      overflow: auto;
      font-family: 'Courier New', monospace;
      font-size: 14px;
    }

    .pn-content ul,
    .pn-content ol{
      padding-left: 24px;
      margin: 16px 0;
    }

    .pn-content li{ margin-bottom: 8px; }

    /* Attachments / Extra links */
    .pn-attachments{
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
      .pn-meta{ gap: 8px; }
      .meta-pill{ font-size: 13px; padding: 6px 12px; }
      .action-btn{ font-size: 13px; padding: 8px 16px; }
      .attachment-item{ padding: 12px 16px; }
      .attachment-icon{ width: 40px; height: 40px; font-size: 18px; }
      .pn-headbar{ gap: 10px; }
    }
  </style>
</head>

<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <main class="pn-container">
    <!-- Header -->
    <header class="pn-header">
      <div class="pn-headbar">
        <h1 class="pn-title" id="pnTitle">Placement Notice</h1>

        <!-- Date pill (top-right) -->
        <span class="meta-pill meta-pill-date" id="metaDate" style="display:none">
          <i class="fa-regular fa-calendar"></i>
          <span></span>
        </span>
      </div>

      <div class="pn-meta" id="pnMeta" style="display:none">
        <!-- Departments -->
        <span class="meta-pill" id="metaDept" style="display:none">
          <i class="fa-solid fa-building-columns"></i>
          <span></span>
        </span>

        <!-- Recruiter -->
        <span class="meta-pill" id="metaRecruiter" style="display:none">
          <i class="fa-solid fa-building"></i>
          <span></span>
        </span>

        <!-- Role -->
        <span class="meta-pill" id="metaRole" style="display:none">
          <i class="fa-solid fa-id-badge"></i>
          <span></span>
        </span>

        <!-- CTC -->
        <span class="meta-pill" id="metaCTC" style="display:none">
          <i class="fa-solid fa-sack-dollar"></i>
          <span></span>
        </span>

        <!-- Deadline -->
        <span class="meta-pill" id="metaDeadline" style="display:none">
          <i class="fa-regular fa-clock"></i>
          <span></span>
        </span>

        <!-- Featured -->
        <span class="meta-pill" id="metaFeatured" style="display:none">
          <i class="fa-solid fa-star"></i>
          <span>Featured</span>
        </span>
      </div>

      <!-- Content inside header -->
      <article id="pnContent" class="pn-content" style="display:none"></article>

      <div class="pn-actions">
        <button class="action-btn" id="copyLinkBtn" type="button">
          <i class="fa-solid fa-link"></i>
          Copy Link
        </button>

        <a class="action-btn" id="applyBtn" href="#" target="_blank" rel="noopener noreferrer" style="display:none">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
          Apply Now
        </a>

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

    <!-- Cover (banner_image_url) -->
    <figure id="coverSection" class="pn-cover" style="display:none">
      <img id="coverImage" alt="Banner image" loading="lazy"/>
    </figure>

    <!-- Attachments / Extra links (from metadata if available) -->
    <section class="pn-attachments" id="attachmentsSection" style="display:none">
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

      function getDepartmentFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        const deptIdx = parts.indexOf('department');
        if (deptIdx !== -1 && parts[deptIdx + 1]) return parts[deptIdx + 1];

        const deptIdx2 = parts.indexOf('departments');
        if (deptIdx2 !== -1 && parts[deptIdx2 + 1]) return parts[deptIdx2 + 1];

        return '';
      }

      function findNoticeObject(payload) {
        if (!payload) return null;

        if (payload.data && typeof payload.data === 'object') return payload.data;
        if (payload.placement_notice && typeof payload.placement_notice === 'object') return payload.placement_notice;
        if (payload.notice && typeof payload.notice === 'object') return payload.notice;
        if (payload.item && typeof payload.item === 'object') return payload.item;
        if (payload.data && payload.data.data && typeof payload.data.data === 'object') return payload.data.data;

        // sometimes APIs return the object at root
        if (typeof payload === 'object' && (payload.title || payload.description || payload.uuid || payload.slug)) return payload;

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

      function normalizeDepartmentsLabel(n) {
        // Try common patterns coming from API
        const parts = [];

        if (Array.isArray(n.departments)) {
          n.departments.forEach(d => {
            if (!d) return;
            const name = d.name || d.title || d.department_name;
            if (name) parts.push(name);
          });
        }

        if (!parts.length) {
          const dn = n.department_name || n.department || n.departmentTitle;
          if (dn && typeof dn === 'string') parts.push(dn);
        }

        if (!parts.length) {
          const maybeNames = n.department_names || n.departmentNames;
          if (Array.isArray(maybeNames)) parts.push(...maybeNames.filter(Boolean).map(String));
          if (typeof maybeNames === 'string') parts.push(maybeNames);
        }

        if (!parts.length) {
          const ids = safeJson(n.department_ids);
          if (Array.isArray(ids) && ids.length) parts.push('Dept IDs: ' + ids.join(', '));
        }

        const label = parts.filter(Boolean).join(', ');
        return label.trim();
      }

      function renderAttachments(fromAny) {
        const list = $('attachmentsList');
        list.innerHTML = '';

        const parsed = safeJson(fromAny);

        // Accept many shapes:
        // - [ {name,url}, ... ]
        // - { files: [...] }
        // - { attachments: [...] }
        // - { documents: [...] }
        // - { links: [...] }
        // - stringified json in metadata
        let arr = null;

        if (Array.isArray(parsed)) arr = parsed;
        else if (parsed && typeof parsed === 'object') {
          if (Array.isArray(parsed.files)) arr = parsed.files;
          else if (Array.isArray(parsed.attachments)) arr = parsed.attachments;
          else if (Array.isArray(parsed.documents)) arr = parsed.documents;
          else if (Array.isArray(parsed.links)) arr = parsed.links;
        }

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
            url = resolveUrl(item.url || item.path || item.file || item.href || '');
            name = item.name || item.title || (url ? url.split('/').pop() : `Attachment ${idx + 1}`);
            meta = item.type || item.mime || item.label || '';
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

      function buildBodyHTML(n) {
        let html = '';

        if (n.description) html += n.description;

        // Optional eligibility block
        if (n.eligibility) {
          html += `<h3>Eligibility</h3>${n.eligibility}`;
        }

        // Optional apply block (small link inside content too)
        const apply = resolveUrl(n.apply_url);
        if (apply) {
          html += `<h3>Apply</h3><p><a href="${apply}" target="_blank" rel="noopener noreferrer">${apply}</a></p>`;
        }

        if (!html.trim()) {
          html = `<p>No details available.</p>`;
        }

        return html;
      }

      function renderPage(n) {
        const title = n.title || 'Placement Notice';
        $('pnTitle').textContent = title;
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

        // Meta pills
        let hasMeta = false;

        const deptLabel = normalizeDepartmentsLabel(n);
        if (deptLabel) {
          $('metaDept').style.display = '';
          $('metaDept').querySelector('span').textContent = deptLabel;
          hasMeta = true;
        } else {
          $('metaDept').style.display = 'none';
          $('metaDept').querySelector('span').textContent = '';
        }

        const recruiter =
          (n.recruiter && (n.recruiter.name || n.recruiter.title))
            ? (n.recruiter.name || n.recruiter.title)
            : (n.recruiter_name || n.recruiterName || '');

        if (recruiter) {
          $('metaRecruiter').style.display = '';
          $('metaRecruiter').querySelector('span').textContent = recruiter;
          hasMeta = true;
        } else {
          $('metaRecruiter').style.display = 'none';
          $('metaRecruiter').querySelector('span').textContent = '';
        }

        const role = n.role_title || n.role || '';
        if (role) {
          $('metaRole').style.display = '';
          $('metaRole').querySelector('span').textContent = role;
          hasMeta = true;
        } else {
          $('metaRole').style.display = 'none';
          $('metaRole').querySelector('span').textContent = '';
        }

        const ctc = (n.ctc !== null && n.ctc !== undefined && String(n.ctc).trim() !== '') ? String(n.ctc) : '';
        if (ctc) {
          $('metaCTC').style.display = '';
          $('metaCTC').querySelector('span').textContent = `CTC: ${ctc}`;
          hasMeta = true;
        } else {
          $('metaCTC').style.display = 'none';
          $('metaCTC').querySelector('span').textContent = '';
        }

        const deadline = formatDate(n.last_date_to_apply);
        if (deadline) {
          $('metaDeadline').style.display = '';
          $('metaDeadline').querySelector('span').textContent = `Last date: ${deadline}`;
          hasMeta = true;
        } else {
          $('metaDeadline').style.display = 'none';
          $('metaDeadline').querySelector('span').textContent = '';
        }

        const featured = (n.is_featured_home === 1 || n.is_featured_home === true || String(n.is_featured_home) === '1');
        $('metaFeatured').style.display = featured ? '' : 'none';
        if (featured) hasMeta = true;

        $('pnMeta').style.display = hasMeta ? '' : 'none';

        // Content (description + eligibility etc.)
        $('pnContent').innerHTML = buildBodyHTML(n);
        $('pnContent').style.display = '';

        // Cover (banner_image_url)
        const cover = resolveUrl(n.banner_image_url);
        if (cover) {
          $('coverSection').style.display = '';
          $('coverImage').src = cover;
        } else {
          $('coverSection').style.display = 'none';
          $('coverImage').removeAttribute('src');
        }

        // Apply button
        const applyUrl = resolveUrl(n.apply_url);
        if (applyUrl) {
          $('applyBtn').style.display = '';
          $('applyBtn').href = applyUrl;
        } else {
          $('applyBtn').style.display = 'none';
          $('applyBtn').href = '#';
        }

        // Attachments (if your API returns anything in metadata)
        // We try: metadata.attachments / metadata.files / metadata.links / etc.
        const metaObj = safeJson(n.metadata);
        const attachmentsCandidate =
          (metaObj && (metaObj.attachments || metaObj.files || metaObj.links || metaObj.documents)) ? metaObj : null;

        renderAttachments(attachmentsCandidate);

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

        // Public show (primary): GET /public/placement-notices/{identifier}
        // Also try /api/public/... and /api/... patterns.
        const candidates = [
          `/public/placement-notices/${encodeURIComponent(identifier)}`,
          `/api/public/placement-notices/${encodeURIComponent(identifier)}`,
          `/api/placement-notices/${encodeURIComponent(identifier)}`
        ];

        // If your web route uses a dept segment, we also try dept-aware admin show routes.
        const dept = getDepartmentFromUrl();
        if (dept) {
          candidates.unshift(`/api/placement-notices/department/${encodeURIComponent(dept)}/${encodeURIComponent(identifier)}`);
          candidates.unshift(`/placement-notices/department/${encodeURIComponent(dept)}/${encodeURIComponent(identifier)}`);
          // (Public API doesn't define showByDepartment, but trying doesn't hurt)
          candidates.unshift(`/api/public/placement-notices/department/${encodeURIComponent(dept)}/${encodeURIComponent(identifier)}`);
          candidates.unshift(`/public/placement-notices/department/${encodeURIComponent(dept)}/${encodeURIComponent(identifier)}`);
        }

        for (const url of candidates) {
          try {
            const { res, data } = await fetchJson(url);
            if (!res || !res.ok) continue;

            const n = findNoticeObject(data);
            if (n && (n.title || n.description || n.uuid || n.slug)) {
              setLoading(false);
              renderPage(n);
              return;
            }
          } catch (e) {
            // try next
          }
        }

        setLoading(false);
        setError('Placement Notice not found or API endpoint is not reachable. Please verify your public show route URL (expected: /public/placement-notices/{identifier}).');
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
