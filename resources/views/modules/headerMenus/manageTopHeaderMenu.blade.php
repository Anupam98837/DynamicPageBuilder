{{-- resources/views/modules/header/manageTopHeaderMenu.blade.php --}}
@section('title','Top Header Menus')

@php
  $thmUid = 'thm_' . \Illuminate\Support\Str::random(8);

  // ✅ APIs (change here only if your routes differ)
  $apiBase          = url('/api/top-header-menus');
  $apiTrash         = url('/api/top-header-menus/trash');
  $apiReorder       = url('/api/top-header-menus/reorder');

  // Contact Infos (global, not tied to any menu item)
  $apiContactInfos  = url('/api/top-header-menus/contact-infos'); // must return all contact infos
  $apiCiSelection   = url('/api/top-header-menus/contact-info'); // ✅ ONLY GET/PUT/DELETE (NO POST)
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* =========================
    Top Header Menus (Admin)
    - Contact Infos are GLOBAL (not per menu)
    - Create/Edit modal is ONLY for menu item
  ========================= */

  .thm-wrap{max-width:1180px;margin:16px auto 44px;padding:0 6px;overflow:visible}
  .thm-panel{
    background:var(--surface);
    border:1px solid var(--line-strong);
    border-radius:16px;
    box-shadow:var(--shadow-2);
    padding:12px;
    overflow:visible;
  }

  /* Tabs */
  .thm-tabs.nav-tabs{border-color:var(--line-strong)}
  .thm-tabs .nav-link{color:var(--ink)}
  .thm-tabs .nav-link.active{
    background:var(--surface);
    border-color:var(--line-strong) var(--line-strong) var(--surface);
  }

  /* Toolbar */
  .thm-toolbar .form-control,
  .thm-toolbar .form-select{
    height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)
  }
  .thm-toolbar .btn{border-radius:12px}
  .thm-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
  .thm-toolbar .btn-primary{background:var(--primary-color);border:none}
  .thm-toolbar .btn-outline{border:1px solid var(--line-strong);background:transparent}

  /* Table card */
  .thm-card{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    overflow:visible;
  }
  .thm-card .card-body{overflow:visible}
  .table-responsive{overflow:visible !important}
  .table{--bs-table-bg:transparent}
  .table thead th{
    font-weight:600;color:var(--muted-color);font-size:13px;
    border-bottom:1px solid(var(--line-strong));background:var(--surface)
  }
  .table thead.sticky-top{z-index:3}
  .table tbody tr{border-top:1px solid var(--line-soft)}
  .table tbody tr:hover{background:var(--page-hover)}

  .small{font-size:12.5px}
  .empty{color:var(--muted-color)}
  .placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

  /* Badges */
  .badge-soft{
    background:color-mix(in oklab, var(--muted-color) 12%, transparent);
    color:var(--ink);
    border:1px solid color-mix(in oklab, var(--muted-color) 20%, transparent);
  }
  .badge.badge-success{background:var(--success-color)!important;color:#fff!important}
  .badge.badge-secondary{background:#64748b!important;color:#fff!important}

  /* Reorder */
  .drag-handle{
    display:inline-flex;align-items:center;justify-content:center;
    width:32px;height:32px;border-radius:10px;
    border:1px dashed transparent;
    color:#9ca3af;cursor:grab;
  }
  .thm-reorder-on .drag-handle{border-color:var(--line-soft)}
  .drag-handle:active{cursor:grabbing}
  .drag-ghost{opacity:.55}
  .drag-chosen{box-shadow:var(--shadow-2)}
  .thm-reorder-note{display:none}
  .thm-reorder-on .thm-reorder-note{display:block}

  .thm-btn-loading{pointer-events:none; opacity:.95}
  .thm-btn-loading .btn-spinner{display:inline-block !important}
  .thm-btn-loading .btn-icon{display:none !important}

  /* Modal */
  .thm-modal .modal-content{
    border-radius:18px;
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-2);
    overflow:hidden;
  }
  .thm-modal .modal-header{
    background:transparent;
    border-bottom:1px solid var(--line-strong);
  }
  .thm-modal .modal-footer{
    border-top:1px solid var(--line-strong);
  }
  .thm-modal .form-control,
  .thm-modal .form-select{
    border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)
  }
  .thm-modal .input-group-text{
    border-radius:12px 0 0 12px;
    border:1px solid var(--line-strong);
    background:color-mix(in oklab, var(--surface) 90%, #000 0%);
  }

  .thm-err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .thm-err:not(:empty){display:block}

  .thm-pill{
    border:1px solid var(--line-strong);
    border-radius:999px;
    padding:3px 8px;
    font-size:12px;
    color:var(--muted-color);
  }

  .thm-switch-inline{display:flex;align-items:center;gap:10px}
  .thm-switch-inline .form-check{margin:0}
  .thm-switch-inline .form-check-label{margin:0;font-weight:600}

  .thm-section-label{
    font-size:13px;font-weight:800;text-transform:uppercase;
    letter-spacing:.06em;color:var(--muted-color);margin-bottom:6px;
  }

  /* Contact Infos (GLOBAL section) */
  .thm-contacts{
    border:1px solid var(--line-strong);
    border-radius:14px;
    background:var(--surface);
    padding:10px;
    max-height:260px;
    overflow:auto;
  }
  .thm-contact-row{
    display:flex;align-items:flex-start;gap:10px;
    padding:8px 10px;border-radius:12px;
  }
  .thm-contact-row:hover{background:var(--page-hover)}
  .thm-contact-meta{font-size:12px;color:var(--muted-color);margin-top:2px}
  .thm-contact-row.disabled{opacity:.55}
  .thm-contact-row.disabled *{cursor:not-allowed}

  .thm-ci-preview{display:flex;flex-wrap:wrap;gap:8px}
  .thm-ci-chip{
    display:inline-flex;align-items:center;gap:8px;
    border:1px solid var(--line-strong);
    background:color-mix(in oklab, var(--surface) 92%, #000 0%);
    border-radius:999px;
    padding:6px 10px;
    font-size:12.5px;
    color:var(--ink);
  }
  .thm-ci-chip i{opacity:.7}

  /* Dark tweaks */
  html.theme-dark .thm-panel,
  html.theme-dark .thm-card{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
  html.theme-dark .thm-modal .modal-content{background:#0f172a}
  html.theme-dark .thm-modal .input-group-text{background:#0b1220}

  /* Overflow safety */
  #{{ $thmUid }},
  #{{ $thmUid }} .table-responsive,
  #{{ $thmUid }} .thm-card,
  #{{ $thmUid }} .thm-panel,
  #{{ $thmUid }} .tab-content,
  #{{ $thmUid }} .tab-pane{overflow:visible !important; transform:none !important;}

  @media (max-width: 768px){
    #{{ $thmUid }} .thm-toolbar .position-relative{min-width:100% !important}
  }
</style>
@endpush

@section('content')
<div id="{{ $thmUid }}"
     class="thm-wrap"
     data-api-base="{{ $apiBase }}"
     data-api-trash="{{ $apiTrash }}"
     data-api-reorder="{{ $apiReorder }}"
     data-api-contact-infos="{{ $apiContactInfos }}"
     data-api-ci-selection="{{ $apiCiSelection }}">

  {{-- Header / Title --}}
  <div class="thm-panel thm-toolbar mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-grip-lines"></i>
      <div>
        <div class="fw-bold">Top Header Menus</div>
        <div class="small text-muted">
          Manage top bar links. Contact infos are configured below (not tied to any menu item).
        </div>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-light js-open-create">
        <i class="fa fa-plus me-1"></i>New Item
      </button>
    </div>
  </div>

  {{-- ========================= GLOBAL CONTACT INFOS (NOT IN MODAL) ========================= --}}
  <div class="thm-panel mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="fw-bold d-flex align-items-center gap-2">
          <i class="fa-solid fa-address-book"></i>
          <span>Contact Infos (Top Bar)</span>
        </div>
        <div class="small text-muted">
          Pick exactly <b>2</b> contact infos to show in the top header (e.g., Phone & Email). These are not linked to menus.
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="small text-muted">
          Selected: <span class="js-ci-main-count">0</span>/2
        </div>

        <button class="btn btn-light btn-sm js-ci-main-reload" type="button">
          <i class="fa fa-rotate me-1"></i>Reload
        </button>

        <button class="btn btn-primary btn-sm js-ci-main-save" type="button">
          <span class="btn-spinner spinner-border spinner-border-sm me-1" style="display:none;" aria-hidden="true"></span>
          <i class="fa fa-floppy-disk me-1 btn-icon"></i>
          <span class="btn-text">Save</span>
        </button>
      </div>
    </div>

    <div class="divider-soft my-3"></div>

    <div class="row g-3">
      <div class="col-12 col-lg-5">
        <div class="thm-section-label">Selected Preview</div>
        <div class="thm-ci-preview js-ci-main-preview">
          <span class="text-muted small">—</span>
        </div>

        <div class="thm-section-label mt-3">Search</div>
        <div class="position-relative">
          <input class="form-control ps-5 js-ci-main-search" placeholder="Search contact infos…">
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>

        <div class="thm-err mt-2" data-for="ci_main"></div>
        <div class="small text-muted mt-2">
          Rule: you must select <b>exactly 2</b>. Others will be locked when limit reached.
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="thm-section-label">All Contact Infos</div>
        <div class="thm-contacts js-ci-main-box">
          <div class="small text-muted js-ci-main-loading" style="display:none;">
            <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading contact infos…
          </div>
          <div class="empty js-ci-main-empty" style="display:none;">No contact infos found.</div>
          <div class="js-ci-main-list"></div>
        </div>
      </div>
    </div>
  </div>

  @php
    $tabActive   = $thmUid.'_tab_active';
    $tabArchived = $thmUid.'_tab_archived';
    $tabBin      = $thmUid.'_tab_bin';
  @endphp

  {{-- Tabs --}}
  <ul class="nav nav-tabs thm-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#{{ $tabActive }}" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabArchived }}" role="tab" aria-selected="false">
        <i class="fa-solid fa-box-archive me-2"></i>Archived
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabBin }}" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ========================= ACTIVE ========================= --}}
    <div class="tab-pane fade show active" id="{{ $tabActive }}" role="tabpanel">

      <div class="thm-panel thm-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <span class="small text-muted">Per page</span>
              <select class="form-select js-per-page" style="width:120px;">
                <option>10</option><option>20</option><option selected>30</option><option>50</option><option>100</option>
              </select>
            </div>

            <div class="position-relative" style="min-width:320px;">
              <input class="form-control ps-5 js-q" placeholder="Search title / slug / url …" />
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button class="btn btn-light js-reset"><i class="fa fa-rotate-left me-1"></i>Reset</button>
          </div>

          <div class="d-flex align-items-center gap-2 ms-auto">
            <button class="btn btn-light js-reorder"><i class="fa fa-up-down-left-right me-1"></i>Reorder</button>

            <button class="btn btn-primary btn-sm js-save-order" style="display:none;">
              <span class="btn-spinner spinner-border spinner-border-sm me-1" style="display:none;" aria-hidden="true"></span>
              <i class="fa fa-floppy-disk me-1 btn-icon"></i>
              <span class="btn-text">Save Order</span>
            </button>
            <button class="btn btn-light btn-sm js-cancel-order" style="display:none;">Cancel</button>
          </div>
        </div>

        <div class="thm-reorder-note small text-muted mt-2">
          Reorder mode is ON — drag using the handle. Order changes apply to the current (filtered) list.
        </div>
      </div>

      <div class="card thm-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:44px;"></th>
                  <th>TITLE & SLUG</th>
                  <th style="width:34%;">URL</th>
                  <th style="width:120px;">STATUS</th>
                  <th style="width:160px;">CREATED</th>
                  <th class="text-end" style="width:210px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-active">
                <tr class="js-loader-active" style="display:none;">
                  <td colspan="6" class="p-0">
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

          <div class="js-empty-active empty p-4 text-center" style="display:none;">
            <i class="fa fa-layer-group mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No active items found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small js-meta-active">—</div>
            <nav style="position:relative; z-index:1;">
              <ul class="pagination mb-0 js-pager-active"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========================= ARCHIVED ========================= --}}
    <div class="tab-pane fade" id="{{ $tabArchived }}" role="tabpanel">
      <div class="card thm-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>TITLE & SLUG</th>
                  <th style="width:34%;">URL</th>
                  <th style="width:160px;">CREATED</th>
                  <th class="text-end" style="width:220px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-archived">
                <tr class="js-loader-archived" style="display:none;">
                  <td colspan="4" class="p-0">
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
            <div>No archived items.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small js-meta-archived">—</div>
            <nav style="position:relative; z-index:1;">
              <ul class="pagination mb-0 js-pager-archived"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========================= BIN ========================= --}}
    <div class="tab-pane fade" id="{{ $tabBin }}" role="tabpanel">
      <div class="card thm-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>TITLE & SLUG</th>
                  <th style="width:34%;">URL</th>
                  <th style="width:180px;">DELETED AT</th>
                  <th class="text-end" style="width:260px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-bin">
                <tr class="js-loader-bin" style="display:none;">
                  <td colspan="4" class="p-0">
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
            <nav style="position:relative; z-index:1;">
              <ul class="pagination mb-0 js-pager-bin"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ========================= MODAL (CREATE/EDIT MENU ITEM ONLY) ========================= --}}
