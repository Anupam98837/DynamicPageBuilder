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

.text-mini{font-size:12px;color:var(--muted-color)}
.hr-soft{border-color:var(--line-soft)!important}

.empty-state{text-align:center;padding:42px 20px}
.empty-state i{font-size:48px;color:var(--muted-color);margin-bottom:16px;opacity:.6}
.empty-state .title{font-weight:900;color:var(--ink);margin-bottom:8px}
.empty-state .subtitle{font-size:14px;color:var(--muted-color)}

/* =========================
 * ✅ Faculty SQUARE tabs + Table
 *    - Rating: radios on top row, labels under each radio
 * ========================= */

.fb-table-wrap{border:1px solid var(--line-soft);border-radius:14px;overflow:auto;max-width:100%}
.fb-table{width:100%;min-width:980px;margin:0}
.fb-table thead th{position:sticky;top:0;background:var(--surface);z-index:3;border-bottom:1px solid var(--line-strong);font-size:12px;text-transform:uppercase;letter-spacing:.04em}
.fb-table th,.fb-table td{vertical-align:top;padding:12px 12px;border-bottom:1px solid var(--line-soft)}
.fb-table tbody tr:hover{background:var(--page-hover)}

.qcell-vcenter{vertical-align: middle !important;}
.qcell-vcenter .fb-qtitle{align-items: center !important;}

.fb-qtitle{font-weight:900;color:var(--ink);display:flex;gap:10px;align-items:flex-start}

