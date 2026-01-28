{{-- resources/views/modules/pages/managePageSubmenu.blade.php --}}
@extends('pages.users.layout.structure')

@php
  $psUid = 'ps_' . \Illuminate\Support\Str::random(8);

  // Web URLs (adjust if your web routes differ)
  $psCreateUrl    = url('/page/submenu/create');
  $psEditPattern  = url('/page/submenu/create') . '?edit={id}';

  // API URLs
  $apiBase         = url('/api/page-submenus');
  $apiTree         = url('/api/page-submenus/tree?only_active=1'); // kept for compatibility (not used now)
  $apiHeaderMenus  = url('/api/header-menus'); // we'll call /tree inside JS
@endphp

@push('styles')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  <style>
    /* ===== Shell ===== */
    .cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
    .panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

    /* Toolbar */
    .mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
    .mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
    .mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
    .mfa-toolbar .btn-primary{background:var(--primary-color);border:none}

    /* ✅ Header Menu badge */
    .badge-soft{
      background:var(--t-primary);
      color:#0f766e;
      border:1px solid rgba(201,75,80,.26);
      border-radius:999px;
      padding:6px 10px;
      font-size:12px;
      font-weight:800;
      display:inline-flex;
      align-items:center;
      gap:8px;
      max-width:420px;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
    }
    .pick-parent-btn{white-space:nowrap}

    /* Card */
    .table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
    .table-wrap .card-body{overflow:visible}
    .table-responsive{overflow:visible !important}
    .table{--bs-table-bg:transparent}
    .table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid(var(--line-strong));background:var(--surface)}
    .table thead.sticky-top{z-index:3}
    .table tbody tr{border-top:1px solid var(--line-soft)}
    .table tbody tr:hover{background:var(--page-hover)}
    .small{font-size:12.5px}

    /* Empty & loader */
    .empty{color:var(--muted-color)}
    .placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

    /* Badges */
    .badge.badge-success{background:var(--success-color)!important;color:#fff!important}
    .badge.badge-secondary{background:#64748b!important;color:#fff!important}

    /* ===== Tree (Unlimited hierarchy) ===== */
    .hm-tree{padding:14px}
    .hm-list{list-style:none;margin:0;padding:0}
    .hm-item{margin:0;padding:0}
    .hm-row{
      --level:0;
      display:flex;
      align-items:flex-start;
      gap:10px;
      padding:10px 12px;
      padding-left: calc(12px + (var(--level) * 18px));
      border:1px solid var(--line-soft);
      border-radius:14px;
      background:var(--surface);
      box-shadow:var(--shadow-1);
      margin-bottom:8px;
    }
    .hm-title{font-weight:600}
    .hm-meta{font-size:12px;color:var(--muted-color);margin-top:2px}
    .hm-actions{margin-left:auto;display:flex;gap:8px;align-items:center}
    .hm-actions .btn{height:34px;border-radius:10px}
    .hm-toggle{
      width:30px;height:30px;border-radius:10px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      display:inline-flex;align-items:center;justify-content:center;
      flex:0 0 auto;
      cursor:pointer;
    }
    .hm-toggle i{transition:transform .15s ease}
    .hm-item.is-collapsed > .hm-children{display:none}
    .hm-item.is-collapsed > .hm-row .hm-toggle i{transform:rotate(-90deg)}
    .hm-toggle[disabled]{opacity:.45;cursor:default}

    .drag-handle{
      display:inline-flex;align-items:center;justify-content:center;
      width:26px;height:26px;border-radius:10px;
      color:#9ca3af;cursor:grab;flex:0 0 auto;
      border:1px dashed transparent;
    }
    .hm-reorder-on .drag-handle{border-color:var(--line-soft)}
    .drag-handle:active{cursor:grabbing}
    .drag-ghost{opacity:.55}
    .drag-chosen{box-shadow:var(--shadow-2)}
    .hm-reorder-note{display:none}
    .hm-reorder-on .hm-reorder-note{display:block}

    /* ✅ Save Order button spinner state */
    .hm-btn-loading{pointer-events:none; opacity:.95}
    .hm-btn-loading .btn-spinner{display:inline-block !important}
    .hm-btn-loading .btn-icon{display:none !important}

    /* ============================
      ✅ Header Menu Picker Modal Tree
    ============================ */
    .tree-wrap{position:relative;min-height:140px}
    .tree-loader{
      position:absolute; inset:0; display:none; align-items:center; justify-content:center;
      background: color-mix(in oklab, var(--surface) 86%, transparent);
      z-index:2;
    }
    .tree-loader.show{display:flex}
    .spin{width:22px;height:22px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
    @keyframes rot{to{transform:rotate(360deg)}}

    .tree{--rad:12px}
    .tree ul{list-style:none;margin:0;padding-left:18px;border-left:1px dashed var(--line-strong)}
    .tree li{margin:4px 0 4px 0;position:relative}
    .tree-node{
      display:flex;align-items:center;gap:10px;
      padding:8px 10px;border:1px solid var(--line-strong);border-radius:var(--rad);
      background:var(--surface);
      transition:transform .12s ease, box-shadow .12s ease;
    }
    .tree-node:hover{transform:translateY(-1px);box-shadow:var(--shadow-1)}
    .tree-node.is-selected{
      border-color:color-mix(in oklab, var(--accent-color) 45%, var(--line-strong));
      box-shadow:0 0 0 3px rgba(201,75,80,.14);
    }
    .tree-node .toggle{
      width:24px;height:24px;border:1px solid var(--line-strong);border-radius:8px;
      display:inline-grid;place-items:center;cursor:pointer;flex:0 0 auto;
      background:color-mix(in oklab, var(--surface) 92%, var(--ink) 0%);
    }
    .tree-node .toggle i{transition:transform .18s ease}
    .tree-node[data-open="1"] .toggle i{transform:rotate(90deg)}
    .tree-title{font-weight:700}
    .tree-meta{font-size:12px;color:var(--muted-color)}
    .tree-actions{margin-left:auto;display:flex;gap:8px}
    .tree .children{margin-top:6px;display:none}
    .tree-node[data-open="1"] + .children{display:block}
    .tree-empty{padding:16px;border:1px dashed var(--line-strong);border-radius:12px;color:var(--muted-color);text-align:center}

    /* Dark tweaks */
    html.theme-dark .panel,
    html.theme-dark .table-wrap.card{background:#0f172a;border-color:var(--line-strong)}
    html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
    html.theme-dark .hm-row{background:#0f172a;border-color:var(--line-soft)}
    html.theme-dark .hm-toggle{background:#0f172a}
    html.theme-dark .tree-node{background:#0f172a}
    html.theme-dark .tree-node .toggle{background:#0f172a}

    /* FIX overflow issues */
    #{{ $psUid }},
    #{{ $psUid }} .table-responsive,
    #{{ $psUid }} .table-wrap,
    #{{ $psUid }} .card,
    #{{ $psUid }} .panel,
    #{{ $psUid }} .tab-content,
    #{{ $psUid }} .tab-pane,
    #{{ $psUid }} .hm-tree,
    #{{ $psUid }} .hm-list,
    #{{ $psUid }} .hm-item {
      overflow: visible !important;
      transform: none !important;
    }

    #{{ $psUid }} .hm-row { max-width: 100%; }
    #{{ $psUid }} .hm-main{
      min-width: 0;
      flex: 1 1 auto;
    }
    #{{ $psUid }} .hm-title,
    #{{ $psUid }} .hm-meta{
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    #{{ $psUid }} .mfa-toolbar .position-relative{
      min-width: min(320px, 100%) !important;
      flex: 1 1 320px;
    }

    @media (max-width: 768px){
      #{{ $psUid }} .hm-row{ flex-wrap: wrap; }
      #{{ $psUid }} .hm-actions{
        width: 100%;
        margin-left: 0;
        justify-content: flex-end;
      }
    }
  </style>
@endpush

@section('content')
  <div id="{{ $psUid }}"
       class="cm-wrap"
       data-create-url="{{ $psCreateUrl }}"
       data-edit-pattern="{{ $psEditPattern }}"
       data-api-base="{{ $apiBase }}"
       data-api-tree="{{ $apiTree }}"
       data-api-header-menus="{{ $apiHeaderMenus }}">

    {{-- ===== Global toolbar ===== --}}
    <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
      <div class="col-12 d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Manage Page Submenus</label>
        </div>
      </div>
    </div>

    {{-- ===== Tabs ===== --}}
    @php
      $tabActive   = $psUid.'_tab_active';
      $tabArchived = $psUid.'_tab_archived';
      $tabBin      = $psUid.'_tab_bin';
    @endphp

    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#{{ $tabActive }}" role="tab" aria-selected="true">
          <i class="fa-solid fa-bars me-2" aria-hidden="true"></i>
          Active Submenus
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabArchived }}" role="tab" aria-selected="false">
          <i class="fa-solid fa-box-archive me-2" aria-hidden="true"></i>
          Archived
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabBin }}" role="tab" aria-selected="false">
          <i class="fa-solid fa-trash-can me-2" aria-hidden="true"></i>
          Bin
        </a>
      </li>
    </ul>

    <div class="tab-content mb-3">

      {{-- ========== ACTIVE (TREE) ========== --}}
      <div class="tab-pane fade show active" id="{{ $tabActive }}" role="tabpanel">
        <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
          <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">

            {{-- ✅ Header Menu Picker (Tree Modal) --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <label class="text-muted small mb-0">Header Menu</label>

              <span id="hmBadge" class="badge-soft">
                <i class="fa-solid fa-bars"></i>
                <span id="hmBadgeText">Not selected</span>
              </span>

              <button class="btn btn-light pick-parent-btn" type="button" id="btnPickHeaderMenu">
                <i class="fa-solid fa-sitemap me-1"></i>Choose from tree
              </button>

              <button class="btn btn-outline-danger btn-sm" type="button" id="btnClearHeaderMenu">
                <i class="fa-solid fa-xmark me-1"></i>Clear
              </button>

              <input type="hidden" id="header_menu_id" value="">
            </div>

            <div class="d-flex align-items-center gap-2">
              <label class="text-muted small mb-0">Per page (roots)</label>
              <select class="form-select js-per-page" style="width:110px;">
                <option>10</option><option>20</option><option selected>30</option><option>50</option><option>100</option>
              </select>
            </div>

            <div class="position-relative">
              <input type="text" class="form-control ps-5 js-q" placeholder="Search title/slug/url…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

          </div>

          <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
            <button class="btn btn-primary js-reset"><i class="fa fa-rotate-left me-1"></i>Reset</button>
            <button class="btn btn-light js-reorder"><i class="fa fa-up-down-left-right me-1"></i>Reorder</button>
            <a href="{{ $psCreateUrl }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i>New Submenu</a>
          </div>
        </div>

        <div class="card table-wrap">
          <div class="card-body p-0">
            <div class="hm-tree">
              <div class="hm-reorder-note p-2 mb-2 small text-muted">
                Reorder mode is ON — drag using the handle. <b>Only sibling reordering is allowed (no parent changes).</b>
              </div>

              <div class="js-loader" style="display:none;">
                <div class="p-3">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </div>

              <div class="js-empty empty p-4 text-center" style="display:none;">
                <i class="fa fa-bars mb-2" style="font-size:32px; opacity:.6;"></i>
                <div>No page submenus found.</div>
              </div>

              <div class="js-tree"></div>

              <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 gap-2">
                <div class="text-muted small js-meta">—</div>
                <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-primary btn-sm js-save-order" style="display:none;">
                    <span class="btn-spinner spinner-border spinner-border-sm me-1" style="display:none;" aria-hidden="true"></span>
                    <i class="fa fa-floppy-disk me-1 btn-icon"></i>
                    <span class="btn-text">Save Order</span>
                  </button>
                  <button class="btn btn-light btn-sm js-cancel-order" style="display:none;">Cancel</button>
                  <nav style="position:relative; z-index:1;">
                    <ul class="pagination mb-0 js-pager"></ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ========== ARCHIVED (TABLE) ========== --}}
      <div class="tab-pane fade" id="{{ $tabArchived }}" role="tabpanel">
        <div class="card table-wrap">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-borderless align-middle mb-0">
                <thead class="sticky-top">
                  <tr>
                    <th>TITLE & SLUG</th>
                    <th style="width:22%;">HEADER MENU</th>
                    <th style="width:18%;">PARENT</th>
                    <th style="width:140px;">CREATED</th>
                    <th class="text-end" style="width:190px;">ACTIONS</th>
                  </tr>
                </thead>
                <tbody class="js-rows-archived">
                  <tr class="js-loader-archived" style="display:none;">
                    <td colspan="5" class="p-0">
                      <div class="p-4">
                        <div class="placeholder-wave">
                          <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                          <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="js-empty-archived empty p-4 text-center" style="display:none;">
              <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
              <div>No archived submenus.</div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
              <div class="text-muted small js-meta-archived">—</div>
              <nav style="position:relative; z-index:1;"><ul class="pagination mb-0 js-pager-archived"></ul></nav>
            </div>
          </div>
        </div>
      </div>

      {{-- ========== BIN (TABLE) ========== --}}
      <div class="tab-pane fade" id="{{ $tabBin }}" role="tabpanel">
        <div class="card table-wrap">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-borderless align-middle mb-0">
                <thead class="sticky-top">
                  <tr>
                    <th>TITLE & SLUG</th>
                    <th style="width:22%;">HEADER MENU</th>
                    <th style="width:18%;">PARENT</th>
                    <th style="width:140px;">DELETED AT</th>
                    <th class="text-end" style="width:230px;">ACTIONS</th>
                  </tr>
                </thead>
                <tbody class="js-rows-bin">
                  <tr class="js-loader-bin" style="display:none;">
                    <td colspan="5" class="p-0">
                      <div class="p-4">
                        <div class="placeholder-wave">
                          <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                          <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="js-empty-bin empty p-4 text-center" style="display:none;">
              <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6;"></i>
              <div>No items in Bin.</div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
              <div class="text-muted small js-meta-bin">—</div>
              <nav style="position:relative; z-index:1;"><ul class="pagination mb-0 js-pager-bin"></ul></nav>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- ✅ Header Menu Picker Modal --}}
  <div class="modal fade" id="headerMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-solid fa-bars me-2"></i>Pick Header Menu</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <button class="btn btn-light btn-sm" type="button" id="btnReloadHeaderTree">
              <span class="label"><i class="fa-solid fa-rotate"></i> Reload</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>

            <div class="input-group" style="max-width: 340px;">
              <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
              <input type="text" class="form-control" id="headerTreeSearch" placeholder="Search by title…">
            </div>
          </div>

          <div class="tree-wrap">
            <div class="tree-loader" id="headerTreeLoader">
              <div class="spin me-2"></div><span class="text-muted">Loading tree…</span>
            </div>
            <div id="headerTreeEmpty" class="tree-empty" style="display:none">No header menus found.</div>
            <div id="headerTreeRoot" class="tree"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
    <div class="toast js-ok-toast text-bg-success border-0">
      <div class="d-flex">
        <div class="toast-body js-ok-msg">Done</div>
        <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div class="toast js-err-toast text-bg-danger border-0 mt-2">
      <div class="d-flex">
        <div class="toast-body js-err-msg">Something went wrong</div>
        <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

  <script>
    (function () {
      const ROOT = document.getElementById(@json($psUid));
      if (!ROOT) return;

      if (ROOT.dataset.psInit === '1') return;
      ROOT.dataset.psInit = '1';

      const TOKEN =
        localStorage.getItem('token') ||
        sessionStorage.getItem('token') ||
        '';

      if (!TOKEN) {
        Swal.fire('Login needed', 'Your session expired. Please login again.', 'warning')
          .then(() => location.href = '/');
        return;
      }

      const API_BASE         = ROOT.dataset.apiBase;
      const API_HEADER_MENUS = ROOT.dataset.apiHeaderMenus;
      const EDIT_PATTERN     = ROOT.dataset.editPattern;

      const qs  = (sel) => ROOT.querySelector(sel);
      const qsa = (sel) => Array.from(ROOT.querySelectorAll(sel));

      const esc = (s) => {
        const m = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
        return (s==null?'':String(s)).replace(/[&<>\"'`]/g, ch => m[ch]);
      };

      const fmtDate = (iso) => {
        if (!iso) return '-';
        const d = new Date(iso);
        if (isNaN(d)) return esc(iso);
        return d.toLocaleString(undefined, {year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
      };

      const editUrl = (id) => EDIT_PATTERN.replace('{id}', encodeURIComponent(id));

      const okToastEl  = document.querySelector('.js-ok-toast');
      const errToastEl = document.querySelector('.js-err-toast');
      const okMsgEl    = document.querySelector('.js-ok-msg');
      const errMsgEl   = document.querySelector('.js-err-msg');

      const okToast  = okToastEl  ? new bootstrap.Toast(okToastEl)  : null;
      const errToast = errToastEl ? new bootstrap.Toast(errToastEl) : null;

      const ok = (m) => {
        if (okMsgEl) okMsgEl.textContent = m || 'Done';
        if (okToast) okToast.show();
        else console.log('[OK]', m);
      };

      const err = (m) => {
        if (errMsgEl) errMsgEl.textContent = m || 'Something went wrong';
        if (errToast) errToast.show();
        else console.error('[ERR]', m);
      };

      async function fetchJSON(url, opts = {}) {
        const res = await fetch(url, {
          cache: 'no-store',
          ...opts,
          headers: {
            'Authorization': 'Bearer ' + TOKEN,
            'Accept': 'application/json',
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache',
            ...(opts.headers || {})
          }
        });

        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.error || j?.message || 'Request failed');
        return j;
      }

      function setBtnBusy(btn, on) {
        if (!btn) return;
        const label = btn.querySelector('.label');
        const spin  = btn.querySelector('.spinner-border');
        btn.disabled = !!on;
        if (spin) spin.classList.toggle('d-none', !on);
        if (label) label.style.opacity = on ? '.9' : '';
      }

      /* =========================
        ✅ REQUIRED HEADER MENU CONTEXT (TREE MODAL PICKER)
      ========================= */
      const hmHidden = qs('#header_menu_id');
      const hmBadgeText = qs('#hmBadgeText');
      const btnPickHeaderMenu = qs('#btnPickHeaderMenu');
      const btnClearHeaderMenu = qs('#btnClearHeaderMenu');

      const headerMenuModal = new bootstrap.Modal(document.getElementById('headerMenuModal'));
      const headerTreeRoot = document.getElementById('headerTreeRoot');
      const headerTreeSearch = document.getElementById('headerTreeSearch');
      const headerTreeLoader = document.getElementById('headerTreeLoader');
      const headerTreeEmpty = document.getElementById('headerTreeEmpty');
      const btnReloadHeaderTree = document.getElementById('btnReloadHeaderTree');

      let headerMenuId = '';

      function getHeaderMenuId() {
        const v = parseInt(String(hmHidden.value || '').trim(), 10);
        return Number.isFinite(v) && v > 0 ? v : 0;
      }

      function setHeaderMenu(id, title) {
        hmHidden.value = id ? String(id) : '';
        headerMenuId = hmHidden.value;

        hmBadgeText.textContent = id
          ? `#${id}: ${title || 'Header menu'}`
          : 'Not selected';

        // Refresh everything
        loaded.active = false; loaded.archived = false; loaded.bin = false;
        markDirty(['active','archived','bin']);
        refreshVisible();
      }

      btnClearHeaderMenu?.addEventListener('click', () => {
        setHeaderMenu('', '');
        ok('Header menu cleared');
      });

      function closeHeaderPicker() {
        try { headerMenuModal.hide(); } catch {}
        headerTreeLoader.classList.remove('show');
        setTimeout(() => {
          document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
          document.body.classList.remove('modal-open');
          document.body.style.removeProperty('padding-right');
        }, 50);
      }

      document.getElementById('headerMenuModal')
        ?.addEventListener('hidden.bs.modal', closeHeaderPicker);

      function renderHeaderTree(nodes) {
        headerTreeRoot.innerHTML = '';
        if (!nodes || !nodes.length) {
          headerTreeEmpty.style.display = 'block';
          return;
        }
        headerTreeEmpty.style.display = 'none';

        const ul = document.createElement('ul');
        ul.className = 'm-0 p-0';

        function makeNode(n, depth=0) {
          const li = document.createElement('li');

          const node = document.createElement('div');
          node.className = 'tree-node';
          node.dataset.open = (depth <= 1 ? '1' : '0');

          if (hmHidden.value && String(n.id) === String(hmHidden.value)) {
            node.classList.add('is-selected');
          }

          const toggle = document.createElement('div');
          toggle.className = 'toggle';
          toggle.innerHTML = '<i class="fa-solid fa-chevron-right tiny"></i>';
          if (!n.children || !n.children.length) toggle.style.visibility = 'hidden';

          const title = document.createElement('div');
          title.className = 'tree-title';
          title.textContent = n.title || '-';

          const meta = document.createElement('div');
          meta.className = 'tree-meta';
          const slugText  = n.slug ? '/' + n.slug : '';
          const statusText = (n.active ? ' • active' : ' • inactive');
          meta.textContent = slugText + statusText;

          const actions = document.createElement('div');
          actions.className = 'tree-actions';

          const pickBtn = document.createElement('button');
          pickBtn.type = 'button';
          pickBtn.className = 'btn btn-sm btn-outline-primary';
          pickBtn.innerHTML = `
            <span class="label"><i class="fa-regular fa-circle-check me-1"></i>Select</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          `;

          pickBtn.addEventListener('click', () => {
            setBtnBusy(pickBtn, true);
            setHeaderMenu(n.id, n.title || '-');
            setTimeout(() => {
              setBtnBusy(pickBtn, false);
              closeHeaderPicker();
            }, 120);
          });

          actions.appendChild(pickBtn);

          node.appendChild(toggle);
          node.appendChild(title);
          node.appendChild(meta);
          node.appendChild(actions);

          li.appendChild(node);

          const childrenWrap = document.createElement('div');
          childrenWrap.className = 'children';

          if (n.children && n.children.length) {
            const inner = document.createElement('ul');
            n.children.forEach(c => inner.appendChild(makeNode(c, depth+1)));
            childrenWrap.appendChild(inner);
          } else {
            const empty = document.createElement('div');
            empty.className = 'tiny text-muted ps-2';
            empty.textContent = 'No children';
            childrenWrap.appendChild(empty);
          }

          li.appendChild(childrenWrap);

          toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = node.dataset.open === '1';
            node.dataset.open = open ? '0' : '1';
          });

          return li;
        }

        nodes.forEach(n => ul.appendChild(makeNode(n, 0)));
        headerTreeRoot.appendChild(ul);

        headerTreeSearch.value = '';
        headerTreeSearch.oninput = function () {
          const q = this.value.trim().toLowerCase();
          headerTreeRoot.querySelectorAll('.tree-node').forEach(nd => {
            const t = (nd.querySelector('.tree-title')?.textContent || '').toLowerCase();
            const m = (nd.querySelector('.tree-meta')?.textContent || '').toLowerCase();
            const match = !q || t.includes(q) || m.includes(q);
            nd.parentElement.style.display = match ? '' : 'none';
          });

          if (q) {
            headerTreeRoot.querySelectorAll('.tree-node').forEach(nd => nd.dataset.open = '1');
          }
        };
      }

      async function loadHeaderMenuTree(autoPickFirst = false) {
        headerTreeRoot.innerHTML = '';
        headerTreeEmpty.style.display = 'none';
        headerTreeLoader.classList.add('show');
        setBtnBusy(btnReloadHeaderTree, true);

        try {
          const j = await fetchJSON(API_HEADER_MENUS + '/tree?only_active=0&_ts=' + Date.now());
          const nodes = Array.isArray(j.data) ? j.data : [];

          renderHeaderTree(nodes);

          // ✅ Auto pick first root if required and nothing selected
          if (autoPickFirst && !getHeaderMenuId() && nodes.length) {
            const first = nodes[0];
            if (first?.id) {
              setHeaderMenu(first.id, first.title || 'Header menu');
            }
          }

        } catch (e) {
          console.error(e);
          headerTreeEmpty.style.display = 'block';
          headerTreeRoot.innerHTML = '';
          err(e.message || 'Failed to load header menus tree');
        } finally {
          headerTreeLoader.classList.remove('show');
          setBtnBusy(btnReloadHeaderTree, false);
        }
      }

      btnPickHeaderMenu?.addEventListener('click', () => {
        loadHeaderMenuTree(false);
        headerMenuModal.show();
      });

      btnReloadHeaderTree?.addEventListener('click', () => loadHeaderMenuTree(false));

      function getScopeParams() {
        const hm = getHeaderMenuId();
        return hm ? { header_menu_id: hm } : {};
      }

      /* =========================
        SMART REFRESH FLAGS
      ========================= */
      const loaded = { active:false, archived:false, bin:false };
      const dirty  = { active:false, archived:false, bin:false };

      const paneActive   = document.getElementById(@json($tabActive));
      const paneArchived = document.getElementById(@json($tabArchived));
      const paneBin      = document.getElementById(@json($tabBin));

      function isPaneShown(pane){
        return !!(pane && pane.classList.contains('show') && pane.classList.contains('active'));
      }

      function markDirty(keys){
        (keys || []).forEach(k => { if (k in dirty) dirty[k] = true; });
      }

      async function refreshVisible() {
        if (isPaneShown(paneActive) && (dirty.active || !loaded.active)) {
          await loadActiveTree();
        }
        if (isPaneShown(paneArchived) && (dirty.archived || !loaded.archived)) {
          await loadArchived();
        }
        if (isPaneShown(paneBin) && (dirty.bin || !loaded.bin)) {
          await loadBin();
        }
      }

      /* =========================
        ACTIVE TREE
      ========================= */
      const perPageSel   = qs('.js-per-page');
      const qInput       = qs('.js-q');
      const btnReset     = qs('.js-reset');
      const btnReorder   = qs('.js-reorder');
      const btnSaveOrd   = qs('.js-save-order');
      const btnCancelOrd = qs('.js-cancel-order');

      const treeWrap = qs('.js-tree');
      const loader   = qs('.js-loader');
      const empty    = qs('.js-empty');
      const meta     = qs('.js-meta');
      const pager    = qs('.js-pager');

      let reorderMode = false;
      let sortables = [];
      let treeAll = [];
      let activePage = 1;

      function setLoading(v) {
        loader.style.display = v ? '' : 'none';
      }

      function setSaveBtnLoading(on) {
        if (!btnSaveOrd) return;
        const sp   = btnSaveOrd.querySelector('.btn-spinner');
        const icon = btnSaveOrd.querySelector('.btn-icon');
        const txt  = btnSaveOrd.querySelector('.btn-text');

        if (!btnSaveOrd.dataset.defaultText) {
          btnSaveOrd.dataset.defaultText = (txt?.textContent || 'Save Order').trim();
        }

        btnSaveOrd.classList.toggle('hm-btn-loading', !!on);
        btnSaveOrd.disabled = !!on;

        if (sp)   sp.style.display = on ? '' : 'none';
        if (icon) icon.style.display = on ? 'none' : '';
        if (txt)  txt.textContent = on ? 'Saving…' : (btnSaveOrd.dataset.defaultText || 'Save Order');

        if (btnCancelOrd) btnCancelOrd.disabled = !!on;
        if (btnReorder)   btnReorder.disabled   = !!on;
      }

      function filterTree(nodes, term) {
        if (!term) return nodes;
        const t = term.toLowerCase();

        function nodeMatches(n) {
          const hay = [
            n.title, n.slug, n.shortcode,
            n.header_menu_title, n.header_menu_label, n.header_menu_name,
            n.description
          ].filter(Boolean).join(' ').toLowerCase();
          return hay.includes(t);
        }

        function walk(list) {
          const out = [];
          list.forEach(n => {
            const kids = Array.isArray(n.children) ? walk(n.children) : [];
            if (nodeMatches(n) || kids.length) out.push({...n, children: kids});
          });
          return out;
        }
        return walk(nodes);
      }

      function countNodes(nodes) {
        let c = 0;
        (function walk(list){
          list.forEach(n => {
            c++;
            if (n.children && n.children.length) walk(n.children);
          });
        })(nodes || []);
        return c;
      }

      function destroySortables() {
        sortables.forEach(s => { try { s.destroy(); } catch(e){} });
        sortables = [];
      }

      function initSortables() {
        destroySortables();

        qsa('.hm-list[data-parent-id]').forEach(list => {
          const s = new Sortable(list, {
            animation: 150,
            handle: '.drag-handle',
            draggable: '.hm-item',
            ghostClass: 'drag-ghost',
            chosenClass: 'drag-chosen',
            group: { name: 'ps-siblings', put: false },
            fallbackOnBody: true,
            swapThreshold: 0.65
          });
          sortables.push(s);
        });
      }

      function collectOrdersFromDOM() {
        const orders = [];

        qsa('.hm-list[data-parent-id]').forEach(list => {
          const pidRaw = list.dataset.parentId;
          const parent_id = (pidRaw === 'null' || pidRaw === '' || typeof pidRaw === 'undefined') ? null : Number(pidRaw);

          Array.from(list.children).forEach((li, idx) => {
            const id = Number(li.dataset.id);
            if (!Number.isFinite(id)) return;
            orders.push({ id, position: idx, parent_id });
          });
        });

        return orders;
      }

      function buildPager(cur, pages) {
        const li = (dis, act, label, t) =>
          `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
            <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
          </li>`;

        let html = '';
        html += li(cur<=1, false, 'Previous', cur-1);

        const w = 3;
        const s = Math.max(1, cur - w);
        const e = Math.min(pages, cur + w);

        if (s > 1) {
          html += li(false, false, 1, 1);
          if (s > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }

        for (let i = s; i <= e; i++) html += li(false, i===cur, i, i);

        if (e < pages) {
          if (e < pages-1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
          html += li(false, false, pages, pages);
        }

        html += li(cur>=pages, false, 'Next', cur+1);
        pager.innerHTML = html;

        pager.querySelectorAll('a.page-link[data-page]').forEach(a => {
          a.addEventListener('click', () => {
            const t = Number(a.dataset.page);
            if (!t || t === activePage) return;
            activePage = Math.max(1, t);
            renderActiveTree();
            window.scrollTo({top:0, behavior:'smooth'});
          });
        });
      }

      function renderNode(n, level) {
        const hasKids = Array.isArray(n.children) && n.children.length > 0;

        const li = document.createElement('li');
        li.className = 'hm-item';
        li.dataset.id = n.id;

        const row = document.createElement('div');
        row.className = 'hm-row';
        row.style.setProperty('--level', level);

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'hm-toggle';
        toggleBtn.innerHTML = `<i class="fa fa-chevron-down"></i>`;
        if (!hasKids) toggleBtn.disabled = true;

        const drag = document.createElement('span');
        drag.className = 'drag-handle';
        drag.title = reorderMode ? 'Drag to reorder' : '';
        drag.innerHTML = `<i class="fa fa-grip-vertical"></i>`;
        drag.style.display = reorderMode ? '' : 'none';

        const main = document.createElement('div');
        main.className = 'hm-main';

        const badge = n.active
          ? `<span class="badge badge-success ms-2">Active</span>`
          : `<span class="badge badge-secondary ms-2">Inactive</span>`;

        main.innerHTML = `
          <div class="hm-title">
            ${esc(n.title || '-')}
            ${badge}
            ${level>0 ? `<span class="badge badge-soft ms-2">Level ${level+1}</span>` : ''}
            ${hasKids ? `<span class="badge badge-soft ms-2">${n.children.length} child</span>` : ''}
          </div>
          <div class="hm-meta">
            ${n.slug ? `/${esc(n.slug)}` : ''}
          </div>
        `;

        const actions = document.createElement('div');
        actions.className = 'hm-actions';
        actions.innerHTML = `
          <a class="btn btn-light btn-sm" href="${editUrl(n.id)}" title="Edit">
            <i class="fa fa-pen"></i>
          </a>
          <button type="button" class="btn btn-light btn-sm" data-act="toggle" data-id="${n.id}" data-title="${esc(n.title||'')}">
            <i class="fa ${n.active ? 'fa-toggle-on text-success' : 'fa-toggle-off'}"></i>
          </button>
          <button type="button" class="btn btn-light btn-sm text-danger" data-act="delete" data-id="${n.id}" data-title="${esc(n.title||'')}">
            <i class="fa fa-trash"></i>
          </button>
        `;

        row.appendChild(toggleBtn);
        row.appendChild(drag);
        row.appendChild(main);
        row.appendChild(actions);

        li.appendChild(row);

        const kids = document.createElement('ul');
        kids.className = 'hm-list hm-children';
        kids.dataset.parentId = String(n.id);
        kids.setAttribute('data-parent-id', String(n.id));

        if (hasKids) n.children.forEach(ch => kids.appendChild(renderNode(ch, level+1)));

        toggleBtn.addEventListener('click', () => {
          if (!hasKids) return;
          li.classList.toggle('is-collapsed');
        });

        li.appendChild(kids);
        return li;
      }

      function renderActiveTree() {
        const term = (qInput.value || '').trim();
        const per = Math.max(10, Number(perPageSel.value || 30));

        const filtered = filterTree(treeAll, term);
        const totalRoots = filtered.length;
        const totalNodes = countNodes(filtered);

        const pages = Math.max(1, Math.ceil(totalRoots / per));
        if (activePage > pages) activePage = pages;

        const startIdx = (activePage - 1) * per;
        const rootsPage = filtered.slice(startIdx, startIdx + per);

        treeWrap.innerHTML = '';
        empty.style.display = (totalRoots === 0) ? '' : 'none';

        const rootList = document.createElement('ul');
        rootList.className = 'hm-list';
        rootList.dataset.parentId = 'null';
        rootList.setAttribute('data-parent-id', 'null');

        rootsPage.forEach(n => rootList.appendChild(renderNode(n, 0)));

        treeWrap.appendChild(rootList);

        meta.textContent = `Roots: ${totalRoots} • Nodes: ${totalNodes} • Page ${activePage} of ${pages}`;
        buildPager(activePage, pages);

        ROOT.classList.toggle('hm-reorder-on', reorderMode);
        btnSaveOrd.style.display = reorderMode ? '' : 'none';
        btnCancelOrd.style.display = reorderMode ? '' : 'none';

        if (reorderMode) initSortables();
        else destroySortables();
      }

      // ✅ build tree from flat list (parent_id)
      function buildTreeFromFlat(items) {
        const map = new Map();
        const roots = [];

        const rows = (items || []).map(r => ({ ...r, children: [] }));
        rows.forEach(r => map.set(Number(r.id), r));

        rows.forEach(r => {
          const pid = r.parent_id ? Number(r.parent_id) : null;
          if (pid && map.has(pid)) {
            map.get(pid).children.push(r);
          } else {
            roots.push(r);
          }
        });

        function sortRec(list) {
          list.sort((a,b) => (Number(a.position||0) - Number(b.position||0)) || (Number(a.id||0) - Number(b.id||0)));
          list.forEach(n => { if (n.children?.length) sortRec(n.children); });
        }
        sortRec(roots);

        return roots;
      }

      let activeLoadPromise = null;

      async function loadActiveTree() {
        if (activeLoadPromise) return activeLoadPromise;

        const params = getScopeParams();

        if (!params.header_menu_id) {
          treeAll = [];
          treeWrap.innerHTML = '';
          empty.style.display = '';
          meta.textContent = 'Select a header menu to view submenus';
          loaded.active = true;
          dirty.active = false;
          return;
        }

        activeLoadPromise = (async () => {
          setLoading(true);
          try {
            const per = 200;
            let page = 1;
            let all = [];

            while (true) {
              const usp = new URLSearchParams();
              usp.set('per_page', String(per));
              usp.set('page', String(page));
              usp.set('active', '1');
              usp.set('header_menu_id', String(params.header_menu_id));
              usp.set('sort', 'position');
              usp.set('direction', 'asc');
              usp.set('_ts', String(Date.now()));

              const j = await fetchJSON(API_BASE + '?' + usp.toString());
              const items = Array.isArray(j.data) ? j.data : [];

              all = all.concat(items);

              if (items.length < per) break;
              page++;
              if (page > 80) break; // safety
            }

            treeAll = buildTreeFromFlat(all);
            renderActiveTree();

            loaded.active = true;
            dirty.active = false;

          } catch (e) {
            console.error(e);
            treeAll = [];
            treeWrap.innerHTML = '';
            empty.style.display = '';
            meta.textContent = 'Failed to load';
            err(e.message || 'Load error');
          } finally {
            setLoading(false);
            activeLoadPromise = null;
          }
        })();

        return activeLoadPromise;
      }

      /* =========================
        ARCHIVED + BIN TABLES
      ========================= */
      const rowsArchived = qs('.js-rows-archived');
      const rowsBin      = qs('.js-rows-bin');

      const loaderArchived = qs('.js-loader-archived');
      const loaderBin      = qs('.js-loader-bin');

      const emptyArchived  = qs('.js-empty-archived');
      const emptyBin       = qs('.js-empty-bin');

      const metaArchived   = qs('.js-meta-archived');
      const metaBin        = qs('.js-meta-bin');

      const pagerArchived  = qs('.js-pager-archived');
      const pagerBin       = qs('.js-pager-bin');

      const state = {
        archived: { page: 1 },
        bin: { page: 1 }
      };

      function clearRows(tbody, keepSelector) {
        Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
          if (keepSelector && tr.matches(keepSelector)) return;
          tr.remove();
        });
      }

      function parentInfo(r) {
        return (r.parent_id ? `#${r.parent_id}` : 'Root');
      }

      function headerMenuInfo(r) {
        return r.header_menu_title || r.header_menu_label || r.header_menu_name || (r.header_menu_id ? ('#' + r.header_menu_id) : '-');
      }

      function archivedRow(r) {
        const tr = document.createElement('tr');
        const slugLine = r.slug ? `<span class="d-block mt-1 small text-muted"><i class="fa fa-link me-1 opacity-75"></i>/${esc(r.slug)}</span>` : '';
        tr.innerHTML = `
          <td>
            <div class="fw-semibold">${esc(r.title || '-')}</div>
            ${slugLine}
          </td>
          <td>${esc(headerMenuInfo(r))}</td>
          <td>${esc(parentInfo(r))}</td>
          <td>${fmtDate(r.created_at)}</td>
          <td class="text-end">
            <a class="btn btn-light btn-sm" href="${editUrl(r.id)}" title="Edit"><i class="fa fa-pen"></i></a>
            <button class="btn btn-light btn-sm" data-act="activate" data-id="${r.id}" data-title="${esc(r.title||'')}">
              <i class="fa fa-check-circle"></i>
            </button>
            <button class="btn btn-light btn-sm text-danger" data-act="delete" data-id="${r.id}" data-title="${esc(r.title||'')}">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        `;
        return tr;
      }

      function binRow(r) {
        const tr = document.createElement('tr');
        const slugLine = r.slug ? `<span class="d-block mt-1 small text-muted"><i class="fa fa-link me-1 opacity-75"></i>/${esc(r.slug)}</span>` : '';
        tr.innerHTML = `
          <td>
            <div class="fw-semibold">${esc(r.title || '-')}</div>
            ${slugLine}
          </td>
          <td>${esc(headerMenuInfo(r))}</td>
          <td>${esc(parentInfo(r))}</td>
          <td>${fmtDate(r.deleted_at)}</td>
          <td class="text-end">
            <button class="btn btn-light btn-sm" data-act="restore" data-id="${r.id}" data-title="${esc(r.title||'')}">
              <i class="fa fa-rotate-left"></i> Restore
            </button>
            <button class="btn btn-light btn-sm text-danger" data-act="force" data-id="${r.id}" data-title="${esc(r.title||'')}">
              <i class="fa fa-skull-crossbones"></i> Delete
            </button>
          </td>
        `;
        return tr;
      }

      function buildPagerGeneric(pagerEl, cur, pages, onPage) {
        const li = (dis, act, label, t) =>
          `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
            <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
          </li>`;

        let html = '';
        html += li(cur<=1, false, 'Previous', cur-1);

        const w = 3;
        const s = Math.max(1, cur - w);
        const e = Math.min(pages, cur + w);

        if (s > 1) {
          html += li(false, false, 1, 1);
          if (s > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }

        for (let i = s; i <= e; i++) html += li(false, i===cur, i, i);

        if (e < pages) {
          if (e < pages-1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
          html += li(false, false, pages, pages);
        }

        html += li(cur>=pages, false, 'Next', cur+1);
        pagerEl.innerHTML = html;

        pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a => {
          a.addEventListener('click', () => {
            const t = Number(a.dataset.page);
            if (!t || t === cur) return;
            onPage(t);
            window.scrollTo({top:0, behavior:'smooth'});
          });
        });
      }

      async function loadArchived() {
        loaderArchived.style.display = '';
        emptyArchived.style.display = 'none';
        metaArchived.textContent = '—';
        pagerArchived.innerHTML = '';
        clearRows(rowsArchived, '.js-loader-archived');

        try {
          const per = 30;
          const usp = new URLSearchParams();
          usp.set('per_page', per);
          usp.set('page', state.archived.page);
          usp.set('active', '0');
          usp.set('sort', 'created_at');
          usp.set('direction', 'desc');

          const params = getScopeParams();
          if (params.header_menu_id) usp.set('header_menu_id', params.header_menu_id);

          const j = await fetchJSON(API_BASE + '?' + usp.toString());
          const items = Array.isArray(j.data) ? j.data : [];
          const pag = j.pagination || {page:1, per_page: per, total: items.length};

          if (!items.length) emptyArchived.style.display = '';

          const frag = document.createDocumentFragment();
          items.forEach(r => frag.appendChild(archivedRow(r)));
          rowsArchived.appendChild(frag);

          const total = Number(pag.total || 0);
          const pages = Math.max(1, Math.ceil(total / Number(pag.per_page || per)));
          metaArchived.textContent = `Showing page ${pag.page} of ${pages} — ${total} result(s)`;

          buildPagerGeneric(pagerArchived, Number(pag.page||1), pages, (t)=>{
            state.archived.page = Math.max(1,t);
            loadArchived();
          });

          loaded.archived = true;
          dirty.archived = false;

        } catch(e) {
          console.error(e);
          emptyArchived.style.display = '';
          metaArchived.textContent = 'Failed to load';
          err(e.message || 'Load error');
        } finally {
          loaderArchived.style.display = 'none';
        }
      }

      async function loadBin() {
        loaderBin.style.display = '';
        emptyBin.style.display = 'none';
        metaBin.textContent = '—';
        pagerBin.innerHTML = '';
        clearRows(rowsBin, '.js-loader-bin');

        try {
          const per = 30;
          const usp = new URLSearchParams();
          usp.set('per_page', per);
          usp.set('page', state.bin.page);

          const j = await fetchJSON(API_BASE + '/trash?' + usp.toString());

          let items = Array.isArray(j.data) ? j.data : [];
          const pag = j.pagination || {page:1, per_page: per, total: items.length};

          const params = getScopeParams();
          if (params.header_menu_id) {
            const hmNum = Number(params.header_menu_id);
            items = items.filter(x => Number(x.header_menu_id || 0) === hmNum);
          }

          if (!items.length) emptyBin.style.display = '';

          const frag = document.createDocumentFragment();
          items.forEach(r => frag.appendChild(binRow(r)));
          rowsBin.appendChild(frag);

          const total = Number(pag.total || items.length);
          const pages = Math.max(1, Math.ceil(total / Number(pag.per_page || per)));
          metaBin.textContent = `Showing page ${pag.page} of ${pages} — ${items.length} item(s)`;

          buildPagerGeneric(pagerBin, Number(pag.page||1), pages, (t)=>{
            state.bin.page = Math.max(1,t);
            loadBin();
          });

          loaded.bin = true;
          dirty.bin = false;

        } catch(e) {
          console.error(e);
          emptyBin.style.display = '';
          metaBin.textContent = 'Failed to load';
          err(e.message || 'Load error');
        } finally {
          loaderBin.style.display = 'none';
        }
      }

      /* =========================
        EVENTS
      ========================= */
      let qTimer;
      qInput.addEventListener('input', () => {
        clearTimeout(qTimer);
        qTimer = setTimeout(() => {
          activePage = 1;
          renderActiveTree();
        }, 250);
      });

      perPageSel.addEventListener('change', () => {
        activePage = 1;
        renderActiveTree();
      });

      btnReset.addEventListener('click', () => {
        qInput.value = '';
        perPageSel.value = '30';
        activePage = 1;
        renderActiveTree();
      });

      btnReorder.addEventListener('click', () => {
        reorderMode = !reorderMode;

        btnReorder.classList.toggle('btn-primary', reorderMode);
        btnReorder.classList.toggle('btn-light', !reorderMode);
        btnReorder.innerHTML = reorderMode
          ? '<i class="fa fa-check-double me-1"></i>Reorder On'
          : '<i class="fa fa-up-down-left-right me-1"></i>Reorder';

        renderActiveTree();
      });

      btnCancelOrd.addEventListener('click', () => {
        reorderMode = false;
        btnReorder.classList.remove('btn-primary');
        btnReorder.classList.add('btn-light');
        btnReorder.innerHTML = '<i class="fa fa-up-down-left-right me-1"></i>Reorder';
        renderActiveTree();
      });

      btnSaveOrd.addEventListener('click', async () => {
        const orders = collectOrdersFromDOM();
        if (!orders.length) {
          Swal.fire('Nothing to save', 'No items found to reorder.', 'info');
          return;
        }

        setSaveBtnLoading(true);

        try {
          const res = await fetch(API_BASE + '/reorder', {
            method: 'POST',
            headers: {
              'Authorization': 'Bearer ' + TOKEN,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ orders })
          });

          const j = await res.json().catch(() => ({}));
          if (!res.ok) throw new Error(j?.error || j?.message || 'Reorder failed');

          ok('Order updated');

          reorderMode = false;
          btnReorder.classList.remove('btn-primary');
          btnReorder.classList.add('btn-light');
          btnReorder.innerHTML = '<i class="fa fa-up-down-left-right me-1"></i>Reorder';

          markDirty(['active']);
          await refreshVisible();

        } catch(e) {
          console.error(e);
          err(e.message || 'Reorder failed');
        } finally {
          setSaveBtnLoading(false);
        }
      });

      // delegated actions
      ROOT.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;

        const act = btn.dataset.act;
        const id = btn.dataset.id;
        const title = btn.dataset.title || 'this submenu';

        if (!id) return;

        if (act === 'toggle') {
          try {
            await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/toggle-active', { method: 'POST' });
            ok('Status updated');
            markDirty(['active', 'archived']);
            await refreshVisible();
          } catch(e) {
            err(e.message || 'Toggle failed');
          }
          return;
        }

        if (act === 'delete') {
          const {isConfirmed} = await Swal.fire({
            icon: 'warning',
            title: 'Delete submenu?',
            html: `"${esc(title)}" will be moved to Bin.`,
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#ef4444'
          });
          if (!isConfirmed) return;

          try {
            await fetchJSON(API_BASE + '/' + encodeURIComponent(id), { method: 'DELETE' });
            ok('Moved to Bin');
            markDirty(['active','archived','bin']);
            await refreshVisible();
          } catch(e) {
            err(e.message || 'Delete failed');
          }
          return;
        }

        if (act === 'activate') {
          try {
            await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/toggle-active', { method: 'POST' });
            ok('Submenu activated');
            markDirty(['archived','active']);
            await refreshVisible();
          } catch(e) {
            err(e.message || 'Activate failed');
          }
          return;
        }

        if (act === 'restore') {
          try {
            await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/restore', { method: 'POST' });
            ok('Restored');
            markDirty(['bin','active','archived']);
            await refreshVisible();
          } catch(e) {
            err(e.message || 'Restore failed');
          }
          return;
        }

        if (act === 'force') {
          const {isConfirmed} = await Swal.fire({
            icon:'warning',
            title:'Delete permanently?',
            html:`This cannot be undone.<br>"${esc(title)}"`,
            showCancelButton:true,
            confirmButtonText:'Delete permanently',
            confirmButtonColor:'#dc2626'
          });
          if (!isConfirmed) return;

          try {
            await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/force', { method: 'DELETE' });
            ok('Permanently deleted');
            markDirty(['bin']);
            await refreshVisible();
          } catch(e) {
            err(e.message || 'Force delete failed');
          }
          return;
        }
      });

      // tab loads
      const tabA = ROOT.querySelector('a[href="#{{ $tabActive }}"]');
      const tabR = ROOT.querySelector('a[href="#{{ $tabArchived }}"]');
      const tabB = ROOT.querySelector('a[href="#{{ $tabBin }}"]');

      tabA?.addEventListener('shown.bs.tab', () => {
        if (loaded.active && !dirty.active) return renderActiveTree();
        loadActiveTree();
      });

      tabR?.addEventListener('shown.bs.tab', () => {
        if (!loaded.archived || dirty.archived) loadArchived();
      });

      tabB?.addEventListener('shown.bs.tab', () => {
        if (!loaded.bin || dirty.bin) loadBin();
      });

      // initial
      (async () => {
        // ✅ Load menu tree once and auto-select first menu if none selected
        await loadHeaderMenuTree(true);
        await loadActiveTree();
      })();

    })();
  </script>
@endpush
