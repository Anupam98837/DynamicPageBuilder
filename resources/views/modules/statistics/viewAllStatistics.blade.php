{{-- resources/views/public/stats/allStatItems.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Stats</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  <style>
    /* =========================
     * Public Stats Items Only
     * ========================= */
    body{background:#f6f7fb}
    .st-wrap{max-width:980px;margin:18px auto 54px;padding:0 8px}

    /* Hero (BG image above table) */
    .st-hero{
      border-radius:18px;
      border:1px solid var(--line-strong);
      box-shadow:var(--shadow-2);
      overflow:hidden;
      background:rgba(0,0,0,.04);
      margin-bottom:12px;
    }
    html.theme-dark .st-hero{background:rgba(255,255,255,.05)}
    .st-hero .hero-bg{
      height:220px;
      background-size:cover;
      background-position:center;
      position:relative;
    }
    .st-hero .hero-bg::after{
      content:"";
      position:absolute;inset:0;
      background:linear-gradient(180deg, rgba(0,0,0,.10), rgba(0,0,0,.55));
    }
    .st-hero .hero-inner{
      position:absolute;left:0;right:0;bottom:0;
      padding:16px 16px 14px;
      color:#fff;
      z-index:2;
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
    }
    .st-hero .hero-title{
      font-weight:900;
      font-size:20px;
      margin:0;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .st-hero .hero-title .chip{
      width:42px;height:42px;border-radius:14px;
      display:flex;align-items:center;justify-content:center;
      background:rgba(255,255,255,.18);
      border:1px solid rgba(255,255,255,.22);
      backdrop-filter: blur(8px);
    }
    .st-hero .hero-sub{
      margin-top:6px;
      font-size:13px;
      opacity:.9;
    }
    .st-hero .hero-pill{
      display:inline-flex;align-items:center;gap:8px;
      padding:8px 12px;
      border-radius:999px;
      background:rgba(255,255,255,.16);
      border:1px solid rgba(255,255,255,.22);
      backdrop-filter: blur(8px);
      font-weight:900;
      font-size:12px;
    }

    /* Card + Table */
    .st-card{
      background:var(--surface);
      border:1px solid var(--line-strong);
      border-radius:18px;
      box-shadow:var(--shadow-2);
      overflow:hidden;
    }

    .table-responsive{max-height:520px}
    .table{margin:0}
    .table thead th{
      position:sticky; top:0; z-index:3;
      font-size:12px;
      letter-spacing:.04em;
      text-transform:uppercase;
      color:var(--muted-color);
      border-bottom:1px solid var(--line-strong);
      background:rgba(0,0,0,.03);
      white-space:nowrap;
      padding:14px 14px;
    }
    html.theme-dark .table thead th{background:rgba(255,255,255,.05)}

    .table tbody td{
      padding:14px 14px;
      border-bottom:1px solid rgba(0,0,0,.06);
      vertical-align:middle;
    }
    html.theme-dark .table tbody td{border-bottom:1px solid rgba(255,255,255,.08)}

    .table tbody tr:nth-child(even){background:rgba(0,0,0,.015)}
    html.theme-dark .table tbody tr:nth-child(even){background:rgba(255,255,255,.03)}
    .table tbody tr:hover{background:rgba(158,54,58,.06)}
    html.theme-dark .table tbody tr:hover{background:rgba(201,75,80,.10)}

    .muted{color:var(--muted-color)}

    .ic{
      width:44px;height:44px;border-radius:16px;
      border:1px solid var(--line-strong);
      display:flex;align-items:center;justify-content:center;
      background:rgba(0,0,0,.03);
    }
    html.theme-dark .ic{background:rgba(255,255,255,.05)}
    .ic i{font-size:16px;opacity:.95}

    .title{
      font-weight:900;
      margin:0;
      line-height:1.2;
      color:var(--text-color);
    }
    .sub{
      margin-top:3px;
      font-size:12px;
      color:var(--muted-color);
    }

    .val{
      display:inline-flex;
      align-items:center;
      justify-content:flex-end;
      gap:8px;
      font-weight:900;
      font-size:14px;
      padding:10px 12px;
      border-radius:14px;
      border:1px solid var(--line-strong);
      background:rgba(0,0,0,.02);
      min-width:140px;
    }
    html.theme-dark .val{background:rgba(255,255,255,.04)}

    /* Skeleton */
    .skeleton{
      height:14px;border-radius:8px;background:rgba(0,0,0,.06);
      animation:pulse 1.1s infinite ease-in-out
    }
    html.theme-dark .skeleton{background:rgba(255,255,255,.08)}
    @keyframes pulse{0%,100%{opacity:.55}50%{opacity:1}}
  </style>
</head>

<body>
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <div class="st-wrap">

    {{-- HERO / BG IMAGE --}}
    <div class="st-hero" id="stHero" style="display:none">
      <div class="hero-bg" id="heroBg">
        <div class="hero-inner">
          <div>
            <h2 class="hero-title">
              <span class="chip"><i class="fa-solid fa-chart-column"></i></span>
              <span id="heroTitle">Statistics</span>
            </h2>
            <div class="hero-sub" id="heroSub">Key numbers at a glance</div>
          </div>

          <div class="hero-pill" id="heroCount">
            <i class="fa-solid fa-list-check"></i>
            <span>0 items</span>
          </div>
        </div>
      </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="st-card">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="width:90px">Icon</th>
              <th>Title</th>
              <th class="text-end" style="width:240px">Value</th>
            </tr>
          </thead>
          <tbody id="tbody">
            {{-- Skeleton rows --}}
            @for($i=0;$i<6;$i++)
              <tr>
                <td><div class="skeleton" style="height:44px;width:44px;border-radius:16px"></div></td>
                <td><div class="skeleton" style="width:260px"></div></td>
                <td class="text-end"><div class="skeleton" style="width:160px;display:inline-block"></div></td>
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
    </div>

  </div>

<script>
(function () {
  var API_CURRENT = @json(url('/api/public/stats/current'));
  var API_LIST    = @json(url('/api/public/stats'));

  var tbody = document.getElementById('tbody');

  var stHero = document.getElementById('stHero');
  var heroBg = document.getElementById('heroBg');
  var heroTitle = document.getElementById('heroTitle');
  var heroSub = document.getElementById('heroSub');
  var heroCount = document.getElementById('heroCount');

  function escapeHtml(s){
    s = String(s == null ? '' : s);
    return s
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function isAbsUrl(u){
    return /^https?:\/\//i.test(String(u || ''));
  }

  function toAbsUrl(path){
    var p = String(path || '').trim();
    if (!p) return '';
    if (isAbsUrl(p)) return p;

    // make absolute using app base
    var base = @json(url('/'));
    base = String(base || '').replace(/\/+$/,'');
    p = p.replace(/^\/+/,'');
    return base + '/' + p;
  }

  function getFirstKey(obj, keys, fallback){
    if (!obj) return fallback;
    for (var i=0; i<keys.length; i++){
      var k = keys[i];
      if (obj[k] !== undefined && obj[k] !== null && String(obj[k]).trim() !== '') {
        return obj[k];
      }
    }
    return fallback;
  }

  function parseItems(statsRow){
    if (!statsRow) return [];
    var v = statsRow.stats_items_json;

    if (Object.prototype.toString.call(v) === '[object Array]') return v;

    if (typeof v === 'string') {
      try {
        var d = JSON.parse(v);
        if (Object.prototype.toString.call(d) === '[object Array]') return d;
      } catch(e) {}
    }
    return [];
  }

  function setHeroFromRow(row, itemsCount){
    if (!row) return;

    // background_image_url may be relative; make absolute
    var bg = row.background_image_url || row.background_image || '';
    bg = toAbsUrl(bg);

    if (bg) {
      heroBg.style.backgroundImage = "url('" + bg.replace(/'/g,"%27") + "')";
      stHero.style.display = '';
    } else {
      // show hero even without image (still nice)
      heroBg.style.backgroundImage = "linear-gradient(135deg, rgba(158,54,58,.95), rgba(43,15,16,.95))";
      stHero.style.display = '';
    }

    // Optional: use slug as title if you want (kept simple)
    heroTitle.textContent = 'Statistics';
    heroSub.textContent = 'Key numbers at a glance';

    heroCount.innerHTML =
      '<i class="fa-solid fa-list-check"></i>' +
      '<span>' + escapeHtml(String(itemsCount || 0)) + ' items</span>';
  }

  function render(items){
    if (!items || !items.length){
      tbody.innerHTML =
        '<tr>' +
          '<td colspan="3" class="text-center p-4">' +
            '<div class="muted">No stats items found.</div>' +
          '</td>' +
        '</tr>';
      return;
    }

    var html = '';
    for (var i=0; i<items.length; i++){
      var it = items[i] || {};

      var icon = String(getFirstKey(it, ['icon','icon_class','fa_icon','iconClass'], '')).trim();
      var title = String(getFirstKey(it, ['label','title','name'], 'Stat')).trim();
      var val = String(getFirstKey(it, ['value','count','number','val'], '-')).trim();

      var icHtml = icon
        ? '<div class="ic"><i class="' + escapeHtml(icon) + '"></i></div>'
        : '<div class="ic"><i class="fa-regular fa-star"></i></div>';

      html +=
        '<tr>' +
          '<td>' + icHtml + '</td>' +
          '<td>' +
            '<p class="title">' + escapeHtml(title) + '</p>' +
            '<div class="sub">Updated stats item</div>' +
          '</td>' +
          '<td class="text-end">' +
            '<span class="val"><i class="fa-solid fa-hashtag"></i> ' + escapeHtml(val) + '</span>' +
          '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
  }

  function showError(msg){
    tbody.innerHTML =
      '<tr>' +
        '<td colspan="3" class="text-center p-4">' +
          '<div class="text-danger fw-bold">Failed to load stats</div>' +
          '<div class="muted small">' + escapeHtml(msg || '') + '</div>' +
        '</td>' +
      '</tr>';
  }

  function fetchJson(url, cb){
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('Accept','application/json');
    xhr.onreadystatechange = function(){
      if (xhr.readyState !== 4) return;
      try {
        var json = JSON.parse(xhr.responseText || '{}');
        cb(null, xhr.status, json);
      } catch(e){
        cb(e);
      }
    };
    xhr.send();
  }

  function load(){
    // 1) current
    fetchJson(API_CURRENT, function(err, status, json){
      if (!err && status >= 200 && status < 300 && json && json.success && json.item){
        var items = parseItems(json.item);
        setHeroFromRow(json.item, items.length);
        render(items);
        return;
      }

      // 2) fallback list
      fetchJson(API_LIST, function(err2, status2, json2){
        if (!err2 && status2 >= 200 && status2 < 300 && json2 && json2.success && json2.data && json2.data.length){
          var row = json2.data[0];
          var items2 = parseItems(row);
          setHeroFromRow(row, items2.length);
          render(items2);
          return;
        }
        showError((json2 && json2.message) ? json2.message : 'No data');
      });
    });
  }

  load();
})();
</script>

</body>
</html>