<div class="modal fade thm-modal" id="{{ $thmUid }}_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="fw-bold js-modal-title">New Top Header Item</div>
          <div class="small text-muted js-modal-sub">Create or edit a top header link item.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" class="js-edit-id" value="">

        <div class="thm-section-label">Item</div>
        <div class="row g-3">
          {{-- Title --}}
          <div class="col-12 col-md-6">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
              <input type="text" class="form-control js-title" maxlength="150" placeholder="e.g., Admissions, Apply Now">
            </div>
            <div class="thm-err" data-for="title"></div>
          </div>

          {{-- Slug --}}
          <div class="col-12 col-md-6">
            <div class="thm-switch-inline mb-1">
              <label class="form-label mb-0">Slug</label>
              <div class="form-check form-switch">
                <input class="form-check-input js-slug-auto" type="checkbox" checked>
              </div>
              <label class="form-check-label small text-muted">Auto from title</label>
            </div>

            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
              <input type="text" class="form-control js-slug" maxlength="160" placeholder="auto-generated" disabled>
              <button class="btn btn-light js-slug-regen" type="button" title="Regenerate from title" disabled>
                <i class="fa-solid fa-rotate"></i>
              </button>
            </div>
            <div class="small text-muted mt-1">Slug is required, auto-generated by default.</div>
            <div class="thm-err" data-for="slug"></div>
          </div>

          {{-- URL --}}
          <div class="col-12">
            <label class="form-label">URL <span class="thm-pill ms-1">optional</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
              <input type="text" class="form-control js-url" maxlength="255" placeholder="https://… or /path">
            </div>
            <div class="thm-err" data-for="url"></div>
          </div>

          {{-- Active --}}
          <div class="col-12">
            <div class="thm-switch-inline mt-1">
              <div class="form-check form-switch">
                <input class="form-check-input js-active" type="checkbox" checked>
              </div>
              <label class="form-check-label" for="">{{ __('Active') }}</label>
              <span class="small text-muted">Inactive items appear under “Archived”.</span>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>

        <button class="btn btn-primary js-save">
          <span class="btn-spinner spinner-border spinner-border-sm me-1" style="display:none;" aria-hidden="true"></span>
          <i class="fa fa-floppy-disk me-1 btn-icon"></i>
          <span class="btn-text">Save</span>
        </button>
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
(function(){
  const ROOT = document.getElementById(@json($thmUid));
  if (!ROOT) return;

  // Prevent double init
  if (ROOT.dataset.thmInit === '1') return;
  ROOT.dataset.thmInit = '1';

  const TOKEN =
    localStorage.getItem('token') ||
    sessionStorage.getItem('token') || '';

  if (!TOKEN) {
    Swal.fire('Login needed', 'Your session expired. Please login again.', 'warning')
      .then(()=> location.href = '/');
    return;
  }

  const API_BASE        = ROOT.dataset.apiBase;
  const API_TRASH       = ROOT.dataset.apiTrash;
  const API_REORDER     = ROOT.dataset.apiReorder;

  // Contact infos (global section)
  const API_CI          = ROOT.dataset.apiContactInfos;
  const API_CI_SELECT   = ROOT.dataset.apiCiSelection;

  const qs  = (s) => ROOT.querySelector(s);
  const esc = (s) => {
    const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
    return (s==null?'':String(s)).replace(/[&<>\"'`]/g, ch => m[ch]);
  };

  const fmtDate = (iso) => {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d)) return esc(iso);
    return d.toLocaleString(undefined, {year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  };

  const slugify = (str) => String(str||'')
    .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
    .toLowerCase().trim()
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/(^-|-$)/g,'');

  // Toasts
  const okToastEl  = document.querySelector('.js-ok-toast');
  const errToastEl = document.querySelector('.js-err-toast');
  const okMsgEl    = document.querySelector('.js-ok-msg');
  const errMsgEl   = document.querySelector('.js-err-msg');
  const okToast  = okToastEl  ? new bootstrap.Toast(okToastEl)  : null;
  const errToast = errToastEl ? new bootstrap.Toast(errToastEl) : null;
  const ok  = (m)=>{ if(okMsgEl) okMsgEl.textContent=m||'Done'; okToast ? okToast.show() : console.log(m); };
  const err = (m)=>{ if(errMsgEl) errMsgEl.textContent=m||'Something went wrong'; errToast ? errToast.show() : console.error(m); };

  async function fetchJSON(url, opts={}){
    const res = await fetch(url, {
      cache:'no-store',
      ...opts,
      headers: {
        'Authorization': 'Bearer ' + TOKEN,
        'Accept': 'application/json',
        ...(opts.body ? {'Content-Type':'application/json'} : {}),
        ...(opts.headers || {})
      }
    });

    const j = await res.json().catch(()=> ({}));

    if (!res.ok){
      // Better Laravel validation message support
      let msg = j?.error || j?.message || 'Request failed';
      if (j?.errors && typeof j.errors === 'object'){
        const firstKey = Object.keys(j.errors)[0];
        const firstVal = j.errors[firstKey];
        const firstMsg = Array.isArray(firstVal) ? firstVal[0] : String(firstVal || '');
        if (firstMsg) msg = firstMsg;
      }
      const e = new Error(msg);
      e.status = res.status;
      e.body = j;
      throw e;
    }

    return j;
  }

  function normalizeCIIds(v){
    if (Array.isArray(v)) return v.map(x=>Number(x)).filter(n=>Number.isFinite(n));
    if (typeof v === 'string' && v.trim() !== ''){
      try{
        const a = JSON.parse(v);
        if (Array.isArray(a)) return a.map(x=>Number(x)).filter(n=>Number.isFinite(n));
      }catch{}
    }
    return [];
  }

  /* =========================
    Tabs: smart reload
  ========================= */
  const loaded = { active:false, archived:false, bin:false };
  const dirty  = { active:false, archived:false, bin:false };

  const paneActive   = document.getElementById(@json($tabActive));
  const paneArchived = document.getElementById(@json($tabArchived));
  const paneBin      = document.getElementById(@json($tabBin));

  function isShown(p){ return !!(p && p.classList.contains('show') && p.classList.contains('active')); }
  function markDirty(keys){ (keys||[]).forEach(k => { if (k in dirty) dirty[k] = true; }); }

  async function refreshVisible(){
    if (isShown(paneActive) && (!loaded.active || dirty.active)) await loadActive();
    if (isShown(paneArchived) && (!loaded.archived || dirty.archived)) await loadArchived();
    if (isShown(paneBin) && (!loaded.bin || dirty.bin)) await loadBin();
  }

  /* =========================
    GLOBAL CONTACT INFOS SECTION
  ========================= */
  let contactInfos = [];
  let contactInfoMap = new Map(); // id -> label
  let selectedTopCI = []; // [id,id] from API

  const ciMain = {
    box: qs('.js-ci-main-box'),
    list: qs('.js-ci-main-list'),
    search: qs('.js-ci-main-search'),
    reload: qs('.js-ci-main-reload'),
    save: qs('.js-ci-main-save'),
    count: qs('.js-ci-main-count'),
    preview: qs('.js-ci-main-preview'),
    loading: qs('.js-ci-main-loading'),
    empty: qs('.js-ci-main-empty'),
    err: ROOT.querySelector('.thm-err[data-for="ci_main"]'),
  };

  function ciLabel(ci){
    return ci?.label || ci?.title || ci?.type || ci?.name || ('#' + (ci?.id ?? ''));
  }
  function ciMeta(ci){
    const bits = [];
    if (ci?.type) bits.push(ci.type);
    if (ci?.value) bits.push(ci.value);
    if (ci?.info) bits.push(ci.info);
    return bits.join(' • ');
  }

  function setCiMainError(msg){
    if (!ciMain.err) return;
    ciMain.err.textContent = msg || '';
    ciMain.err.style.display = msg ? 'block' : 'none';
  }

  async function loadContactInfos(force=false){
    if (contactInfos.length && !force) return;
    ciMain.loading.style.display = '';
    ciMain.empty.style.display = 'none';
    try{
      const sep = API_CI.includes('?') ? '&' : '?';
      const j = await fetchJSON(API_CI + sep + '_ts=' + Date.now());
      const list = Array.isArray(j) ? j : (Array.isArray(j.data) ? j.data : []);
      contactInfos = list;

      contactInfoMap = new Map();
      list.forEach(ci => {
        if (ci && ci.id != null) contactInfoMap.set(Number(ci.id), ciLabel(ci));
      });

    }catch(e){
      console.error(e);
      contactInfos = [];
      contactInfoMap = new Map();
    }finally{
      ciMain.loading.style.display = 'none';
      ciMain.empty.style.display = (contactInfos.length ? 'none' : '');
    }
  }

  async function loadSelectedTopCI(){
    selectedTopCI = [];
    if (!API_CI_SELECT) return;

    try{
      const sep = API_CI_SELECT.includes('?') ? '&' : '?';
      const j = await fetchJSON(API_CI_SELECT + sep + '_ts=' + Date.now());

      // Accept: {data:{contact_info_ids:[...]}} OR {contact_info_ids:[...]} OR [...]
      if (Array.isArray(j)) selectedTopCI = normalizeCIIds(j);
      else if (j?.data && (j.data.contact_info_ids || j.data.ids)) selectedTopCI = normalizeCIIds(j.data.contact_info_ids || j.data.ids);
      else if (j?.contact_info_ids || j?.ids) selectedTopCI = normalizeCIIds(j.contact_info_ids || j.ids);

      selectedTopCI = selectedTopCI.slice(0,2);
    }catch(e){
      console.warn('CI selection load failed:', e?.message || e);
      selectedTopCI = [];
    }
  }

  function selectedMainCIIds(){
    const ids = [];
    ciMain.list.querySelectorAll('input[type="checkbox"][data-ci-id]').forEach(cb => {
      if (cb.checked) ids.push(Number(cb.dataset.ciId));
    });
    return ids.filter(n=>Number.isFinite(n));
  }

  function renderMainPreview(ids){
    const arr = (ids||[]).slice(0,2);
    if (!arr.length){
      ciMain.preview.innerHTML = `<span class="text-muted small">—</span>`;
      return;
    }
    const chips = arr.map(id => {
      const ci = contactInfos.find(x => Number(x?.id) === Number(id));
      const label = ci ? ciLabel(ci) : (contactInfoMap.get(Number(id)) || ('#' + id));
      const meta = ci ? ciMeta(ci) : '';
      return `
        <span class="thm-ci-chip">
          <i class="fa-solid fa-circle-info"></i>
          <span class="fw-semibold">${esc(label)}</span>
          ${meta ? `<span class="text-muted">${esc(meta)}</span>` : ''}
        </span>
      `;
    }).join('');
    ciMain.preview.innerHTML = chips;
  }

  function enforceMainCILimit(){
    const ids = selectedMainCIIds();
    ciMain.count.textContent = String(ids.length);
    renderMainPreview(ids);

    const limitReached = ids.length >= 2;
    ciMain.list.querySelectorAll('input[type="checkbox"][data-ci-id]').forEach(cb => {
      if (!cb.checked) cb.disabled = limitReached;
      const row = cb.closest('.thm-contact-row');
      if (row) row.classList.toggle('disabled', cb.disabled && !cb.checked);
    });
  }

  function renderMainContactInfos(presetIds=[]){
    const preset = new Set((presetIds||[]).map(x=>Number(x)).filter(n=>Number.isFinite(n)));
    ciMain.list.innerHTML = '';
    setCiMainError('');

    if (!contactInfos.length){
      ciMain.empty.style.display = '';
      renderMainPreview([]);
      ciMain.count.textContent = '0';
      return;
    }
    ciMain.empty.style.display = 'none';

    const frag = document.createDocumentFragment();

    contactInfos.forEach(ci => {
      if (!ci || ci.id == null) return;

      const id = Number(ci.id);
      const row = document.createElement('div');
      row.className = 'thm-contact-row';

      row.innerHTML = `
        <div class="form-check" style="margin-top:2px;">
          <input class="form-check-input" type="checkbox" data-ci-id="${id}">
        </div>
        <div style="min-width:0;flex:1 1 auto;">
          <div class="fw-semibold">${esc(ciLabel(ci))}</div>
          <div class="thm-contact-meta">${esc(ciMeta(ci) || '')}</div>
        </div>
      `;

      const cb = row.querySelector('input[type="checkbox"]');
      cb.checked = preset.has(id);

      cb.addEventListener('change', () => {
        const ids = selectedMainCIIds();
        if (ids.length > 2){
          cb.checked = false;
          ok('Only 2 contact infos allowed.');
        }
        enforceMainCILimit();
      });

      frag.appendChild(row);
    });

    ciMain.list.appendChild(frag);

    // search filter
    ciMain.search.value = '';
    ciMain.search.oninput = function(){
      const q = (this.value || '').trim().toLowerCase();
      ciMain.list.querySelectorAll('.thm-contact-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
      });
    };

    enforceMainCILimit();
  }

  function setCiSaveLoading(on){
    const sp = ciMain.save.querySelector('.btn-spinner');
    const icon = ciMain.save.querySelector('.btn-icon');
    const txt = ciMain.save.querySelector('.btn-text');
    ciMain.save.classList.toggle('thm-btn-loading', !!on);
    ciMain.save.disabled = !!on;
    if (sp) sp.style.display = on ? '' : 'none';
    if (icon) icon.style.display = on ? 'none' : '';
    if (txt) txt.textContent = on ? 'Saving…' : 'Save';
    ciMain.reload.disabled = !!on;
  }

  async function saveTopContactInfos(){
    setCiMainError('');
    const ids = selectedMainCIIds();
    if (ids.length !== 2){
      setCiMainError('Please select exactly 2 contact infos.');
      return;
    }
    if (!API_CI_SELECT){
      setCiMainError('Contact selection API is not configured.');
      return;
    }

    // ✅ FIX: DO NOT FALLBACK TO POST (your route doesn't support POST)
    // Supported: GET/HEAD/PUT/DELETE.
    setCiSaveLoading(true);
    try{
      // send both keys to be compatible with different controller validations
      const payload = { contact_info_ids: ids, ids };

      const res = await fetchJSON(API_CI_SELECT, {
        method:'PUT',
        body: JSON.stringify(payload)
      });

      ok(res?.message || 'Contact infos saved');
      selectedTopCI = ids.slice(0,2);
      renderMainPreview(selectedTopCI);

    }catch(e){
      console.error(e);
      // show inline error too (so you see real PUT error, not a fake POST 405)
      setCiMainError(e.message || 'Failed to save contact infos');
      err(e.message || 'Failed to save contact infos');
    }finally{
      setCiSaveLoading(false);
    }
  }

  ciMain.reload.addEventListener('click', async () => {
    await loadContactInfos(true);
    await loadSelectedTopCI();
    renderMainContactInfos(selectedTopCI);
  });
  ciMain.save.addEventListener('click', saveTopContactInfos);

  async function initContactSection(){
    await loadContactInfos(false);
    await loadSelectedTopCI();
    renderMainContactInfos(selectedTopCI);
  }

  /* =========================
    Modal: create / edit (MENU ITEM ONLY)
  ========================= */
  const modalEl = document.getElementById(@json($thmUid . '_modal'));
  const MODAL = modalEl ? new bootstrap.Modal(modalEl) : null;

  const m = {
    title: modalEl.querySelector('.js-title'),
    slug: modalEl.querySelector('.js-slug'),
    slugAuto: modalEl.querySelector('.js-slug-auto'),
    slugRegen: modalEl.querySelector('.js-slug-regen'),
    url: modalEl.querySelector('.js-url'),
    active: modalEl.querySelector('.js-active'),

    editId: modalEl.querySelector('.js-edit-id'),
    saveBtn: modalEl.querySelector('.js-save'),
    modalTitle: modalEl.querySelector('.js-modal-title'),
    modalSub: modalEl.querySelector('.js-modal-sub'),
  };

  function clearModalErrors(){
    modalEl.querySelectorAll('.thm-err').forEach(e => { e.textContent=''; e.style.display='none'; });
  }
  function showModalError(field, msg){
    const el = modalEl.querySelector(`.thm-err[data-for="${field}"]`);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
  }

  function setSaveLoading(on){
    const sp = m.saveBtn.querySelector('.btn-spinner');
    const icon = m.saveBtn.querySelector('.btn-icon');
    const txt = m.saveBtn.querySelector('.btn-text');

    m.saveBtn.classList.toggle('thm-btn-loading', !!on);
    m.saveBtn.disabled = !!on;

    if (sp) sp.style.display = on ? '' : 'none';
    if (icon) icon.style.display = on ? 'none' : '';
    if (txt) txt.textContent = on ? 'Saving…' : 'Save';
  }

  function applySlugAutoState(){
    const on = !!m.slugAuto.checked;
    m.slug.disabled = on;
    m.slugRegen.disabled = on;
    if (on) m.slug.value = slugify(m.title.value);
  }

  m.title.addEventListener('input', () => {
    if (m.slugAuto.checked) m.slug.value = slugify(m.title.value);
  });
  m.slugAuto.addEventListener('change', applySlugAutoState);
  m.slugRegen.addEventListener('click', () => { m.slug.value = slugify(m.title.value); });

  async function openCreate(){
    clearModalErrors();
    m.editId.value = '';
    m.modalTitle.textContent = 'New Top Header Item';
    m.modalSub.textContent = 'Create a top header link item.';

    m.title.value = '';
    m.slugAuto.checked = true;
    m.slug.value = '';
    m.url.value = '';
    m.active.checked = true;

    applySlugAutoState();
    MODAL.show();
  }

  async function openEdit(id){
    clearModalErrors();
    m.editId.value = String(id);
    m.modalTitle.textContent = 'Edit Top Header Item';
    m.modalSub.textContent = 'Update title, slug, url, and status.';

    // In edit, don’t force overwrite slug
    m.slugAuto.checked = false;
    m.slug.disabled = false;
    m.slugRegen.disabled = false;

    try{
      const j = await fetchJSON(API_BASE + '/' + encodeURIComponent(id));
      const row = (j && typeof j === 'object' && 'data' in j) ? j.data : j;

      m.title.value = row?.title || '';
      m.slug.value  = row?.slug || '';
      m.url.value   = row?.url || row?.link || row?.page_url || '';
      m.active.checked = !!row?.active;

      MODAL.show();
    }catch(e){
      console.error(e);
      err(e.message || 'Failed to load item');
    }
  }

  async function saveModal(){
    clearModalErrors();

    const title = (m.title.value || '').trim();
    if (!title){
      showModalError('title','Title is required');
      m.title.focus();
      return;
    }

    if (m.slugAuto.checked){
      m.slug.value = slugify(title);
    }
    const slug = (m.slug.value || '').trim();
    if (!slug){
      showModalError('slug','Slug is required');
      return;
    }

    const payload = {
      title,
      slug,
      url: (m.url.value || '').trim() || null,
      active: !!m.active.checked,
    };

    const isEdit = (m.editId.value || '').trim() !== '';
    const url = isEdit ? (API_BASE + '/' + encodeURIComponent(m.editId.value)) : API_BASE;
    const method = isEdit ? 'PUT' : 'POST';

    setSaveLoading(true);

    try{
      const res = await fetchJSON(url, { method, body: JSON.stringify(payload) });
      ok(res?.message || (isEdit ? 'Updated' : 'Created'));

      markDirty(['active','archived','bin']);
      MODAL.hide();
      await refreshVisible();

    }catch(e){
      console.error(e);
      err(e.message || 'Save failed');
    }finally{
      setSaveLoading(false);
    }
  }

  // open modal buttons
  qs('.js-open-create').addEventListener('click', openCreate);
  m.saveBtn.addEventListener('click', saveModal);

  /* =========================
    Lists (Active/Archived/Bin)
  ========================= */
  const perPageSel   = qs('.js-per-page');
  const qInput       = qs('.js-q');
  const btnReset     = qs('.js-reset');

  const btnReorder   = qs('.js-reorder');
  const btnSaveOrd   = qs('.js-save-order');
  const btnCancelOrd = qs('.js-cancel-order');

  const rowsActive   = qs('.js-rows-active');
  const rowsArchived = qs('.js-rows-archived');
  const rowsBin      = qs('.js-rows-bin');

  const loaderActive   = qs('.js-loader-active');
  const loaderArchived = qs('.js-loader-archived');
  const loaderBin      = qs('.js-loader-bin');

  const emptyActive   = qs('.js-empty-active');
  const emptyArchived = qs('.js-empty-archived');
  const emptyBin      = qs('.js-empty-bin');

  const metaActive   = qs('.js-meta-active');
  const metaArchived = qs('.js-meta-archived');
  const metaBin      = qs('.js-meta-bin');

  const pagerActive   = qs('.js-pager-active');
  const pagerArchived = qs('.js-pager-archived');
  const pagerBin      = qs('.js-pager-bin');

  const state = {
    active:   { page: 1 },
    archived: { page: 1 },
    bin:      { page: 1 }
  };

  function clearRows(tbody, keepSelector){
    Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
      if (keepSelector && tr.matches(keepSelector)) return;
      tr.remove();
    });
  }

  function rowActions(r, mode){
    if (mode === 'bin'){
      return `
        <button class="btn btn-light btn-sm" data-act="restore" data-id="${r.id}" data-title="${esc(r.title||'')}">
          <i class="fa fa-rotate-left me-1"></i>Restore
        </button>
        <button class="btn btn-light btn-sm text-danger" data-act="force" data-id="${r.id}" data-title="${esc(r.title||'')}">
          <i class="fa fa-skull-crossbones me-1"></i>Delete
        </button>
      `;
    }

    const toggleIcon = (r.active ? 'fa-toggle-on text-success' : 'fa-toggle-off');
    return `
      <button class="btn btn-light btn-sm" data-act="edit" data-id="${r.id}">
        <i class="fa fa-pen"></i>
      </button>
      <button class="btn btn-light btn-sm" data-act="toggle" data-id="${r.id}" data-title="${esc(r.title||'')}">
        <i class="fa ${toggleIcon}"></i>
      </button>
      <button class="btn btn-light btn-sm text-danger" data-act="delete" data-id="${r.id}" data-title="${esc(r.title||'')}">
        <i class="fa fa-trash"></i>
      </button>
    `;
  }

  function buildActiveRow(r){
    const tr = document.createElement('tr');
    const slugLine = r.slug ? `<span class="d-block mt-1 small text-muted"><i class="fa fa-link me-1 opacity-75"></i>/${esc(r.slug)}</span>` : '';
    const url = r.url || r.link || r.page_url || '-';
    const status = r.active
      ? `<span class="badge badge-success">Active</span>`
      : `<span class="badge badge-secondary">Inactive</span>`;

    tr.dataset.id = String(r.id);
    tr.innerHTML = `
      <td>
        <span class="drag-handle" title="Drag to reorder" style="display:none;"><i class="fa fa-grip-vertical"></i></span>
      </td>
      <td>
        <div class="fw-semibold">${esc(r.title || '-')}</div>
        ${slugLine}
      </td>
      <td>${esc(url)}</td>
      <td>${status}</td>
      <td>${fmtDate(r.created_at)}</td>
      <td class="text-end">${rowActions(r, 'active')}</td>
    `;
    return tr;
  }

  function buildArchivedRow(r){
    const tr = document.createElement('tr');
    const slugLine = r.slug ? `<span class="d-block mt-1 small text-muted"><i class="fa fa-link me-1 opacity-75"></i>/${esc(r.slug)}</span>` : '';
    const url = r.url || r.link || r.page_url || '-';
    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.title || '-')}</div>
        ${slugLine}
      </td>
      <td>${esc(url)}</td>
      <td>${fmtDate(r.created_at)}</td>
      <td class="text-end">${rowActions(r, 'archived')}</td>
    `;
    return tr;
  }

  function buildBinRow(r){
    const tr = document.createElement('tr');
    const slugLine = r.slug ? `<span class="d-block mt-1 small text-muted"><i class="fa fa-link me-1 opacity-75"></i>/${esc(r.slug)}</span>` : '';
    const url = r.url || r.link || r.page_url || '-';
    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.title || '-')}</div>
        ${slugLine}
      </td>
      <td>${esc(url)}</td>
      <td>${fmtDate(r.deleted_at)}</td>
      <td class="text-end">${rowActions(r, 'bin')}</td>
    `;
    return tr;
  }

  function buildPager(pagerEl, cur, pages, onPage){
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

  function activeQueryParams(){
    const usp = new URLSearchParams();
    usp.set('per_page', String(Math.min(100, Math.max(5, Number(perPageSel.value || 30)))));
    usp.set('page', String(state.active.page));
    usp.set('active', '1');
    const q = (qInput.value || '').trim();
    if (q) usp.set('q', q);
    usp.set('sort', 'position');
    usp.set('direction', 'asc');
    return usp;
  }

  function archivedQueryParams(){
    const usp = new URLSearchParams();
    usp.set('per_page', '30');
    usp.set('page', String(state.archived.page));
    usp.set('active', '0');
    usp.set('sort', 'created_at');
    usp.set('direction', 'desc');
    return usp;
  }

  function binQueryParams(){
    const usp = new URLSearchParams();
    usp.set('per_page', '30');
    usp.set('page', String(state.bin.page));
    return usp;
  }

  let reorderMode = false;
  let sortable = null;

  function destroySortable(){
    if (sortable) { try{ sortable.destroy(); }catch{} }
    sortable = null;
  }

  function initSortable(){
    destroySortable();
    sortable = new Sortable(rowsActive, {
      animation: 150,
      handle: '.drag-handle',
      draggable: 'tr[data-id]',
      ghostClass: 'drag-ghost',
      chosenClass: 'drag-chosen',
      fallbackOnBody: true,
      swapThreshold: 0.65
    });
  }

  function setSaveBtnLoading(on){
    const sp = btnSaveOrd.querySelector('.btn-spinner');
    const icon = btnSaveOrd.querySelector('.btn-icon');
    const txt = btnSaveOrd.querySelector('.btn-text');

    btnSaveOrd.classList.toggle('thm-btn-loading', !!on);
    btnSaveOrd.disabled = !!on;

    if (sp) sp.style.display = on ? '' : 'none';
    if (icon) icon.style.display = on ? 'none' : '';
    if (txt) txt.textContent = on ? 'Saving…' : 'Save Order';

    btnCancelOrd.disabled = !!on;
    btnReorder.disabled = !!on;
  }

  function collectOrdersFromDOM(){
    const orders = [];
    Array.from(rowsActive.querySelectorAll('tr[data-id]')).forEach((tr, idx) => {
      const id = Number(tr.dataset.id);
      if (!Number.isFinite(id)) return;
      orders.push({ id, position: idx });
    });
    return orders;
  }

  async function loadActive(){
    loaderActive.style.display = '';
    emptyActive.style.display = 'none';
    metaActive.textContent = '—';
    pagerActive.innerHTML = '';
    clearRows(rowsActive, '.js-loader-active');

    try{
      const usp = activeQueryParams();
      const j = await fetchJSON(API_BASE + '?' + usp.toString());

      const items = Array.isArray(j.data) ? j.data : [];
      const pag = j.pagination || { page: state.active.page, per_page: Number(usp.get('per_page')), total: items.length };

      if (!items.length) emptyActive.style.display = '';

      const frag = document.createDocumentFragment();
      items.forEach(r => frag.appendChild(buildActiveRow(r)));
      rowsActive.appendChild(frag);

      ROOT.classList.toggle('thm-reorder-on', reorderMode);
      rowsActive.querySelectorAll('.drag-handle').forEach(h => h.style.display = reorderMode ? '' : 'none');
      btnSaveOrd.style.display = reorderMode ? '' : 'none';
      btnCancelOrd.style.display = reorderMode ? '' : 'none';

      if (reorderMode) initSortable();
      else destroySortable();

      const total = Number(pag.total || 0);
      const per = Number(pag.per_page || usp.get('per_page') || 30);
      const pages = Math.max(1, Math.ceil(total / per));
      metaActive.textContent = `Showing page ${pag.page} of ${pages} — ${total} item(s)`;

      buildPager(pagerActive, Number(pag.page||1), pages, (t)=>{
        state.active.page = Math.max(1,t);
        loadActive();
      });

      loaded.active = true;
      dirty.active = false;

    }catch(e){
      console.error(e);
      emptyActive.style.display = '';
      metaActive.textContent = 'Failed to load';
      err(e.message || 'Load error');
    }finally{
      loaderActive.style.display = 'none';
    }
  }

  async function loadArchived(){
    loaderArchived.style.display = '';
    emptyArchived.style.display = 'none';
    metaArchived.textContent = '—';
    pagerArchived.innerHTML = '';
    clearRows(rowsArchived, '.js-loader-archived');

    try{
      const usp = archivedQueryParams();
      const j = await fetchJSON(API_BASE + '?' + usp.toString());

      const items = Array.isArray(j.data) ? j.data : [];
      const pag = j.pagination || { page: state.archived.page, per_page: 30, total: items.length };

      if (!items.length) emptyArchived.style.display = '';

      const frag = document.createDocumentFragment();
      items.forEach(r => frag.appendChild(buildArchivedRow(r)));
      rowsArchived.appendChild(frag);

      const total = Number(pag.total || 0);
      const per = Number(pag.per_page || 30);
      const pages = Math.max(1, Math.ceil(total / per));
      metaArchived.textContent = `Showing page ${pag.page} of ${pages} — ${total} item(s)`;

      buildPager(pagerArchived, Number(pag.page||1), pages, (t)=>{
        state.archived.page = Math.max(1,t);
        loadArchived();
      });

      loaded.archived = true;
      dirty.archived = false;

    }catch(e){
      console.error(e);
      emptyArchived.style.display = '';
      metaArchived.textContent = 'Failed to load';
      err(e.message || 'Load error');
    }finally{
      loaderArchived.style.display = 'none';
    }
  }

  async function loadBin(){
    loaderBin.style.display = '';
    emptyBin.style.display = 'none';
    metaBin.textContent = '—';
    pagerBin.innerHTML = '';
    clearRows(rowsBin, '.js-loader-bin');

    try{
      const usp = binQueryParams();
      const j = await fetchJSON(API_TRASH + '?' + usp.toString());

      const items = Array.isArray(j.data) ? j.data : [];
      const pag = j.pagination || { page: state.bin.page, per_page: 30, total: items.length };

      if (!items.length) emptyBin.style.display = '';

      const frag = document.createDocumentFragment();
      items.forEach(r => frag.appendChild(buildBinRow(r)));
      rowsBin.appendChild(frag);

      const total = Number(pag.total || 0);
      const per = Number(pag.per_page || 30);
      const pages = Math.max(1, Math.ceil(total / per));
      metaBin.textContent = `Showing page ${pag.page} of ${pages} — ${total} item(s)`;

      buildPager(pagerBin, Number(pag.page||1), pages, (t)=>{
        state.bin.page = Math.max(1,t);
        loadBin();
      });

      loaded.bin = true;
      dirty.bin = false;

    }catch(e){
      console.error(e);
      emptyBin.style.display = '';
      metaBin.textContent = 'Failed to load';
      err(e.message || 'Load error');
    }finally{
      loaderBin.style.display = 'none';
    }
  }

  /* =========================
    Active toolbar events
  ========================= */
  let qTimer;
  qInput.addEventListener('input', () => {
    clearTimeout(qTimer);
    qTimer = setTimeout(() => {
      state.active.page = 1;
      loadActive();
    }, 250);
  });

  perPageSel.addEventListener('change', () => {
    state.active.page = 1;
    loadActive();
  });

  btnReset.addEventListener('click', () => {
    qInput.value = '';
    perPageSel.value = '30';
    state.active.page = 1;
    loadActive();
  });

  btnReorder.addEventListener('click', () => {
    reorderMode = !reorderMode;

    btnReorder.classList.toggle('btn-primary', reorderMode);
    btnReorder.classList.toggle('btn-light', !reorderMode);
    btnReorder.innerHTML = reorderMode
      ? '<i class="fa fa-check-double me-1"></i>Reorder On'
      : '<i class="fa fa-up-down-left-right me-1"></i>Reorder';

    loadActive();
  });

  btnCancelOrd.addEventListener('click', () => {
    reorderMode = false;
    btnReorder.classList.remove('btn-primary');
    btnReorder.classList.add('btn-light');
    btnReorder.innerHTML = '<i class="fa fa-up-down-left-right me-1"></i>Reorder';
    loadActive();
  });

  btnSaveOrd.addEventListener('click', async () => {
    const orders = collectOrdersFromDOM();
    if (!orders.length){
      Swal.fire('Nothing to save', 'No items found to reorder.', 'info');
      return;
    }

    setSaveBtnLoading(true);

    try{
      await fetchJSON(API_REORDER, {
        method: 'POST',
        body: JSON.stringify({ orders })
      });

      ok('Order updated');
      reorderMode = false;
      btnReorder.classList.remove('btn-primary');
      btnReorder.classList.add('btn-light');
      btnReorder.innerHTML = '<i class="fa fa-up-down-left-right me-1"></i>Reorder';

      markDirty(['active']);
      await refreshVisible();

    }catch(e){
      console.error(e);
      err(e.message || 'Reorder failed');
    }finally{
      setSaveBtnLoading(false);
    }
  });

  /* =========================
    Delegated actions
  ========================= */
  ROOT.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;

    const act = btn.dataset.act;
    const id = btn.dataset.id;
    const title = btn.dataset.title || 'this item';

    if (!id) return;

    if (act === 'edit'){
      return openEdit(id);
    }

    if (act === 'toggle'){
      try{
        await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/toggle-active', { method:'POST' });
        ok('Status updated');
        markDirty(['active','archived']);
        await refreshVisible();
      }catch(ex){
        err(ex.message || 'Toggle failed');
      }
      return;
    }

    if (act === 'delete'){
      const {isConfirmed} = await Swal.fire({
        icon:'warning',
        title:'Delete item?',
        html:`"${esc(title)}" will be moved to Bin.`,
        showCancelButton:true,
        confirmButtonText:'Delete',
        confirmButtonColor:'#ef4444'
      });
      if (!isConfirmed) return;

      try{
        await fetchJSON(API_BASE + '/' + encodeURIComponent(id), { method:'DELETE' });
        ok('Moved to Bin');
        markDirty(['active','archived','bin']);
        await refreshVisible();
      }catch(ex){
        err(ex.message || 'Delete failed');
      }
      return;
    }

    if (act === 'restore'){
      try{
        await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/restore', { method:'POST' });
        ok('Restored');
        markDirty(['bin','active','archived']);
        await refreshVisible();
      }catch(ex){
        err(ex.message || 'Restore failed');
      }
      return;
    }

    if (act === 'force'){
      const {isConfirmed} = await Swal.fire({
        icon:'warning',
        title:'Delete permanently?',
        html:`This cannot be undone.<br>"${esc(title)}"`,
        showCancelButton:true,
        confirmButtonText:'Delete permanently',
        confirmButtonColor:'#dc2626'
      });
      if (!isConfirmed) return;

      try{
        await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/force', { method:'DELETE' });
        ok('Permanently deleted');
        markDirty(['bin']);
        await refreshVisible();
      }catch(ex){
        err(ex.message || 'Force delete failed');
      }
      return;
    }
  });

  /* =========================
    Tab events
  ========================= */
  const tabA = ROOT.querySelector('a[href="#{{ $tabActive }}"]');
  const tabR = ROOT.querySelector('a[href="#{{ $tabArchived }}"]');
  const tabB = ROOT.querySelector('a[href="#{{ $tabBin }}"]');

  tabA?.addEventListener('shown.bs.tab', () => {
    if (!loaded.active || dirty.active) loadActive();
  });
  tabR?.addEventListener('shown.bs.tab', () => {
    if (!loaded.archived || dirty.archived) loadArchived();
  });
  tabB?.addEventListener('shown.bs.tab', () => {
    if (!loaded.bin || dirty.bin) loadBin();
  });

  // Initial load
  initContactSection();
  loadActive();

})();
</script>
@endpush
