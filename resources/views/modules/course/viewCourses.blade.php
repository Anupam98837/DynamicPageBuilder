{{-- resources/views/landing/viewCourse.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Course</title>

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
    .course-container{
      max-width: 1280px;
      margin: 0 auto;
      padding: clamp(24px, 4vw, 48px) clamp(16px, 3vw, 24px);
    }

    /* Header */
    .course-header{
      background: var(--surface);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border: 1px solid var(--line-strong);
      margin-bottom: 32px;
      border-radius: 10px;
    }

    /* Title row with date pill at top-right */
    .course-headbar{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .course-title{
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
    .course-meta{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      align-items:center;
      margin-bottom:18px;
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
      opacity: .85;
    }

    /* Date pill */
    .meta-pill-date{
      margin-left:auto;
      flex:0 0 auto;
    }

    /* Summary */
    .course-summary{
      margin: 0 0 18px;
      color: var(--muted-color);
      font-size: 15px;
      line-height: 1.8;
    }

    /* Content (inside header like your announcement page) */
    .course-content{
      color: var(--ink);
      font-size:16px;
      line-height:1.85;
      overflow-wrap:anywhere;
      margin-bottom:18px;
      display:none;
    }

    .course-content p{ margin:0 0 16px; }

    .course-content h1,
    .course-content h2,
    .course-content h3,
    .course-content h4{
      margin: 24px 0 12px;
      line-height: 1.3;
      letter-spacing: -0.02em;
      font-weight: 700;
      color: var(--ink);
    }

    .course-content h1{ font-size:2rem; }
    .course-content h2{ font-size:1.75rem; }
    .course-content h3{ font-size:1.5rem; }
    .course-content h4{ font-size:1.25rem; }

    .course-content img{
      max-width:100%;
      height:auto;
      border-radius: var(--radius-lg);
      margin: 20px 0;
      box-shadow: var(--shadow-1);
    }

    .course-content a{
      color: var(--primary-color);
      text-decoration: underline;
      text-underline-offset: 3px;
      transition: color .2s ease;
    }
    .course-content a:hover{ color: var(--accent-color); }

    .course-content blockquote{
      margin: 20px 0;
      padding: 16px 20px;
      border-left: 5px solid var(--primary-color);
      background: var(--surface-alt);
      border-radius: var(--radius-md);
      font-style: italic;
    }

    .course-content pre{
      padding:16px;
      border-radius: var(--radius-md);
      border:1px solid var(--line-strong);
      background: var(--surface-alt);
      overflow:auto;
      font-family: 'Courier New', monospace;
      font-size:14px;
    }

    .course-content ul,
    .course-content ol{
      padding-left:24px;
      margin:16px 0;
    }

    .course-content li{ margin-bottom:8px; }

    /* Actions */
    .course-actions{
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
    .course-cover{
      margin-bottom:32px;
      border-radius: var(--radius-xl);
      overflow:hidden;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-2);
      display:none;
    }

    .course-cover img{
      width:100%;
      height:auto;
      display:block;
      max-height:500px;
      object-fit:cover;
    }

    /* Attachments */
    .course-attachments{
      background: var(--surface);
      border-radius: var(--radius-xl);
      padding: clamp(24px, 4vw, 40px);
      box-shadow: var(--shadow-2);
      border:1px solid var(--line-strong);
      display:none;
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
      display:none;
    }

    .error-container i{
      font-size:24px;
      margin-bottom:12px;
      display:block;
    }

    /* Responsive */
    @media (max-width:768px){
      .course-meta{ gap:8px; }
      .meta-pill{ font-size:13px; padding:6px 12px; }
      .action-btn{ font-size:13px; padding:8px 16px; }
      .attachment-item{ padding:12px 16px; }
      .attachment-icon{ width:40px; height:40px; font-size:18px; }
      .course-headbar{ gap:10px; }
    }
  </style>
</head>

<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <main class="course-container">
    <!-- Header -->
    <header class="course-header">
      <div class="course-headbar">
        <h1 class="course-title" id="courseTitle">Course</h1>

        <!-- Date pill (top-right) -->
        <span class="meta-pill meta-pill-date" id="metaDate" style="display:none">
          <i class="fa-regular fa-calendar"></i>
          <span></span>
        </span>
      </div>

      <div class="course-meta" id="courseMeta" style="display:none">
        <span class="meta-pill" id="metaDept" style="display:none">
          <i class="fa-solid fa-building-columns"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaLevel" style="display:none">
          <i class="fa-solid fa-layer-group"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaType" style="display:none">
          <i class="fa-solid fa-graduation-cap"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaMode" style="display:none">
          <i class="fa-solid fa-laptop"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaDuration" style="display:none">
          <i class="fa-regular fa-clock"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaCredits" style="display:none">
          <i class="fa-solid fa-hashtag"></i>
          <span></span>
        </span>

        <span class="meta-pill" id="metaFeatured" style="display:none">
          <i class="fa-solid fa-star"></i>
          <span>Featured</span>
        </span>
      </div>

      <p class="course-summary" id="courseSummary" style="display:none"></p>

      <!-- Body inside header (same idea as announcement page) -->
      <article id="courseContent" class="course-content"></article>

      <div class="course-actions">
        <a class="action-btn" id="syllabusBtn" href="#" target="_blank" rel="noopener noreferrer" style="display:none">
          <i class="fa-solid fa-file-lines"></i>
          Open Syllabus
        </a>

        <button class="action-btn" id="copyLinkBtn">
          <i class="fa-solid fa-link"></i>
          Copy Link
        </button>

        <button class="action-btn" id="shareBtn" style="display:none">
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
    <div id="errorSection" class="error-container">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <div id="errorMessage"></div>
    </div>

    <!-- Cover -->
    <figure id="coverSection" class="course-cover">
      <img id="coverImage" alt="Course cover image" loading="lazy"/>
    </figure>

    <!-- Attachments -->
    <section class="course-attachments" id="attachmentsSection">
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

      function escapeHtml(s){
        return String(s ?? '')
          .replace(/&/g,'&amp;')
          .replace(/</g,'&lt;')
          .replace(/>/g,'&gt;')
          .replace(/"/g,'&quot;')
          .replace(/'/g,'&#039;');
      }

      // identifier:
      // - supports /courses/{identifier}
      // - supports /{dept}/courses/{identifier}
      // - supports /departments/{dept}/courses/{identifier}
      function getIdentifierFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        const idx = parts.lastIndexOf('courses');
        if (idx !== -1 && parts[idx + 1]) return parts[idx + 1];
        return parts[parts.length - 1] || '';
      }

      // department:
      // - /departments/{dept}/courses/{id}
      // - /{dept}/courses/{id}
      function getDepartmentFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);

        const deptIdx = parts.indexOf('departments');
        if (deptIdx !== -1 && parts[deptIdx + 1]) return parts[deptIdx + 1];

        const coursesIdx = parts.indexOf('courses');
        if (coursesIdx === 1 && parts[0]) return parts[0];

        return '';
      }

      function findCourseObject(payload) {
        if (!payload) return null;

        // common API shapes
        if (payload.item && typeof payload.item === 'object') return payload.item;
        if (payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data)) return payload.data;
        if (payload.course && typeof payload.course === 'object') return payload.course;

        // nested
        if (payload.data && payload.data.item && typeof payload.data.item === 'object') return payload.data.item;
        if (payload.data && payload.data.data && typeof payload.data.data === 'object') return payload.data.data;

        // direct object
        if (typeof payload === 'object' && (payload.title || payload.body || payload.uuid || payload.slug)) return payload;

        // array
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

      function humanizeMode(s){
        s = String(s || '').trim();
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1);
      }

      function renderAttachments(course) {
        const list = $('attachmentsList');
        list.innerHTML = '';

        // Prefer normalized attachments[] from controller
        let arr = Array.isArray(course.attachments) ? course.attachments : null;

        // fallback: attachments_json (string or object)
        if (!arr) {
          const parsed = safeJson(course.attachments_json);
          if (Array.isArray(parsed)) arr = parsed;
          else if (parsed && Array.isArray(parsed.files)) arr = parsed.files;
        }

        // Optional: include syllabus as first item
        const syllabusUrl = course.syllabus_url_full || resolveUrl(course.syllabus_url);
        if (syllabusUrl) {
          const a = document.createElement('a');
          a.className = 'attachment-item';
          a.href = syllabusUrl;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          a.innerHTML = `
            <div class="attachment-left">
              <div class="attachment-icon"><i class="fa-solid fa-file-lines"></i></div>
              <div class="attachment-info">
                <div class="attachment-name" title="Syllabus">Syllabus</div>
                <div class="attachment-meta">Click to open</div>
              </div>
            </div>
            <div class="attachment-number">#S</div>
          `;
          list.appendChild(a);
        }

        if (Array.isArray(arr) && arr.length) {
          arr.forEach((item, idx) => {
            let url = '', name = '', meta = '';

            if (typeof item === 'string') {
              url = resolveUrl(item);
              name = item.split('/').pop() || `Attachment ${idx + 1}`;
            } else if (item && typeof item === 'object') {
              url = resolveUrl(item.url || item.path || item.file || '');
              name = item.name || (url ? url.split('/').pop() : `Attachment ${idx + 1}`);
              meta = item.mime || item.type || '';
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
                  <div class="attachment-name" title="${escapeHtml(name)}">${escapeHtml(name)}</div>
                  <div class="attachment-meta">${escapeHtml(meta || 'Click to open')}</div>
                </div>
              </div>
              <div class="attachment-number">#${idx + 1}</div>
            `;

            list.appendChild(a);
          });
        }

        $('attachmentsSection').style.display = list.children.length ? '' : 'none';
      }

      function renderPage(c) {
        const title = c.title || 'Course';
        $('courseTitle').textContent = title;
        document.title = title;

        // Date pill
        const date = formatDate(c.publish_at || c.created_at || c.updated_at);
        if (date) {
          $('metaDate').style.display = '';
          $('metaDate').querySelector('span').textContent = date;
        } else {
          $('metaDate').style.display = 'none';
          $('metaDate').querySelector('span').textContent = '';
        }

        // Meta pills
        let hasMeta = false;

        const dept = c.department_title || c.department_name || (c.department && (c.department.title || c.department.name)) || '';
        if (dept) {
          $('metaDept').style.display = '';
          $('metaDept').querySelector('span').textContent = dept;
          hasMeta = true;
        } else {
          $('metaDept').style.display = 'none';
          $('metaDept').querySelector('span').textContent = '';
        }

        const level = c.program_level ? String(c.program_level).toUpperCase() : '';
        if (level) {
          $('metaLevel').style.display = '';
          $('metaLevel').querySelector('span').textContent = level;
          hasMeta = true;
        } else {
          $('metaLevel').style.display = 'none';
          $('metaLevel').querySelector('span').textContent = '';
        }

        const type = c.program_type ? humanizeMode(c.program_type) : '';
        if (type) {
          $('metaType').style.display = '';
          $('metaType').querySelector('span').textContent = type;
          hasMeta = true;
        } else {
          $('metaType').style.display = 'none';
          $('metaType').querySelector('span').textContent = '';
        }

        const mode = c.mode ? humanizeMode(c.mode) : '';
        if (mode) {
          $('metaMode').style.display = '';
          $('metaMode').querySelector('span').textContent = mode;
          hasMeta = true;
        } else {
          $('metaMode').style.display = 'none';
          $('metaMode').querySelector('span').textContent = '';
        }

        const dv = Number(c.duration_value || 0);
        const du = String(c.duration_unit || '').trim();
        const durationText = dv > 0 ? `${dv} ${du || 'months'}` : '';
        if (durationText) {
          $('metaDuration').style.display = '';
          $('metaDuration').querySelector('span').textContent = durationText;
          hasMeta = true;
        } else {
          $('metaDuration').style.display = 'none';
          $('metaDuration').querySelector('span').textContent = '';
        }

        const credits = (c.credits === 0 || c.credits) ? c.credits : null;
        if (credits !== null && String(credits) !== '') {
          $('metaCredits').style.display = '';
          $('metaCredits').querySelector('span').textContent = `${credits} Credits`;
          hasMeta = true;
        } else {
          $('metaCredits').style.display = 'none';
          $('metaCredits').querySelector('span').textContent = '';
        }

        const featured = (c.is_featured_home === 1 || c.is_featured_home === true || String(c.is_featured_home) === '1');
        $('metaFeatured').style.display = featured ? '' : 'none';
        if (featured) hasMeta = true;

        $('courseMeta').style.display = hasMeta ? '' : 'none';

        // Summary
        const summary = String(c.summary || '').trim();
        if (summary) {
          $('courseSummary').style.display = '';
          $('courseSummary').textContent = summary;
        } else {
          $('courseSummary').style.display = 'none';
          $('courseSummary').textContent = '';
        }

        // Body
        $('courseContent').innerHTML = c.body || '';
        $('courseContent').style.display = '';

        // Cover
        const cover = c.cover_image_url || resolveUrl(c.cover_image);
        if (cover) {
          $('coverSection').style.display = '';
          $('coverImage').src = cover;
        } else {
          $('coverSection').style.display = 'none';
          $('coverImage').removeAttribute('src');
        }

        // Syllabus button
        const syllabusUrl = c.syllabus_url_full || resolveUrl(c.syllabus_url);
        if (syllabusUrl) {
          $('syllabusBtn').style.display = '';
          $('syllabusBtn').href = syllabusUrl;
        } else {
          $('syllabusBtn').style.display = 'none';
          $('syllabusBtn').setAttribute('href', '#');
        }

        // Attachments
        renderAttachments(c);

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

        const dept = getDepartmentFromUrl();

        // ✅ Public show candidates (matches your controller naming style)
        const candidates = [
          `/api/public/courses/${encodeURIComponent(identifier)}`,
          `/public/courses/${encodeURIComponent(identifier)}`,
          `/api/courses/${encodeURIComponent(identifier)}`
        ];

        // Dept-aware candidates (if you expose such routes)
        if (dept) {
          candidates.unshift(`/api/public/departments/${encodeURIComponent(dept)}/courses/${encodeURIComponent(identifier)}`);
          candidates.unshift(`/api/departments/${encodeURIComponent(dept)}/courses/${encodeURIComponent(identifier)}`);
        }

        for (const url of candidates) {
          try {
            const { res, data } = await fetchJson(url);
            if (!res || !res.ok) continue;

            const c = findCourseObject(data);
            if (c && (c.title || c.body)) {
              setLoading(false);
              renderPage(c);
              return;
            }
          } catch (e) {
            // try next
          }
        }

        setLoading(false);
        setError('Course not found or API endpoint is not reachable. Please verify your public show route URL (expected: /api/public/courses/{identifier}).');
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

      // Init
      load();
    })();
  </script>
</body>
</html>
