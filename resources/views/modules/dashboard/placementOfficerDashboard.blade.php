{{-- resources/views/modules/dashboard/placementOfficerDashboard.blade.php --}}
@section('title','Placement Officer Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Placement Officer Dashboard (MSIT theme)
 * - Fully dynamic from GET /api/placement-officer/dashboard (default)
 * - If user has NO department selected -> show ONLY basic details (hide dept sections)
 * - Same UI language as Admin/HOD/TA dashboards for theme consistency
 * ========================= */

.po-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}

/* Hero */
.po-hero{
  position:relative;
  border-radius:22px;
  padding:20px 20px;
  color:#fff;
  overflow:hidden;
  box-shadow:var(--shadow-3);
  background:linear-gradient(135deg,
    var(--primary-color) 0%,
    color-mix(in oklab, var(--primary-color) 70%, #f59e0b) 100%);
  border:1px solid color-mix(in oklab, #fff 15%, transparent);
}
.po-hero::before{
  content:'';
  position:absolute;right:-80px;top:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle, rgba(255,255,255,.14) 0%, rgba(255,255,255,0) 70%);
}
.po-hero-inner{position:relative;z-index:1;display:flex;gap:14px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap}
.po-hero-left{min-width:260px;flex:1}
.po-hero-title{font-size:26px;font-weight:800;letter-spacing:-.2px;margin:0;font-family:var(--font-head)}
.po-hero-sub{margin:8px 0 0;font-size:14px;opacity:.92}
.po-hero-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
.po-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 12px;border-radius:999px;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.16);
  font-size:13px;
}
.po-chip i{opacity:.95}

/* Grid */
.po-grid{margin-top:14px;display:grid;grid-template-columns:repeat(12, minmax(0,1fr));gap:14px;align-items:stretch}
.po-grid > div{display:flex}
.po-grid > div > .po-card{width:100%;height:100%}

.po-col-4{grid-column:span 4}
.po-col-8{grid-column:span 8}
.po-col-6{grid-column:span 6}
.po-col-12{grid-column:span 12}

/* Cards */
.po-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.po-card-head{
  padding:14px 16px;
  border-bottom:1px solid var(--line-soft);
  display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.po-card-title{
  display:flex;align-items:center;gap:10px;
  font-weight:800;color:var(--ink);
  font-family:var(--font-head);
}
.po-card-sub{font-size:12.5px;color:var(--muted-color);margin-top:3px}
.po-card-body{padding:14px 16px}
.po-card-foot{
  padding:12px 16px;
  border-top:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
}

/* KPI */
.kpi{
  display:flex;gap:12px;align-items:center;
  padding:12px 12px;border-radius:14px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 96%, transparent);
}
.kpi .ico{
  width:40px;height:40px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--primary-color);
  flex:0 0 auto;
}
.kpi .num{font-size:20px;font-weight:900;color:var(--ink);line-height:1}
.kpi .lbl{font-size:12.5px;color:var(--muted-color);margin-top:4px}
.kpi .sub{font-size:12.5px;color:var(--muted-color);margin-top:6px}
.kpi .right{margin-left:auto;text-align:right}
.kpi .badge{
  font-size:11px;font-weight:800;
  padding:4px 8px;border-radius:999px;
  border:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--accent-color) 10%, transparent);
  color:var(--secondary-color);
}

/* Profile list */
.po-kv{
  border:1px solid var(--line-soft);
  border-radius:14px;
  padding:12px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  display:grid;
  grid-template-columns:1fr;
  gap:10px;
}
.po-kv .item{
  padding:10px 12px;
  border:1px dashed var(--line-soft);
  border-radius:12px;
}
.po-kv .k{font-size:12px;color:var(--muted-color)}
.po-kv .v{font-weight:800;color:var(--ink);word-break:break-word}
@media (min-width: 768px){
  .po-kv{grid-template-columns:repeat(2, minmax(0,1fr))}
}

