{{-- resources/views/modules/feedbacks/submitFeedback.blade.php --}}
@section('title','Submit Feedback')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
.fbsub-wrap{max-width:1200px;margin:16px auto 54px;padding:0 6px;overflow:visible}
.fbsub-panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}
.fbsub-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.fbsub-card .card-header{background:transparent;border-bottom:1px solid var(--line-soft)}
.loading-overlay{position:fixed; inset:0;background:rgba(0,0,0,.45);display:none;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}
.loading-overlay.is-show{display:flex}
.loading-spinner{background:var(--surface);padding:20px 22px;border-radius:14px;display:flex;flex-direction:column;align-items:center;gap:10px;box-shadow:0 10px 26px rgba(0,0,0,.3)}
.spinner{width:40px;height:40px;border-radius:50%;border:4px solid rgba(148,163,184,.3);border-top:4px solid var(--primary-color);animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

.count-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color);font-weight:800;font-size:12px;white-space:nowrap}
.badge-submitted{background:rgba(16,185,129,.14);color:#059669;border:1px solid rgba(16,185,129,.35)}
.badge-pending{background:rgba(245,158,11,.14);color:#b45309;border:1px solid rgba(245,158,11,.35)}

.filter-pills{display:flex;gap:8px;flex-wrap:wrap}
.filter-pill{
  border:1px solid var(--line-strong);
  background:var(--surface);
  color:var(--ink);
  border-radius:999px;
  padding:7px 12px;
  font-weight:800;
  font-size:12px;
  cursor:pointer;
  user-select:none;
}
.filter-pill.active{
  background:color-mix(in oklab, var(--primary-color) 10%, var(--surface));
  border-color:color-mix(in oklab, var(--primary-color) 45%, var(--line-strong));
}
.filter-pill .dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px;vertical-align:middle}
.dot-all{background:rgba(148,163,184,.8)}
.dot-pending{background:#ef4444}
.dot-submitted{background:#22c55e}

.accordion-item{border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;background:var(--surface);margin-bottom:10px}
.accordion-item:last-child{margin-bottom:0}
.accordion-button{background:var(--surface);color:var(--ink);font-weight:900;padding:14px 16px;font-size:15px}
.accordion-button:not(.collapsed){background:color-mix(in oklab, var(--primary-color) 8%, var(--surface));color:var(--ink);border-bottom:1px solid var(--line-soft)}
.accordion-button:focus{box-shadow:0 0 0 .2rem rgba(201,75,80,.35)}

.fb-post-head{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.fb-post-dot{width:10px;height:10px;border-radius:50%}
.fb-post-dot.submitted{background:#22c55e}
.fb-post-dot.pending{background:#ef4444}
.fb-post-title{font-weight:950}
.fb-post-meta{font-size:12px;color:var(--muted-color)}
.fb-post-pill{margin-left:auto}

.fb-post-ac-item{position:relative}
.fb-post-ac-item::before{
  content:"";
  position:absolute;left:0;top:0;bottom:0;width:6px;
  background:rgba(148,163,184,.35);
}
.fb-post-ac-item.is-submitted::before{ background: rgba(34,197,94,.55); }
.fb-post-ac-item.is-pending::before{ background: rgba(239,68,68,.55); }

.fbsub-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.fbsub-toolbar .left{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.fbsub-toolbar .right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

.fb-table-wrap{border:1px solid var(--line-soft);border-radius:14px;overflow:auto;max-width:100%}
.fb-table{width:100%;min-width:980px;margin:0}
.fb-table thead th{position:sticky;top:0;background:var(--surface);z-index:3;border-bottom:1px solid var(--line-strong);font-size:12px;text-transform:uppercase;letter-spacing:.04em}
.fb-table th,.fb-table td{vertical-align:top;padding:12px 12px;border-bottom:1px solid var(--line-soft)}
.fb-table tbody tr:hover{background:var(--page-hover)}
.fb-qtitle{font-weight:900;color:var(--ink);display:flex;gap:10px;align-items:flex-start}
.fb-qmeta{font-size:12px;color:var(--muted-color);margin-top:2px}

.starbar{display:inline-flex;align-items:center;gap:6px;user-select:none}
.starbtn{border:none;background:transparent;padding:2px 2px;line-height:1;cursor:pointer;color:rgba(148,163,184,.9);font-size:18px;transition:transform .08s ease,color .12s ease}
.starbtn:hover{transform:translateY(-1px)}
.starbtn.is-on{color:var(--primary-color)}
.starbtn:focus{outline:none;box-shadow:0 0 0 .2rem rgba(201,75,80,.25);border-radius:10px}

.text-mini{font-size:12px;color:var(--muted-color)}
.hr-soft{border-color:var(--line-soft)!important}

.empty-state{text-align:center;padding:42px 20px}
.empty-state i{font-size:48px;color:var(--muted-color);margin-bottom:16px;opacity:.6}
.empty-state .title{font-weight:900;color:var(--ink);margin-bottom:8px}
.empty-state .subtitle{font-size:14px;color:var(--muted-color)}

/* ✅ Minimal faculty chips (small + inline, no big card, no "Rating:" line) */
.fac-flex{display:flex;gap:10px;align-items:flex-start}
.fac-chip{
  padding:8px 10px;
  border:1px solid var(--line-soft);
  border-radius:12px;
  background:var(--surface);
  min-width:170px;
  max-width:220px;
}
.fac-chip.overall{
  border-color:color-mix(in oklab, var(--primary-color) 22%, var(--line-soft));
  background:color-mix(in oklab, var(--primary-color) 5%, var(--surface));
}
.fac-chip .name{
  font-weight:900;
  font-size:12px;
  color:var(--ink);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  line-height:1.1;
  margin-bottom:6px;
}
.fac-chip .name i{opacity:.75;margin-right:6px}
.fac-chip .stars{display:flex;align-items:center;gap:6px}
.fac-chip .stars .starbar{gap:4px}
.fac-chip .stars .starbtn{font-size:16px}
.fac-chip .mini-score{margin-top:5px;font-size:11px;color:var(--muted-color);font-weight:800}

.qcell-vcenter{
  vertical-align: middle !important; /* centers the whole cell in the row */
}

.qcell-vcenter .fb-qtitle{
  align-items: center !important;    /* centers icon + text vertically within the flex row */
}

@media (max-width: 768px){
  .fbsub-panel .d-flex{flex-direction:column;gap:12px !important}
  .fb-table{min-width:860px}
  .fac-chip{min-width:155px}
}
</style>
@endpush

@section('content')
<div class="fbsub-wrap">

  <div id="globalLoading" class="loading-overlay">
    @include('partials.overlay')

  </div>

  <div class="fbsub-panel mb-3">
    <div class="fbsub-toolbar">
      <div class="left">
        <div class="fw-semibold"><i class="fa fa-star me-2"></i>Submit Feedback</div>
        <span class="count-badge" id="postBadge">—</span>
      </div>
      <div class="right">
        <div class="filter-pills" id="postFilters">
          <span class="filter-pill active" data-filter="all"><span class="dot dot-all"></span>All <span class="ms-1" id="cntAll">0</span></span>
          <span class="filter-pill" data-filter="pending"><span class="dot dot-pending"></span>Pending <span class="ms-1" id="cntPending">0</span></span>
          <span class="filter-pill" data-filter="submitted"><span class="dot dot-submitted"></span>Submitted <span class="ms-1" id="cntSubmitted">0</span></span>
        </div>

        <button id="btnRefresh" class="btn btn-light">
          <i class="fa fa-rotate me-1"></i>Refresh
        </button>
      </div>
    </div>

    <div class="text-mini mt-2" id="summaryText">—</div>
  </div>

  <div class="card fbsub-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="fw-semibold"><i class="fa fa-layer-group me-2"></i>Feedback Posts</div>
      <div class="small text-muted">Open a post (accordion) • rate faculty under each question • Submit/Update works for that post.</div>
    </div>

    <div class="card-body">
      <div id="emptyState" class="empty-state">
        <i class="fa fa-circle-info"></i>
        <div class="title">No Feedback Posts</div>
        <div class="subtitle">If you have assigned feedback posts, they will appear here.</div>
      </div>

      <div id="accordionsRoot" style="display:none;"></div>
    </div>
  </div>

</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__FEEDBACK_SUBMIT_PAGE_V7__) return;
  window.__FEEDBACK_SUBMIT_PAGE_V7__ = true;

  const $ = (id) => document.getElementById(id);

  const API = {
    available: () => '/api/feedback-posts/available',
    questionsCurrent: () => '/api/feedback-questions/current',
    users: () => '/api/users',
    submit: (idOrUuid) => `/api/feedback-posts/${idOrUuid}/submit`,
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function idNum(v){
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : null;
  }
  function pickArray(v){
    if (Array.isArray(v)) return v;
    if (v === null || v === undefined) return [];
    if (typeof v === 'string'){
      try{ const d = JSON.parse(v); return Array.isArray(d) ? d : []; }catch(_){ return []; }
    }
    return [];
  }
  function pickObj(v){
    if (v && typeof v === 'object' && !Array.isArray(v)) return v;
    if (typeof v === 'string'){
      try{
        const d = JSON.parse(v);
        return (d && typeof d === 'object' && !Array.isArray(d)) ? d : null;
      }catch(_){ return null; }
    }
    return null;
  }
  function normalizeList(js){
    if (!js) return null;
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    return null;
  }

  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  function authHeaders(extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token(),
      'Accept': 'application/json'
    }, extra);
  }
  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }
  function showLoading(on){ $('globalLoading')?.classList.toggle('is-show', !!on); }

  const toastOk  = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok  = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  // =========================
  // State
  // =========================
  const state = {
    posts: [],
    questions: [],
    users: [],
    filter: 'all',
    ratingsByPost: {}, // ratingsByPost[postKey][qid][fid] = stars
  };

  function qLabel(q){ return String(q?.question_title || q?.title || q?.name || (`Question #${q?.id}`) || 'Question'); }
  function qGroup(q){ return String(q?.group_title || q?.group || '').trim(); }
  function userLabel(u){ return String(u?.name || u?.full_name || 'User'); }
  function facultyUsers(){ return (state.users || []).filter(u => String(u?.role || '').toLowerCase() === 'faculty'); }

  function semesterTitle(post){
    if (post?.semester_no !== null && post?.semester_no !== undefined && String(post.semester_no).trim() !== '') {
      return `Semester ${String(post.semester_no)}`;
    }
    if (post?.semester_name && String(post.semester_name).trim()) return String(post.semester_name);
    if (post?.semester_id) return `Semester ${post.semester_id}`;
    return 'General';
  }
  function subjectTitle(post){
    if (post?.subject_name && String(post.subject_name).trim()) return String(post.subject_name);
    if (post?.subject_id) return `Subject #${post.subject_id}`;
    return 'General';
  }

  function getAllowedFacultyIdsForQuestion(post, qid){
    if (!post) return [];
    const globalFaculty = new Set(pickArray(post?.faculty_ids).map(idNum).filter(Boolean));
    const qf = pickObj(post?.question_faculty) || {};
    const rule = qf?.[String(qid)];

    if (rule === null) return []; // no faculty -> Overall only
    if (rule === undefined) return Array.from(globalFaculty);

    const ruleObj = pickObj(rule);
    if (!ruleObj) return Array.from(globalFaculty);

    if (ruleObj.faculty_ids === null) return Array.from(globalFaculty);

    const arr = pickArray(ruleObj.faculty_ids).map(idNum).filter(Boolean);
    if (globalFaculty.size) return arr.filter(x => globalFaculty.has(x));
    return arr;
  }

  function filteredPosts(){
    if (state.filter === 'submitted') return state.posts.filter(p => !!p.is_submitted);
    if (state.filter === 'pending') return state.posts.filter(p => !p.is_submitted);
    return state.posts;
  }

  function updateCounts(){
    const all = state.posts.length;
    const sub = state.posts.filter(p => !!p.is_submitted).length;
    const pen = all - sub;
    $('cntAll').textContent = all;
    $('cntSubmitted').textContent = sub;
    $('cntPending').textContent = pen;
  }

  function updateTopSummary(){
    const list = filteredPosts();
    if (!list.length){
      $('postBadge').textContent = '—';
      $('summaryText').textContent = 'No posts for the current filter.';
      return;
    }
    const sub = list.filter(p => !!p.is_submitted).length;
    const pen = list.length - sub;
    $('postBadge').textContent = `Showing: ${list.length} • Pending: ${pen} • Submitted: ${sub}`;
    $('summaryText').textContent = 'Open a post below and rate faculty (compact chips) per question.';
  }

  function ensureRatingSlot(postKey, qid, fid){
    if (!state.ratingsByPost[postKey]) state.ratingsByPost[postKey] = {};
    if (!state.ratingsByPost[postKey][qid]) state.ratingsByPost[postKey][qid] = {};
    if (state.ratingsByPost[postKey][qid][fid] === undefined) state.ratingsByPost[postKey][qid][fid] = 0;
  }

  function starHTML(postKey, qid, fid, value){
    const v = state.ratingsByPost?.[postKey]?.[qid]?.[fid] ?? 0;
    const on = value <= v;
    return `
      <button type="button"
        class="starbtn ${on ? 'is-on' : ''}"
        data-post="${esc(String(postKey))}"
        data-qid="${esc(String(qid))}"
        data-fid="${esc(String(fid))}"
        data-val="${esc(String(value))}"
        aria-label="Rate ${value}">
        <i class="fa-solid fa-star"></i>
      </button>
    `;
  }

  function prefillFromSubmission(postKey, post){
    const ans = pickObj(post?.submission?.answers) || null;
    if (!ans) return;

    Object.keys(ans).forEach(qidKey => {
      const qid = idNum(qidKey);
      if (qid === null) return;
      const facObj = ans[qidKey];
      if (!facObj || typeof facObj !== 'object') return;

      Object.keys(facObj).forEach(fidKey => {
        const fid = idNum(fidKey);
        if (fid === null) return;
        const v = idNum(facObj[fidKey]);
        if (v === null) return;
        ensureRatingSlot(postKey, qid, fid);
        state.ratingsByPost[postKey][qid][fid] = Math.max(0, Math.min(5, v));
      });
    });
  }

  function buildQuestionChips(post, postKey){
    const qIds = pickArray(post?.question_ids).map(idNum).filter(Boolean);
    const qMap = new Map((state.questions || []).map(q => [idNum(q?.id), q]));
    const allFaculty = facultyUsers();

    const out = [];
    qIds.forEach(qid => {
      const q = qMap.get(qid) || { id: qid, question_title: `Question #${qid}` };
      const allowedFacultyIds = getAllowedFacultyIdsForQuestion(post, qid);

      let facultyList = [];
      if (!allowedFacultyIds.length){
        ensureRatingSlot(postKey, qid, 0);
        facultyList = [{ id: 0, name: 'Overall', _overall: true }];
      } else {
        // ensure slots for each faculty id (even if name missing)
        allowedFacultyIds.forEach(fid => ensureRatingSlot(postKey, qid, fid));

        facultyList = allowedFacultyIds.map(fid => {
          const u = allFaculty.find(f => String(f?.id) === String(fid));
          return {
            id: fid,
            name: u ? userLabel(u) : 'Faculty not found',
            _missing: !u
          };
        });
      }

      out.push({ qid, q, facultyList });
    });

    return out;
  }

  function syncMiniScore(postKey, qid, fid){
    const root = $('accordionsRoot');
    const v = parseInt(state.ratingsByPost?.[postKey]?.[qid]?.[fid] ?? 0, 10);
    const el = root?.querySelector(`[data-mini-score="${CSS.escape(String(postKey)+'_'+String(qid)+'_'+String(fid))}"]`);
    if (el) el.textContent = (v >= 1 && v <= 5) ? `${v}/5` : '—';
  }

  function renderPostBody(post, postKey){
    const sem = semesterTitle(post);
    const sub = subjectTitle(post);

    // init + prefill
    const qIds = pickArray(post?.question_ids).map(idNum).filter(Boolean);
    qIds.forEach(qid => {
      const allowed = getAllowedFacultyIdsForQuestion(post, qid);
      if (!allowed.length) ensureRatingSlot(postKey, qid, 0);
      else allowed.forEach(fid => ensureRatingSlot(postKey, qid, fid));
    });
    prefillFromSubmission(postKey, post);

    const items = buildQuestionChips(post, postKey);

    const pub = post?.publish_at ? String(post.publish_at) : '—';
    const exp = post?.expire_at ? String(post.expire_at) : '—';

    const buttonLabel = post?.is_submitted ? 'Update' : 'Submit';
    const buttonIcon  = post?.is_submitted ? 'fa-pen-to-square' : 'fa-paper-plane';

    const submittedLine = (post?.is_submitted && post?.submission?.submitted_at)
      ? `<div class="text-mini mt-1"><i class="fa fa-check me-1" style="opacity:.8"></i>Submitted At: ${esc(String(post.submission.submitted_at))} • Editable</div>`
      : '';

    const tableHTML = items.length ? `
      <div class="fb-table-wrap">
        <table class="table fb-table">
          <thead>
            <tr>
              <th style="width:520px;">Question</th>
              <th>Faculty</th>
            </tr>
          </thead>
          <tbody>
            ${items.map(it => {
              const q = it.q || {};
              return `
                <tr>
                  <td class="qcell-vcenter">
                    <div class="fb-qtitle">
                      <i class="fa-regular fa-circle-question" style="opacity:.85;margin-top:2px"></i>
                      <div>
                        <div>${esc(qLabel(q))}</div>
                        <div class="fb-qmeta">${qGroup(q) ? ('Group: ' + esc(qGroup(q))) : '—'}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fac-flex">
                      ${it.facultyList.map(f => {
                        const fid = idNum(f.id) ?? 0;
                        const isOverall = String(fid) === '0' || !!f._overall;
                        const miniKey = String(postKey)+'_'+String(it.qid)+'_'+String(fid);
                        const v = state.ratingsByPost?.[postKey]?.[it.qid]?.[fid] ?? 0;

                        return `
                          <div class="fac-chip ${isOverall ? 'overall' : ''}">
                            <div class="name" title="${esc(String(f.name))}">
                              ${isOverall
                                ? `<i class="fa-solid fa-star"></i>${esc(String(f.name))}`
                                : `<i class="fa-solid fa-chalkboard-user"></i>${esc(String(f.name))}`
                              }
                            </div>

                            <div class="stars">
                              <div class="starbar" data-post="${esc(String(postKey))}" data-qid="${esc(String(it.qid))}" data-fid="${esc(String(fid))}">
                                ${[1,2,3,4,5].map(x => starHTML(postKey, it.qid, fid, x)).join('')}
                              </div>
                            </div>

                          </div>
                        `;
                      }).join('')}
                    </div>
                  </td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    ` : `
      <div class="text-center text-muted py-4">
        <i class="fa fa-circle-info me-2"></i>No questions in this feedback post.
      </div>
    `;

    return `
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
          <div class="fw-semibold"><i class="fa fa-calendar-alt me-2"></i>${esc(sem)} <span class="text-mini">•</span> <i class="fa fa-book ms-2 me-2"></i>${esc(sub)}</div>
          <div class="text-mini mt-1"><i class="fa fa-clock me-1" style="opacity:.8"></i>Publish: ${esc(pub)} • Expire: ${esc(exp)}</div>
          ${submittedLine}
        </div>

        <button class="btn btn-primary fb-post-submit-btn" data-post="${esc(String(postKey))}">
          <i class="fa ${buttonIcon} me-1"></i>${buttonLabel}
        </button>
      </div>

      <hr class="hr-soft my-3"/>

      ${tableHTML}
    `;
  }

  function renderPostsAccordion(){
    const root = $('accordionsRoot');
    const empty = $('emptyState');

    const list = filteredPosts();
    updateTopSummary();

    if (!list.length){
      empty.style.display = '';
      root.style.display = 'none';
      root.innerHTML = '';
      return;
    }

    empty.style.display = 'none';
    root.style.display = '';
    root.innerHTML = `
      <div class="accordion" id="postsAccordion">
        ${list.map((p, idx) => {
          const postKey = String(p?.uuid || p?.id || idx);
          const hid = `post_h_${postKey.replace(/[^a-zA-Z0-9_]/g,'_')}`;
          const cid = `post_c_${postKey.replace(/[^a-zA-Z0-9_]/g,'_')}`;
          const title = p?.title || ('Feedback #' + (p?.id ?? ''));
          const sem = semesterTitle(p);
          const sub = subjectTitle(p);

          const dotCls = p?.is_submitted ? 'submitted' : 'pending';
          const itemCls = p?.is_submitted ? 'is-submitted' : 'is-pending';
          const statusBadge = p?.is_submitted
            ? `<span class="count-badge badge-submitted fb-post-pill"><i class="fa fa-check me-1"></i>Submitted</span>`
            : `<span class="count-badge badge-pending fb-post-pill"><i class="fa fa-clock me-1"></i>Pending</span>`;

          return `
            <div class="accordion-item fb-post-ac-item ${itemCls}">
              <h2 class="accordion-header" id="${esc(hid)}">
                <button class="accordion-button collapsed" type="button"
                  data-bs-toggle="collapse" data-bs-target="#${esc(cid)}"
                  aria-expanded="false" aria-controls="${esc(cid)}"
                  data-postkey="${esc(postKey)}">
                  <div class="fb-post-head w-100">
                    <span class="fb-post-dot ${dotCls}"></span>
                    <span class="fb-post-title">${esc(String(title))}</span>
                    <span class="fb-post-meta">• ${esc(sem)} • ${esc(sub)}</span>
                    ${statusBadge}
                  </div>
                </button>
              </h2>

              <div id="${esc(cid)}" class="accordion-collapse collapse" data-bs-parent="#postsAccordion">
                <div class="accordion-body">
                  <div id="postBody_${esc(postKey)}" class="post-body-slot">
                    <div class="text-mini text-muted"><i class="fa fa-spinner fa-spin me-2"></i>Loading…</div>
                  </div>
                </div>
              </div>
            </div>
          `;
        }).join('')}
      </div>
    `;

    // ✅ render body on open
    root.querySelectorAll('.accordion-collapse').forEach(col => {
      col.addEventListener('show.bs.collapse', (ev) => {
        const cid = ev.target.id;
        const btn = root.querySelector(`button[data-bs-target="#${CSS.escape(cid)}"]`);
        const postKey = btn?.dataset?.postkey;
        if (!postKey) return;

        const post = state.posts.find(x => String(x?.uuid || x?.id) === String(postKey))
          || filteredPosts().find(x => String(x?.uuid || x?.id) === String(postKey));

        const slot = $('postBody_' + postKey);
        if (!post || !slot) return;

        slot.innerHTML = renderPostBody(post, postKey);
      });
    });
  }

  function collectPayloadAnswers(postKey){
    const map = state.ratingsByPost?.[postKey] || {};
    const answers = {};
    Object.keys(map).forEach(qid => {
      const inner = map[qid] || {};
      const obj = {};
      Object.keys(inner).forEach(fid => {
        const v = parseInt(inner[fid] || 0, 10);
        if (v >= 1 && v <= 5) obj[fid] = v;
      });
      answers[qid] = obj;
    });
    return answers;
  }

  function validateBeforeSubmit(post){
    const postKey = String(post?.uuid || post?.id || '');
    if (!postKey) return 'Invalid feedback post.';

    const qIds = pickArray(post?.question_ids).map(idNum).filter(Boolean);
    if (!qIds.length) return 'No questions found in this feedback post.';

    const ans = collectPayloadAnswers(postKey);

    for (const qid of qIds){
      const block = ans[String(qid)] || {};
      const allowed = getAllowedFacultyIdsForQuestion(post, qid);

      if (allowed.length){
        if (!Object.keys(block).length) return `Please give at least one rating for Question #${qid}.`;
      } else {
        if (!block || !block['0']) return `Please rate Question #${qid} (Overall).`;
      }
    }
    return '';
  }

  async function submitPost(postKey){
    const post = state.posts.find(p => String(p?.uuid || p?.id) === String(postKey));
    if (!post){ err('Feedback post not found.'); return; }

    const msg = validateBeforeSubmit(post);
    if (msg){ err(msg); return; }

    const idOrUuid = post.uuid || post.id;
    const payload = { answers: collectPayloadAnswers(postKey), metadata: null };

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.submit(idOrUuid), {
        method: 'POST',
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      }, 25000);

      const js = await res.json().catch(()=> ({}));
      if (res.status === 401){ window.location.href='/'; return; }

      if (!res.ok || js?.success === false){
        throw new Error(js?.message || 'Submit failed');
      }

      ok((js?.message || '').toLowerCase() === 'updated'
        ? 'Feedback updated successfully'
        : 'Feedback submitted successfully');

      await loadBase(true);

      // reopen same post if present
      const root = $('accordionsRoot');
      const btn = root?.querySelector(`button[data-postkey="${CSS.escape(String(postKey))}"]`);
      if (btn){
        const targetSel = btn.getAttribute('data-bs-target');
        const col = targetSel ? root.querySelector(targetSel) : null;
        const inst = col ? bootstrap.Collapse.getOrCreateInstance(col, { toggle:false }) : null;
        if (inst) inst.show();
      }

    }catch(ex){
      err(ex?.name === 'AbortError' ? 'Request timed out' : (ex?.message || 'Submit failed'));
    }finally{
      showLoading(false);
    }
  }

  async function loadBase(){
    const [resAvail, resQ, resU] = await Promise.all([
      fetchWithTimeout(API.available(), { headers: authHeaders() }, 20000),
      fetchWithTimeout(API.questionsCurrent(), { headers: authHeaders() }, 20000),
      fetchWithTimeout(API.users(), { headers: authHeaders() }, 20000),
    ]);

    if (resAvail.status === 401 || resQ.status === 401 || resU.status === 401){
      window.location.href = '/';
      return;
    }

    const jsAvail = await resAvail.json().catch(()=> ({}));
    const jsQ     = await resQ.json().catch(()=> ({}));
    const jsU     = await resU.json().catch(()=> ({}));

    if (!resAvail.ok) throw new Error(jsAvail?.message || 'Failed to load available posts');
    if (!resQ.ok) throw new Error(jsQ?.message || 'Failed to load questions');
    if (!resU.ok) throw new Error(jsU?.message || 'Failed to load users');

    state.posts = normalizeList(jsAvail) || [];
    state.questions = normalizeList(jsQ) || [];
    state.users = (normalizeList(jsU) || []).filter(u => String(u?.status || 'active').toLowerCase() !== 'inactive');

    updateCounts();
    renderPostsAccordion();
  }

  function bindFilters(){
    $('postFilters').addEventListener('click', (e) => {
      const pill = e.target.closest('.filter-pill');
      if (!pill) return;

      state.filter = pill.dataset.filter || 'all';
      $('postFilters').querySelectorAll('.filter-pill')
        .forEach(x => x.classList.toggle('active', x === pill));

      renderPostsAccordion();
    });
  }

  function bindStarClicks(){
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.starbtn');
      if (!btn) return;

      const postKey = btn.dataset.post;
      const qid = idNum(btn.dataset.qid);
      const fid = idNum(btn.dataset.fid);
      const val = idNum(btn.dataset.val);
      if (!postKey || qid === null || fid === null || val === null) return;

      ensureRatingSlot(postKey, qid, fid);
      state.ratingsByPost[postKey][qid][fid] = val;

      // repaint stars in that chip only
      const bar = btn.closest('.starbar');
      if (bar){
        bar.querySelectorAll('.starbtn').forEach(s => {
          const v = idNum(s.dataset.val);
          s.classList.toggle('is-on', v && v <= val);
        });
      }

      // update compact score text (just "—" or "3/5")
      syncMiniScore(postKey, qid, fid);
    });
  }

  function bindSubmitButtons(){
    document.addEventListener('click', (e) => {
      const b = e.target.closest('.fb-post-submit-btn');
      if (!b) return;
      const postKey = b.dataset.post;
      if (!postKey){ err('Invalid post.'); return; }
      submitPost(postKey);
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()){ window.location.href='/'; return; }

    bindFilters();
    bindStarClicks();
    bindSubmitButtons();

    $('btnRefresh').addEventListener('click', async () => {
      showLoading(true);
      try{ await loadBase(); ok('Refreshed'); }
      catch(ex){ err(ex?.message || 'Refresh failed'); }
      finally{ showLoading(false); }
    });

    showLoading(true);
    try{ await loadBase(); }
    catch(ex){ err(ex?.message || 'Initialization failed'); }
    finally{ showLoading(false); }
  });
})();
</script>
@endpush
