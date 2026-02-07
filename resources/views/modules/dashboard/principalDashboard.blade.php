{{-- resources/views/modules/principal/principalDashboard.blade.php --}}
@section('title','Principal Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Principal Dashboard (MSIT theme)
 * - Fully dynamic from dashboard API
 * - No static numbers
 * - Cards/charts/tables styled to match main.css
 * ========================= */

.ad-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}

/* Hero */
.ad-hero{
  position:relative;
  border-radius:22px;
  padding:20px 20px;
  color:#fff;
  overflow:hidden;
  box-shadow:var(--shadow-3);
  background:linear-gradient(135deg,
    var(--primary-color) 0%,
    color-mix(in oklab, var(--primary-color) 70%, #7c3aed) 100%);
  border:1px solid color-mix(in oklab, #fff 15%, transparent);
}
.ad-hero::before{
  content:'';
  position:absolute;right:-80px;top:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle, rgba(255,255,255,.14) 0%, rgba(255,255,255,0) 70%);
}
.ad-hero-inner{position:relative;z-index:1;display:flex;gap:14px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap}
.ad-hero-left{min-width:260px;flex:1}
.ad-hero-title{font-size:26px;font-weight:800;letter-spacing:-.2px;margin:0;font-family:var(--font-head)}
.ad-hero-sub{margin:8px 0 0;font-size:14px;opacity:.92}
.ad-hero-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
.ad-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 12px;border-radius:999px;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.16);
  font-size:13px;
}
.ad-chip i{opacity:.95}

/* Grid */
.ad-grid{margin-top:14px;display:grid;grid-template-columns:repeat(12, minmax(0,1fr));gap:14px}
.ad-col-4{grid-column:span 4}
.ad-col-8{grid-column:span 8}
.ad-col-6{grid-column:span 6}
.ad-col-12{grid-column:span 12}

/* Cards */
.ad-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.ad-card-head{
  padding:14px 16px;
  border-bottom:1px solid var(--line-soft);
  display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.ad-card-title{
  display:flex;align-items:center;gap:10px;
  font-weight:800;color:var(--ink);
  font-family:var(--font-head);
}
.ad-card-sub{font-size:12.5px;color:var(--muted-color);margin-top:3px}
.ad-card-body{padding:14px 16px}
.ad-card-foot{
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

/* Chart shell */
.chart-wrap{
  width:100%;
  border:1px solid var(--line-soft);
  border-radius:14px;
  padding:10px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
}
.chart-canvas{width:100%;height:280px}

/* Table wrap */
.table-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:auto;
}
.table{margin:0}

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
.skel.h40{height:40px}
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
  .ad-col-8,.ad-col-6,.ad-col-4{grid-column:span 12}
  .chart-canvas{height:240px}
}
</style>
@endpush