/* Chart */
.chart-wrap{
  width:100%;
  border:1px solid var(--line-soft);
  border-radius:14px;
  padding:10px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
}
.chart-canvas{width:100%;height:280px}

/* Table */
.table-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:auto;
}
.table{margin:0}

/* Empty */
.po-empty{
  border:1px dashed var(--line-strong);
  border-radius:14px;
  padding:12px;
  color:var(--muted-color);
  background:color-mix(in oklab, var(--surface) 96%, transparent);
}

/* Skeleton */
.skel{
  background:linear-gradient(90deg, #00000010, #00000005, #00000010);
  border-radius:10px;
  height:14px;
  animation:sh 1.2s linear infinite;
  background-size:200% 100%;
}
@keyframes sh{0%{background-position:0 0}100%{background-position:-200% 0}}
.skel.h28{height:28px}
.skel.w60{width:60%}
.skel.w40{width:40%}
.skel.w80{width:80%}

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

@media (max-width: 992px){
  .po-col-8,.po-col-6,.po-col-4{grid-column:span 12}
  .chart-canvas{height:240px}
}
</style>
@endpush

@section('content')
{{-- data-endpoint can be overridden from controller if needed --}}
<div class="po-wrap" id="placeOfficerDashWrap" data-endpoint="{{ $endpoint ?? '/api/placement-officer/dashboard' }}">

  {{-- hidden debug hook (kept for JS compatibility; not shown in UI) --}}
  <code id="endpointHint" class="d-none"></code>

  {{-- overlay loader --}}
  <div id="inlineLoader" class="inline-loader">
    @include('partials.overlay')
  </div>

  {{-- HERO --}}
  <div class="po-hero">
    <div class="po-hero-inner">
      <div class="po-hero-left">
        <h1 class="po-hero-title" id="heroTitle">Welcome 👋</h1>
        <div class="po-hero-sub" id="heroSub">Loading your dashboard…</div>

        <div class="po-hero-meta">
          <span class="po-chip"><i class="fa-solid fa-briefcase"></i> <span id="chipRole">—</span></span>
          <span class="po-chip"><i class="fa-solid fa-building-user"></i> <span id="chipDept">—</span></span>
          <span class="po-chip"><i class="fa-regular fa-clock"></i> <span id="chipUpdated">—</span></span>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-light" id="btnRefresh" type="button">
          <i class="fa-solid fa-rotate"></i> Refresh
        </button>
      </div>
    </div>
  </div>

  <div class="po-grid">

    {{-- BASIC DETAILS (always visible) --}}
    <div class="po-col-12" id="basicDetailsCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-id-card"></i> Basic Details</div>
            <div class="po-card-sub" id="basicSub">Your profile summary</div>
          </div>
        </div>
        <div class="po-card-body">
          <div id="basicBox" class="po-kv">
            <div class="skel w80"></div>
            <div class="skel w60"></div>
            <div class="skel w40"></div>
            <div class="skel w80"></div>
          </div>

          <div class="po-empty mt-3 d-none" id="deptMissingNote">
            <i class="fa-regular fa-circle-info me-1"></i>
            Department is not selected. Set your department to unlock department-wise dashboard.
          </div>
        </div>
        <div class="po-card-foot">
          <div class="small text-muted" id="basicHint">—</div>
          <a class="btn btn-sm btn-outline-primary" id="btnProfile" href="#" style="border-radius:10px">
            <i class="fa-solid fa-user-pen"></i> Update Profile
          </a>
        </div>
      </div>
    </div>

    {{-- DEPARTMENT KPIs (hidden if no dept) --}}
    <div class="po-col-12 d-none" id="deptKpiCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-chart-line"></i> Department Overview</div>
            <div class="po-card-sub">Placement KPIs (dynamic)</div>
          </div>
        </div>
        <div class="po-card-body">
          <div class="row g-3" id="kpiRow">
            @for($i=0;$i<4;$i++)
              <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi">
                  <div class="ico"><i class="fa-solid fa-spinner fa-spin"></i></div>
                  <div style="flex:1">
                    <div class="skel h28 w60"></div>
                    <div class="skel w80" style="margin-top:8px"></div>
                  </div>
                </div>
              </div>
            @endfor
          </div>
        </div>
        <div class="po-card-foot">
          <div class="small text-muted">
            <i class="fa-regular fa-circle-info me-1"></i>
            KPI order & labels come from API response.
          </div>
          <div class="small text-muted" id="kpiNote">—</div>
        </div>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="po-col-6" id="quickCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
            <div class="po-card-sub">Shortcuts for placement work</div>
          </div>
        </div>
        <div class="po-card-body" id="quickActions">
          <div class="skel w80"></div>
          <div class="skel w60" style="margin-top:10px"></div>
          <div class="skel w40" style="margin-top:10px"></div>
        </div>
      </div>
    </div>

    {{-- Alerts --}}
    <div class="po-col-6" id="alertsCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-circle-exclamation"></i> Alerts</div>
            <div class="po-card-sub">Needs your attention</div>
          </div>
        </div>
        <div class="po-card-body" id="alertsBox">
          <div class="skel w80"></div>
          <div class="skel w60" style="margin-top:10px"></div>
          <div class="skel w40" style="margin-top:10px"></div>
        </div>
      </div>
    </div>

    {{-- Department Activity (hidden if no dept) --}}
    <div class="po-col-12 d-none" id="deptActivityCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-wave-square"></i> Department Activity</div>
            <div class="po-card-sub" id="activitySub">Last 7 days</div>
          </div>
        </div>
        <div class="po-card-body">
          <div class="chart-wrap">
            <canvas id="activityChart" class="chart-canvas"></canvas>
          </div>
          <div class="small text-muted mt-2" id="activityHint">—</div>
        </div>
      </div>
    </div>

    {{-- Recent Department Updates (hidden if no dept) --}}
    <div class="po-col-12 d-none" id="deptRecentCard">
      <div class="po-card">
        <div class="po-card-head">
          <div>
            <div class="po-card-title"><i class="fa-solid fa-list"></i> Recent Updates</div>
            <div class="po-card-sub" id="recentSub">Latest items</div>
          </div>
        </div>
        <div class="po-card-body">
          <div class="table-wrap">
            <table class="table table-hover align-middle">
              <thead>
                <tr id="recentHead">
                  <th>Item</th>
                  <th>Details</th>
                  <th class="text-end">Time</th>
                </tr>
              </thead>
              <tbody id="recentBody">
                <tr>
                  <td colspan="3" class="text-muted">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i> Loading…
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="small text-muted mt-2" id="recentHint">—</div>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function(){
  if (window.__PO_DASH_INIT__) return;
  window.__PO_DASH_INIT__ = true;

  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const wrap = document.getElementById('placeOfficerDashWrap');
  const preferredEndpoint = wrap?.dataset?.endpoint || '/api/placement-officer/dashboard';

  const endpointCandidates = [
    preferredEndpoint,
    '/api/placement-officer/dashboard'
  ].filter((v, i, a) => v && a.indexOf(v) === i);

  const inlineLoader = document.getElementById('inlineLoader');
  const showInlineLoading = (show) => inlineLoader?.classList.toggle('show', !!show);

  // Toasts
  const toastOk = window.bootstrap?.Toast ? new bootstrap.Toast(document.getElementById('toastSuccess')) : null;
  const toastEr = window.bootstrap?.Toast ? new bootstrap.Toast(document.getElementById('toastError')) : null;
  const ok = (m)=>{ document.getElementById('toastSuccessText').textContent = m || 'Done'; toastOk?.show(); };
  const err = (m)=>{ document.getElementById('toastErrorText').textContent = m || 'Something went wrong'; toastEr?.show(); };

  function authHeaders(extra = {}) {
    return Object.assign({ 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }, extra);
  }

  // DOM
  const heroTitle   = document.getElementById('heroTitle');
  const heroSub     = document.getElementById('heroSub');
  const chipRole    = document.getElementById('chipRole');
  const chipDept    = document.getElementById('chipDept');
  const chipUpdated = document.getElementById('chipUpdated');
  const btnRefresh  = document.getElementById('btnRefresh');
  const endpointHint= document.getElementById('endpointHint');

  const basicSub    = document.getElementById('basicSub');
  const basicHint   = document.getElementById('basicHint');
  const basicBox    = document.getElementById('basicBox');
  const btnProfile  = document.getElementById('btnProfile');
  const deptMissingNote = document.getElementById('deptMissingNote');

  const deptKpiCard = document.getElementById('deptKpiCard');
  const kpiRow      = document.getElementById('kpiRow');
  const kpiNote     = document.getElementById('kpiNote');

  const quickActions = document.getElementById('quickActions');
  const alertsBox    = document.getElementById('alertsBox');

  const deptActivityCard = document.getElementById('deptActivityCard');
  const activitySub  = document.getElementById('activitySub');
  const activityHint = document.getElementById('activityHint');

  const deptRecentCard = document.getElementById('deptRecentCard');
  const recentSub   = document.getElementById('recentSub');
  const recentHint  = document.getElementById('recentHint');
  const recentBody  = document.getElementById('recentBody');
  const recentHead  = document.getElementById('recentHead');

  // Chart
  let activityChart = null;

  function fmtDateTime(v){
    if(!v) return '—';
    try{
      const d = new Date(v);
      if(!isNaN(d.getTime())) return d.toLocaleString();
    }catch(_){}
    return (v || '').toString();
  }

  function safeStr(x, fallback='—'){
    const s = (x ?? '').toString().trim();
    return s ? s : fallback;
  }

  function isArr(x){ return Array.isArray(x); }
  function isObj(x){ return x && typeof x === 'object' && !Array.isArray(x); }

  function normalizeDashboardPayload(raw){
    const root = isObj(raw) ? raw : {};
    const data = isObj(root.data) ? root.data : (isObj(root) && !('success' in root) ? root : {});
    return data;
  }

  function friendlyHeroSub(rawSub, hasDept, deptName){
    const s = (rawSub ?? '').toString().trim();
    if (!hasDept) {
      return `You haven’t selected a department yet. Update your profile to unlock department-wise dashboard.`;
    }
    if (!s) {
      return deptName ? `Here’s what’s happening in ${deptName} today.` : `Here’s what’s happening in your department today.`;
    }
    const lower = s.toLowerCase();
    if (lower.includes('department scoped access') || lower.includes('dept id') || lower.includes('department_id')) {
      return deptName ? `You’re viewing updates for ${deptName}.` : `You’re viewing updates for your department.`;
    }
    return s;
  }

  function toggle(el, show){
    if (!el) return;
    el.classList.toggle('d-none', !show);
  }

  function renderBasicDetails(data, hero, deptObj, hasDept){
    const user = isObj(data.user) ? data.user : (isObj(hero.user) ? hero.user : {});
    const name  = safeStr(hero.user_name || hero.name || user.name || data.profile?.name || data.name || '');
    const email = safeStr(user.email || data.email || hero.email || '');
    const phone = safeStr(user.phone || user.mobile || data.phone || data.mobile || '');
    const role  = safeStr(hero.role || data.role || 'placement_officer');
    const deptName = hasDept ? safeStr(deptObj?.name || deptObj?.title || data.department_name || hero.department_name || '') : 'Not selected';

    const joined = fmtDateTime(user.created_at || data.created_at || '');
    const updated = fmtDateTime(user.updated_at || data.updated_at || hero.updated_at || '');

    basicSub.textContent = hasDept ? 'Profile + department context' : 'Profile (department not selected)';

    basicBox.innerHTML = `
      <div class="item">
        <div class="k">Name</div>
        <div class="v">${safeStr(name,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Email</div>
        <div class="v">${safeStr(email,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Phone</div>
        <div class="v">${safeStr(phone,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Role</div>
        <div class="v">${safeStr(role,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Department</div>
        <div class="v">${safeStr(deptName,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Joined</div>
        <div class="v">${safeStr(joined,'—')}</div>
      </div>
      <div class="item">
        <div class="k">Last Updated</div>
        <div class="v">${safeStr(updated,'—')}</div>
      </div>
    `;

    basicHint.textContent = safeStr(
      data.basic_hint || data.profile_hint || hero.hint || '',
      hasDept ? 'You can manage placement operations based on permissions.' : 'Select department to see department-wise placement data.'
    );

    const profileUrl =
      data.profile_url ||
      data.quick_profile_url ||
      hero.profile_url ||
      '/placement-officer/profile';
    btnProfile.setAttribute('href', profileUrl);
  }

  function renderKPIs(kpis){
    if(!isArr(kpis) || !kpis.length){
      kpiRow.innerHTML = `
        <div class="col-12">
          <div class="po-empty">
            <i class="fa-regular fa-circle-info me-1"></i>
            No KPI data returned from API.
          </div>
        </div>`;
      return;
    }

    kpiRow.innerHTML = kpis.map(k => {
      const icon = safeStr(k.icon, 'fa-chart-simple');
      const label = safeStr(k.label, '—');
      const value = (k.value ?? '—');
      const sub = safeStr(k.sub, '');
      const badge = safeStr(k.badge, '');
      return `
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi">
            <div class="ico"><i class="fa-solid ${icon}"></i></div>
            <div style="flex:1">
              <div class="num">${value}</div>
              <div class="lbl">${label}</div>
              ${sub ? `<div class="sub">${sub}</div>` : ``}
            </div>
            ${badge ? `<div class="right"><span class="badge">${badge}</span></div>` : ``}
          </div>
        </div>
      `;
    }).join('');
  }

  function renderQuickActions(actions, hasDept){
    if(!isArr(actions) || !actions.length){
      // ✅ fallback only if API doesn’t provide quick_actions
      actions = hasDept ? [
        { title:'Update Profile', url:'/placement-officer/profile', icon:'fa-user-pen', hint:'Manage your account details' },
        { title:'Open Placement Work', url:'#', icon:'fa-briefcase', hint:'Go to placement-related modules' },
      ] : [
        { title:'Update Profile', url:'/placement-officer/profile', icon:'fa-user-pen', hint:'Select your department to unlock dashboard' },
      ];
    }

    quickActions.innerHTML = `
      <div class="d-flex flex-column gap-2">
        ${actions.map(a => {
          const title = safeStr(a.title, 'Action');
          const href  = safeStr(a.url, '#');
          const icon  = safeStr(a.icon, 'fa-arrow-right');
          const hint  = safeStr(a.hint, '');
          return `
            <a class="d-flex align-items-start gap-2 p-2 rounded-1 shadow-1"
               href="${href}"
               style="border:1px solid var(--line-soft);background:color-mix(in oklab, var(--surface) 96%, transparent)">
              <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
                          border:1px solid var(--line-strong);color:var(--primary-color);
                          background:color-mix(in oklab, var(--primary-color) 10%, transparent)">
                <i class="fa-solid ${icon}"></i>
              </div>
              <div style="flex:1">
                <div style="font-weight:800;color:var(--ink);font-family:var(--font-head)">${title}</div>
                ${hint ? `<div class="small text-muted">${hint}</div>` : ``}
              </div>
              <i class="fa-solid fa-chevron-right text-muted" style="margin-top:6px"></i>
            </a>
          `;
        }).join('')}
      </div>
    `;
  }

  function renderAlerts(alerts, hasDept){
    if(!isArr(alerts) || !alerts.length){
      if (!hasDept) {
        alerts = [{
          type: 'info',
          icon: 'fa-circle-info',
          title: 'Department not selected',
          sub: 'Update your profile and select a department to view department-wise alerts.'
        }];
      } else {
        alertsBox.innerHTML = `<div class="po-empty">No alerts 🎉</div>`;
        return;
      }
    }

    alertsBox.innerHTML = `
      <div class="d-flex flex-column gap-2">
        ${alerts.map(x => {
          const type = (x.type || 'info').toString();
          const icon = safeStr(x.icon, 'fa-circle-info');
          const title = safeStr(x.title, 'Alert');
          const sub = safeStr(x.sub, '');
          const badgeClass =
            type === 'danger' ? 'badge-soft-danger' :
            type === 'warning' ? 'badge-soft-warning' :
            type === 'success' ? 'badge-soft-success' : 'badge-soft-info';

          return `
            <div class="d-flex align-items-start gap-2 p-2 rounded-1"
                 style="border:1px solid var(--line-soft);background:color-mix(in oklab, var(--surface) 96%, transparent)">
              <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
                          border:1px solid var(--line-strong);color:var(--secondary-color);
                          background:color-mix(in oklab, var(--accent-color) 10%, transparent)">
                <i class="fa-solid ${icon}"></i>
              </div>
              <div style="flex:1">
                <div style="font-weight:800;color:var(--ink);font-family:var(--font-head);line-height:1.2">${title}</div>
                ${sub ? `<div class="small text-muted">${sub}</div>` : ``}
              </div>
              <span class="badge ${badgeClass}">${type.toUpperCase()}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  function renderRecent(recent){
    if(!isObj(recent) || (!isArr(recent.rows) || !recent.rows.length)){
      recentBody.innerHTML = `
        <tr><td colspan="3" class="text-muted">
          <i class="fa-regular fa-circle-info me-1"></i> No recent rows returned.
        </td></tr>`;
      return;
    }

    const columns = isArr(recent.columns) && recent.columns.length
      ? recent.columns
      : [
          { key:'title',  label:'Item' },
          { key:'detail', label:'Details' },
          { key:'time',   label:'Time', align:'end' }
        ];

    recentHead.innerHTML = columns.map(c => {
      const label = safeStr(c.label, c.key || '—');
      const align = (c.align || '').toString().toLowerCase();
      const cls = align === 'end' ? 'text-end' : (align === 'center' ? 'text-center' : '');
      return `<th class="${cls}">${label}</th>`;
    }).join('');

    recentBody.innerHTML = recent.rows.map(r => {
      return `<tr>${
        columns.map(c => {
          const align = (c.align || '').toString().toLowerCase();
          const cls = align === 'end' ? 'text-end' : (align === 'center' ? 'text-center' : '');
          const val = (r && c.key in r) ? r[c.key] : '';
          return `<td class="${cls}">${safeStr(val,'—')}</td>`;
        }).join('')
      }</tr>`;
    }).join('');
  }

  function renderActivity(activity){
    const labels = isArr(activity?.labels) ? activity.labels : [];
    const values = isArr(activity?.values) ? activity.values : [];

    let L = labels, V = values;
    if ((!L.length || !V.length) && isArr(activity?.points)) {
      L = activity.points.map(p => p.label);
      V = activity.points.map(p => p.value);
    }

    activitySub.textContent  = safeStr(activity?.sub, 'Activity');
    activityHint.textContent = safeStr(activity?.hint, '');

    const ctx = document.getElementById('activityChart');
    if (!ctx) return;

    if (activityChart) { try { activityChart.destroy(); } catch(_) {} activityChart = null; }

    if (!L.length || !V.length) {
      activityChart = new Chart(ctx, {
        type: 'line',
        data: { labels: ['—'], datasets: [{ label: 'No data', data: [0], tension: .35, fill: true }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
      });
      return;
    }

    activityChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: L,
        datasets: [{
          label: safeStr(activity?.label, 'Count'),
          data: V,
          tension: .35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  async function fetchDashboardFromCandidates(){
    let lastErr = null;

    for (const url of endpointCandidates) {
      try{
        if (endpointHint) endpointHint.textContent = `GET ${url}`;
        const res = await fetch(url, { headers: authHeaders() });
        const js = await res.json().catch(()=>({}));

        if(!res.ok || js.success === false){
          const msg = js.error || js.message || `Failed (${res.status})`;
          throw new Error(msg);
        }
        return { url, json: js };
      }catch(ex){
        lastErr = ex;
      }
    }
    throw lastErr || new Error('Failed to load dashboard');
  }

  async function loadDashboard(){
    showInlineLoading(true);
    try{
      const { url, json } = await fetchDashboardFromCandidates();
      if (endpointHint) endpointHint.textContent = `GET ${url}`;

      const data = normalizeDashboardPayload(json);

      const hero = isObj(data.hero) ? data.hero : {};
      const deptFromHero = hero.department;

      const deptObj =
        isObj(data.department) ? data.department :
        isObj(deptFromHero) ? deptFromHero : {};

      const deptIdRaw =
        deptObj?.id ??
        deptObj?.department_id ??
        hero.department_id ??
        data.department_id ??
        data.dept_id ??
        null;

      const deptId = parseInt(deptIdRaw, 10);
      const deptNameRaw = (deptObj?.name || deptObj?.title || hero.department_name || data.department_name || '').toString().trim();
      const hasDept = (!!deptNameRaw) || (!!deptId && deptId > 0);

      const deptName = hasDept ? safeStr(deptNameRaw, '—') : 'Not selected';

      const userNameRaw =
        hero.user_name ||
        hero.name ||
        hero.user?.name ||
        data.user?.name ||
        data.profile?.name ||
        data.name ||
        '';

      const userName = safeStr(userNameRaw, '');

      heroTitle.textContent = userName ? `Welcome, ${userName} 👋` : `Welcome 👋`;
      heroSub.textContent   = friendlyHeroSub(hero.sub, hasDept, deptName);

      chipRole.textContent    = safeStr(hero.role, (data.role || 'placement_officer'));
      chipDept.textContent    = deptName;
      chipUpdated.textContent = fmtDateTime(hero.updated_at || data.updated_at || new Date().toISOString());

      // Basic details always
      renderBasicDetails(data, hero, deptObj, hasDept);
      toggle(deptMissingNote, !hasDept);

      // If NO department selected -> hide dept sections
      toggle(deptKpiCard, hasDept);
      toggle(deptActivityCard, hasDept);
      toggle(deptRecentCard, hasDept);

      if (hasDept) {
        renderKPIs(data.kpis);
        kpiNote.textContent = safeStr(data.kpi_note, '');

        renderActivity(data.activity || {});
        recentSub.textContent  = safeStr(data.recent?.sub, 'Latest items');
        recentHint.textContent = safeStr(data.recent?.hint, '');
        renderRecent(data.recent || {});
      } else {
        if (activityChart) { try { activityChart.destroy(); } catch(_) {} activityChart = null; }
      }

      // Quick actions / alerts always
      renderQuickActions(data.quick_actions, hasDept);
      renderAlerts(data.alerts, hasDept);

      ok('Dashboard loaded');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to load dashboard');

      heroSub.textContent = 'Could not load your dashboard right now.';
      chipDept.textContent = '—';

      basicBox.innerHTML = `<div class="po-empty">No data (API error).</div>`;
      quickActions.innerHTML = `<div class="po-empty">No data (API error).</div>`;
      alertsBox.innerHTML = `<div class="po-empty">No data (API error).</div>`;

      toggle(deptKpiCard, false);
      toggle(deptActivityCard, false);
      toggle(deptRecentCard, false);

      if (activityChart) { try { activityChart.destroy(); } catch(_) {} activityChart = null; }
    }finally{
      showInlineLoading(false);
    }
  }

  btnRefresh?.addEventListener('click', ()=> loadDashboard());
  loadDashboard();
})();
</script>
@endpush