/* Faculty tabs top (square) */
.fac-tabsbar{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  border:1px solid var(--line-soft);
  background:var(--surface);
  border-radius:14px;
  padding:10px;
  margin-bottom:12px;
}
.fac-tabbtn{
  border:1px solid var(--line-strong);
  background:var(--surface);
  color:var(--ink);
  border-radius:12px;
  padding:10px 12px;
  font-weight:950;
  font-size:12px;
  cursor:pointer;
  user-select:none;
  display:inline-flex;
  align-items:center;
  gap:10px;
  max-width:320px;
  box-shadow: 0 6px 16px rgba(0,0,0,.06);
}
.fac-tabbtn:hover{transform:translateY(-1px)}
.fac-tabbtn i{opacity:.9}
.fac-tabbtn .nm{
  max-width:240px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.fac-tabbtn.active{
  background:color-mix(in oklab, var(--primary-color) 10%, var(--surface));
  border-color:color-mix(in oklab, var(--primary-color) 45%, var(--line-strong));
  box-shadow: 0 10px 22px rgba(0,0,0,.08);
}

/* Rating grid */
.rate-grid{
  display:flex;
  align-items:flex-start;
  gap:14px;
  /* flex-wrap:wrap; */
}
.rate-col{
  border-radius:12px;
  padding:10px 10px;
  cursor:pointer;
  user-select:none;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:flex-start;
  gap:8px;
}
.rate-col:hover{background:var(--page-hover)}
.rate-col input[type="radio"]{
  width:18px;height:18px;
  accent-color: var(--primary-color);
  cursor:pointer;
}
.rate-col .txt{
  font-weight:950;
  font-size:12px;
  text-align:center;
  line-height:1.15;
}

/* ✅ Color per rating (text only) */
.rate-col[data-rate="5"] .txt{ color:#16a34a; }  /* Outstanding - green */
.rate-col[data-rate="4"] .txt{ color:#22c55e; }  /* Excellent - light green */
.rate-col[data-rate="3"] .txt{ color:#0ea5e9; }  /* Good - blue */
.rate-col[data-rate="2"] .txt{ color:#f59e0b; }  /* Fair - orange */
.rate-col[data-rate="1"] .txt{ color:#ef4444; }  /* Not Satisfactory - red */

.rate-col.is-on{
  background:color-mix(in oklab, var(--primary-color) 8%, var(--surface));
  border-color:color-mix(in oklab, var(--primary-color) 30%, var(--line-soft));
}

.na-pill{
  display:inline-flex;
  align-items:center;
  gap:6px;
  border:1px dashed var(--line-soft);
  color:var(--muted-color);
  font-weight:950;
  font-size:12px;
  padding:6px 10px;
  border-radius:999px;
}

@media (max-width: 768px){
  .fbsub-panel .d-flex{flex-direction:column;gap:12px !important}
  .fb-table{min-width:860px}
  .rate-col{min-width: 105px}
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
      <div class="small text-muted">Open a post • select faculty tab • choose rating per question • Submit/Update works for that post.</div>
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
  if (window.__FEEDBACK_SUBMIT_PAGE_V11__) return;
  window.__FEEDBACK_SUBMIT_PAGE_V11__ = true;

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

  const RATING_OPTIONS = [
    { v:5, t:'Outstanding' },
    { v:4, t:'Excellent' },
    { v:3, t:'Good' },
    { v:2, t:'Fair' },
    { v:1, t:'Not Satisfactory' },
  ];

  const state = {
    posts: [],
    questions: [],
    users: [],
    filter: 'all',
    ratingsByPost: {},
    activeFacultyByPost: {},
  };

  function qLabel(q){ return String(q?.question_title || q?.title || q?.name || (`Question #${q?.id}`) || 'Question'); }
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

    if (rule === null) return [];
    if (rule === undefined) return Array.from(globalFaculty);

    const ruleObj = pickObj(rule);
    if (!ruleObj) return Array.from(globalFaculty);

    if (ruleObj.faculty_ids === null) return Array.from(globalFaculty);

    const arr = pickArray(ruleObj.faculty_ids).map(idNum).filter(Boolean);
    if (globalFaculty.size) return arr.filter(x => globalFaculty.has(x));
    return arr;
  }

  function isQuestionApplicableToFaculty(post, qid, fid){
    const allowed = getAllowedFacultyIdsForQuestion(post, qid);
    if (!allowed.length) return String(fid) === '0';
    return allowed.includes(idNum(fid));
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
    $('summaryText').textContent = 'Open a post → select faculty tab → choose rating per question → Submit/Update.';
  }

  function ensureRatingSlot(postKey, qid, fid){
    if (!state.ratingsByPost[postKey]) state.ratingsByPost[postKey] = {};
    if (!state.ratingsByPost[postKey][qid]) state.ratingsByPost[postKey][qid] = {};
    if (state.ratingsByPost[postKey][qid][fid] === undefined) state.ratingsByPost[postKey][qid][fid] = 0;
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

  function buildFacultyTabsTop(post){
    const allFaculty = facultyUsers();
    const ids = pickArray(post?.faculty_ids).map(idNum).filter(Boolean);

    if (!ids.length){
      return [{ id: 0, name: 'Overall', _overall: true }];
    }

    const tabs = ids.map(fid => {
      const u = allFaculty.find(x => String(x?.id) === String(fid));
      return { id: fid, name: u ? userLabel(u) : 'Faculty not found', _missing: !u };
    });

    return tabs.length ? tabs : [{ id: 0, name: 'Overall', _overall: true }];
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
        const hasAny = Object.keys(block).some(fid => {
          const v = parseInt(block[fid] || 0, 10);
          return v >= 1 && v <= 5;
        });
        if (!hasAny) return `Please give at least one rating for Question #${qid}.`;
      } else {
        const v = parseInt(block['0'] || 0, 10);
        if (!(v >= 1 && v <= 5)) return `Please rate Question #${qid} (Overall).`;
      }
    }
    return '';
  }

  function renderQuestionsTable(post, postKey, activeFid){
    const qIds = pickArray(post?.question_ids).map(idNum).filter(Boolean);
    const qMap = new Map((state.questions || []).map(q => [idNum(q?.id), q]));

    if (!qIds.length){
      return `
        <div class="text-center text-muted py-4">
          <i class="fa fa-circle-info me-2"></i>No questions in this feedback post.
        </div>
      `;
    }

    qIds.forEach(qid => {
      const allowed = getAllowedFacultyIdsForQuestion(post, qid);
      if (!allowed.length) ensureRatingSlot(postKey, qid, 0);
      else allowed.forEach(fid => ensureRatingSlot(postKey, qid, fid));
    });

    return `
      <div class="fb-table-wrap">
        <table class="table fb-table">
          <thead>
            <tr>
              <th style="width:520px;">Question</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody>
            ${qIds.map(qid => {
              const q = qMap.get(qid) || { id: qid, question_title: `Question #${qid}` };

              const fid = idNum(activeFid) ?? 0;
              const applicable = isQuestionApplicableToFaculty(post, qid, fid);

              if (applicable) ensureRatingSlot(postKey, qid, fid);

              const current = state.ratingsByPost?.[postKey]?.[qid]?.[fid] ?? 0;
              const name = `rate_${postKey}_${qid}_${fid}`.replace(/[^a-zA-Z0-9_]/g,'_');

              return `
                <tr>
                  <td class="qcell-vcenter">
                    <div class="fb-qtitle">
                      <i class="fa-regular fa-circle-question" style="opacity:.85;margin-top:2px"></i>
                      <div>${esc(qLabel(q))}</div>
                    </div>
                  </td>
                  <td>
                    ${applicable ? `
                      <div class="rate-grid">
                        ${RATING_OPTIONS.map(opt => {
                          const checked = (current === opt.v);
                          return `
                            <label class="rate-col ${checked ? 'is-on' : ''}" data-rate="${esc(String(opt.v))}">
                              <input type="radio"
                                name="${esc(name)}"
                                value="${esc(String(opt.v))}"
                                ${checked ? 'checked' : ''}
                                data-post="${esc(String(postKey))}"
                                data-qid="${esc(String(qid))}"
                                data-fid="${esc(String(fid))}"
                              />
                              <div class="txt">${esc(opt.t)}</div>
                            </label>
                          `;
                        }).join('')}
                      </div>
                    ` : `
                      <span class="na-pill"><i class="fa fa-ban"></i>Not applicable</span>
                    `}
                  </td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  function renderPostBody(post, postKey){
    prefillFromSubmission(postKey, post);

    const sem = semesterTitle(post);
    const sub = subjectTitle(post);

    const buttonLabel = post?.is_submitted ? 'Update' : 'Submit';
    const buttonIcon  = post?.is_submitted ? 'fa-pen-to-square' : 'fa-paper-plane';

    const submittedLine = (post?.is_submitted && post?.submission?.submitted_at)
      ? `<div class="text-mini mt-1"><i class="fa fa-check me-1" style="opacity:.8"></i>Submitted At: ${esc(String(post.submission.submitted_at))} • Editable</div>`
      : '';

    const tabs = buildFacultyTabsTop(post);
    if (!state.activeFacultyByPost[postKey]) state.activeFacultyByPost[postKey] = String(tabs?.[0]?.id ?? 0);
    const activeFid = state.activeFacultyByPost[postKey];

    return `
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
          <div class="fw-semibold"><i class="fa fa-calendar-alt me-2"></i>${esc(sem)} <span class="text-mini">•</span> <i class="fa fa-book ms-2 me-2"></i>${esc(sub)}</div>
          ${submittedLine}
        </div>

        <button class="btn btn-primary fb-post-submit-btn" data-post="${esc(String(postKey))}">
          <i class="fa ${buttonIcon} me-1"></i>${buttonLabel}
        </button>
      </div>

      <hr class="hr-soft my-3"/>

      <div class="fac-tabsbar" data-posttabs="${esc(String(postKey))}">
        ${tabs.map(t => {
          const isActive = (String(t.id) === String(activeFid));
          return `
            <button type="button"
              class="fac-tabbtn ${isActive ? 'active' : ''}"
              data-post="${esc(String(postKey))}"
              data-fid="${esc(String(t.id))}">
              ${t._overall ? `<i class="fa-solid fa-star"></i>` : `<i class="fa-solid fa-user-tie"></i>`}
              <span class="nm" title="${esc(String(t.name))}">${esc(String(t.name))}</span>
            </button>
          `;
        }).join('')}
      </div>

      <div id="tablePane_${esc(String(postKey))}">
        ${renderQuestionsTable(post, postKey, activeFid)}
      </div>
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

  function bindFacultyTabs(){
    document.addEventListener('click', (e) => {
      const b = e.target.closest('.fac-tabbtn[data-post][data-fid]');
      if (!b) return;

      const postKey = b.dataset.post;
      const fid = b.dataset.fid;

      const post = state.posts.find(p => String(p?.uuid || p?.id) === String(postKey));
      if (!post) return;

      state.activeFacultyByPost[postKey] = String(fid);

      const bar = document.querySelector(`[data-posttabs="${CSS.escape(String(postKey))}"]`);
      bar?.querySelectorAll('.fac-tabbtn').forEach(x => x.classList.toggle('active', x === b));

      const pane = $('tablePane_' + postKey);
      if (pane){
        pane.innerHTML = renderQuestionsTable(post, postKey, fid);
      }
    });
  }

  function bindRatingRadios(){
    document.addEventListener('change', (e) => {
      const r = e.target.closest('input[type="radio"][data-post][data-qid][data-fid]');
      if (!r) return;

      const postKey = r.dataset.post;
      const qid = idNum(r.dataset.qid);
      const fid = idNum(r.dataset.fid);
      const val = idNum(r.value);

      if (!postKey || qid === null || fid === null || val === null) return;

      ensureRatingSlot(postKey, qid, fid);
      state.ratingsByPost[postKey][qid][fid] = val;

      const grid = r.closest('.rate-grid');
      if (grid){
        grid.querySelectorAll('.rate-col').forEach(x => x.classList.remove('is-on'));
        const col = r.closest('.rate-col');
        if (col) col.classList.add('is-on');
      }
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
    bindFacultyTabs();
    bindRatingRadios();
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