@section('content')
<div class="ad-wrap">

  {{-- overlay loader --}}
  <div id="inlineLoader" class="inline-loader">
    @include('partials.overlay')
  </div>

  {{-- HERO --}}
  <div class="ad-hero">
    <div class="ad-hero-inner">
      <div class="ad-hero-left">
        <h1 class="ad-hero-title" id="heroTitle">Welcome 👋</h1>
        <div class="ad-hero-sub" id="heroSub">Loading dashboard data…</div>

        <div class="ad-hero-meta">
          <span class="ad-chip"><i class="fa-solid fa-user-shield"></i> <span id="chipRole">—</span></span>
          <span class="ad-chip"><i class="fa-regular fa-clock"></i> <span id="chipUpdated">—</span></span>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-light" id="btnRefresh" type="button">
          <i class="fa-solid fa-rotate"></i> Refresh
        </button>
      </div>
    </div>
  </div>

  <div class="ad-grid">

    {{-- KPIs --}}
    <div class="ad-col-12">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-chart-line"></i> Overview</div>
            <div class="ad-card-sub">Key platform numbers (dynamic)</div>
          </div>
          {{-- ✅ removed "Loads from GET /api/..." text --}}
        </div>

        <div class="ad-card-body">
          <div class="row g-3" id="kpiRow">
            {{-- skeletons (replaced by JS) --}}
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

        <div class="ad-card-foot">
          <div class="small text-muted">
            <i class="fa-regular fa-circle-info me-1"></i>
            KPI order & labels come from API response.
          </div>
          <div class="small text-muted" id="kpiNote">—</div>
        </div>
      </div>
    </div>

    {{-- CHART: Activity --}}
    <div class="ad-col-8">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-wave-square"></i> Activity</div>
            <div class="ad-card-sub" id="activitySub">Last 7 days</div>
          </div>
        </div>
        <div class="ad-card-body">
          <div class="chart-wrap">
            <canvas id="activityChart" class="chart-canvas"></canvas>
          </div>
          <div class="small text-muted mt-2" id="activityHint">—</div>
        </div>
      </div>
    </div>

    {{-- RIGHT: Quick cards --}}
    <div class="ad-col-4">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
            <div class="ad-card-sub">Based on your role</div>
          </div>
        </div>
        <div class="ad-card-body" id="quickActions">
          <div class="skel w80"></div>
          <div class="skel w60" style="margin-top:10px"></div>
          <div class="skel w40" style="margin-top:10px"></div>
        </div>
      </div>

      <div class="ad-card mt-3">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-circle-exclamation"></i> Alerts</div>
            <div class="ad-card-sub">Requires attention</div>
          </div>
        </div>
        <div class="ad-card-body" id="alertsBox">
          <div class="skel w80"></div>
          <div class="skel w60" style="margin-top:10px"></div>
          <div class="skel w40" style="margin-top:10px"></div>
        </div>
      </div>
    </div>

    {{-- TABLE: Recent items --}}
    <div class="ad-col-12">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title"><i class="fa-solid fa-list"></i> Recent</div>
            <div class="ad-card-sub" id="recentSub">Latest updates</div>
          </div>
        </div>
        <div class="ad-card-body">
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
  if (window.__PRINCIPAL_DASH_INIT__) return;
  window.__PRINCIPAL_DASH_INIT__ = true;

  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

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
  const chipUpdated = document.getElementById('chipUpdated');
  const btnRefresh  = document.getElementById('btnRefresh');

  const kpiRow   = document.getElementById('kpiRow');
  const kpiNote  = document.getElementById('kpiNote');

  const activitySub  = document.getElementById('activitySub');
  const activityHint = document.getElementById('activityHint');

  const quickActions = document.getElementById('quickActions');
  const alertsBox    = document.getElementById('alertsBox');

  const recentSub  = document.getElementById('recentSub');
  const recentHint = document.getElementById('recentHint');
  const recentBody = document.getElementById('recentBody');
  const recentHead = document.getElementById('recentHead');

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
    /**
     * Supports multiple backend shapes so you don't have to rewrite front-end.
     * Best:
     * { success:true, data:{ hero:{sub,role,updated_at,user_name}, kpis:[], activity:{...}, quick_actions:[], alerts:[], recent:{...} } }
     * Minimal:
     * { success:true, data:{ ... } } or { ... }
     */
    const root = isObj(raw) ? raw : {};
    const data = isObj(root.data) ? root.data : (isObj(root) && !('success' in root) ? root : {});
    return data;
  }

  function renderKPIs(kpis){
    if(!isArr(kpis) || !kpis.length){
      kpiRow.innerHTML = `
        <div class="col-12">
          <div class="empty">
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

  function renderQuickActions(actions){
    if(!isArr(actions) || !actions.length){
      quickActions.innerHTML = `<div class="empty">No quick actions provided.</div>`;
      return;
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

  function renderAlerts(alerts){
    if(!isArr(alerts) || !alerts.length){
      alertsBox.innerHTML = `<div class="empty">No alerts 🎉</div>`;
      return;
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

    activitySub.textContent = safeStr(activity?.sub, 'Activity');
    activityHint.textContent = safeStr(activity?.hint, '');

    const ctx = document.getElementById('activityChart');
    if (!ctx) return;

    if (activityChart) {
      try { activityChart.destroy(); } catch(_) {}
      activityChart = null;
    }

    if (!L.length || !V.length) {
      activityChart = new Chart(ctx, {
        type: 'line',
        data: { labels: ['—'], datasets: [{ label: 'No data', data: [0], tension: .35, fill: true }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
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

  function getStoredName(){
    return (
      sessionStorage.getItem('user_name') ||
      localStorage.getItem('user_name') ||
      sessionStorage.getItem('name') ||
      localStorage.getItem('name') ||
      ''
    );
  }

  async function loadDashboard(){
    showInlineLoading(true);
    try{
      // ✅ same endpoint as admin dashboard (principal is allowed by backend middleware)
      const res = await fetch('/api/admin/dashboard', { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));

      if(!res.ok || js.success === false){
        throw new Error(js.error || js.message || 'Failed to load dashboard');
      }

      const data = normalizeDashboardPayload(js);

      // HERO
      const hero = isObj(data.hero) ? data.hero : {};

      const userName =
        safeStr(
          hero.user_name ||
          hero.name ||
          data.user_name ||
          data.name ||
          data.user?.name ||
          getStoredName(),
          'Principal'
        );

      heroTitle.textContent = `Welcome, ${userName} 👋`;
      heroSub.textContent = safeStr(hero.sub, '—');
      chipRole.textContent = safeStr(hero.role, (data.role || 'PRINCIPAL'));
      chipUpdated.textContent = fmtDateTime(hero.updated_at || data.updated_at || new Date().toISOString());

      // KPIs
      renderKPIs(data.kpis);
      kpiNote.textContent = safeStr(data.kpi_note, '');

      // Quick actions / alerts
      renderQuickActions(data.quick_actions);
      renderAlerts(data.alerts);

      // Activity chart
      renderActivity(data.activity || {});

      // Recent
      recentSub.textContent = safeStr(data.recent?.sub, 'Latest updates');
      recentHint.textContent = safeStr(data.recent?.hint, '');
      renderRecent(data.recent || {});

      ok('Dashboard loaded');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to load dashboard');

      heroSub.textContent = 'Failed to load dashboard data.';
      kpiRow.innerHTML = `<div class="col-12"><div class="empty">No data (API error).</div></div>`;
      quickActions.innerHTML = `<div class="empty">No data (API error).</div>`;
      alertsBox.innerHTML = `<div class="empty">No data (API error).</div>`;
      recentBody.innerHTML = `<tr><td colspan="3" class="text-muted">No data (API error).</td></tr>`;
    }finally{
      showInlineLoading(false);
    }
  }

  btnRefresh?.addEventListener('click', ()=> loadDashboard());
  loadDashboard();
})();
</script>
@endpush
