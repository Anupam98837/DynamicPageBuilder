{{-- resources/views/modules/home/manageHeroCarousel.blade.php --}}
@section('title','Hero Carousel')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* ===== Page shell ===== */
.hc-wrap{padding:14px 4px}
.hc-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2)}
.hc-toolbar{padding:12px 12px}

/* ===== Tabs ===== */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}

/* ===== Table card ===== */
.table-card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.table-card .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{
  font-weight:600;
  color:var(--muted-color);
  font-size:13px;
  border-bottom:1px solid var(--line-strong);
  background:var(--surface);
}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}

/* ===== Responsive horizontal scroll ===== */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{
  width:max-content;
  min-width:1240px;
}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* ===== Slug column ===== */
th.col-slug, td.col-slug{width:200px;max-width:200px}
td.col-slug{overflow:hidden}
td.col-slug code{
  display:inline-block;
  max-width:190px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* ===== Image thumbs ===== */
.thumb{
  width:90px;height:48px;object-fit:cover;border-radius:12px;
  border:1px solid var(--line-soft);background:#fff;
}
.thumb-wrap{display:flex;align-items:center;gap:10px}
.thumb-meta{display:flex;flex-direction:column;gap:2px}
.thumb-meta .muted{color:var(--muted-color);font-size:12px}

/* ===== Badges ===== */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}

/* ===== Dropdown fix (actions) ===== */
.table-card .dropdown{position:relative}
.dropdown .hc-dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ match contact page behavior */
}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* ===== Loading overlay ===== */
.loading-overlay{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;backdrop-filter:blur(2px)
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* ===== Button loading state ===== */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;width:16px;height:16px;
  top:50%;left:50%;margin:-8px 0 0 -8px;
  border:2px solid transparent;border-top:2px solid currentColor;border-radius:50%;
  animation:spin 1s linear infinite
}

/* ===== Modal editor ===== */
.mini-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.code-switch{display:flex;border:1px solid var(--line-soft);border-radius:12px;overflow:hidden}
.code-switch button{
  border:0;background:transparent;color:var(--ink);
  padding:8px 12px;font-size:12px;cursor:pointer
}
.code-switch button+button{border-left:1px solid var(--line-soft)}
.code-switch button.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700
}
.editor-box{
  border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;background:var(--surface)
}
.editor-area{min-height:180px;padding:12px;outline:none}
.editor-area:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
textarea.editor-code{
  width:100%;min-height:180px;border:0;outline:0;resize:vertical;
  padding:12px;background:transparent;color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;line-height:1.45;
}
.editor-tools{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px 10px;border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.et-btn{
  border:1px solid var(--line-soft);background:transparent;color:var(--ink);
  padding:7px 9px;border-radius:10px;line-height:1;cursor:pointer;
}
.et-btn:hover{background:var(--page-hover)}
.et-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}
</style>
@endpush

@section('content')
<div class="hc-wrap">

  {{-- Loading Overlay --}}
  <div id="hcLoading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#hc-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-images me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#hc-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#hc-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  {{-- Toolbar (common) --}}
  <div class="hc-panel hc-toolbar mb-3">
    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Per Page</label>
          <select id="hcPerPage" class="form-select" style="width:96px;">
            <option>10</option>
            <option selected>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        <div class="position-relative" style="min-width:280px;">
          <input id="hcSearch" type="search" class="form-control ps-5" placeholder="Search title / slug / overlay…">
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>

        {{-- Inactive status selector --}}
        <div id="inactiveStatusWrap" class="d-flex align-items-center gap-2" style="display:none;">
          <label class="text-muted small mb-0">Show</label>
          <select id="hcInactiveStatus" class="form-select" style="width:160px;">
            <option value="draft" selected>Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#hcFilterModal" id="hcBtnFilter">
          <i class="fa fa-sliders me-1"></i>Filter
        </button>

        <button class="btn btn-light" id="hcBtnReset">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
      </div>

      <div class="d-flex align-items-center gap-2" id="hcWriteControls" style="display:none;">
        <button type="button" class="btn btn-outline-primary" id="hcBtnSaveSort">
          <i class="fa fa-arrow-up-wide-short me-1"></i>Save Sort (Visible)
        </button>
        <button type="button" class="btn btn-primary" id="hcBtnAdd">
          <i class="fa fa-plus me-1"></i>Add Slide
        </button>
      </div>
    </div>
  </div>

  <div class="tab-content">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="hc-tab-active" role="tabpanel">
      <div class="card table-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Slide</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:130px;">Status</th>
                  <th style="width:160px;">Publish</th>
                  <th style="width:160px;">Expire</th>
                  <th style="width:120px;">Sort</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="hcTbodyActive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="hcEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-images mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active slides found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="hcInfoActive">—</div>
            <nav><ul id="hcPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="hc-tab-inactive" role="tabpanel">
      <div class="card table-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Slide</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:130px;">Status</th>
                  <th style="width:160px;">Publish</th>
                  <th style="width:160px;">Expire</th>
                  <th style="width:120px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="hcTbodyInactive">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="hcEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive slides found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="hcInfoInactive">—</div>
            <nav><ul id="hcPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="hc-tab-trash" role="tabpanel">
      <div class="card table-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:170px;">Deleted</th>
                  <th style="width:120px;">Sort</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="hcTbodyTrash">
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="hcEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="hcInfoTrash">—</div>
            <nav><ul id="hcPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="hcFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Sort</label>
            <select id="hcFilterSort" class="form-select">
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-created_at">Newest</option>
              <option value="created_at">Oldest</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
              <option value="-views_count">Views (Desc)</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Visible Now</label>
            <select id="hcFilterVisibleNow" class="form-select">
              <option value="">Any</option>
              <option value="1">Only currently visible</option>
              <option value="0">Ignore window</option>
            </select>
            <div class="form-text">Applies mainly to Published items.</div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        {{-- ✅ fix: ensure this is not treated as submit anywhere --}}
        <button type="button" class="btn btn-primary" id="hcApplyFilters">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="hcItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="hcItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="hcItemTitle">Add Slide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="hcUuid">
        <input type="hidden" id="hcId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">

              <div class="col-12">
                <label class="form-label">Title</label>
                <input class="form-control" id="hcTitleInput" maxlength="255" placeholder="e.g., Orientation 2025">
                <div class="form-text">Title is optional (slug can still auto-generate).</div>
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="hcSlugInput" maxlength="160" placeholder="orientation-2025">
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="hcSortOrder" min="0" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="hcStatus">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Alt Text</label>
                <input class="form-control" id="hcAltText" maxlength="255" placeholder="Accessibility alt text">
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="hcPublishAt">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="hcExpireAt">
              </div>

              <div class="col-12">
                <label class="form-label">Desktop Image <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="hcDesktopFile" accept="image/*">
                <div class="form-text">Upload OR provide path/url below.</div>
                <input class="form-control mt-2" id="hcDesktopPath" maxlength="255" placeholder="depy_uploads/hero_carousel/xxx.jpg or https://...">
                <div class="d-flex align-items-center gap-2 mt-2" id="hcDesktopRemoveWrap" style="display:none;">
                  <input class="form-check-input" type="checkbox" id="hcDesktopRemove">
                  <label class="form-check-label small" for="hcDesktopRemove">Remove desktop image</label>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Mobile Image</label>
                <input type="file" class="form-control" id="hcMobileFile" accept="image/*">
                <div class="form-text">Optional (upload OR path/url).</div>
                <input class="form-control mt-2" id="hcMobilePath" maxlength="255" placeholder="depy_uploads/hero_carousel/xxx-mobile.jpg or https://...">
                <div class="d-flex align-items-center gap-2 mt-2" id="hcMobileRemoveWrap" style="display:none;">
                  <input class="form-check-input" type="checkbox" id="hcMobileRemove">
                  <label class="form-check-label small" for="hcMobileRemove">Remove mobile image</label>
                </div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Overlay Text (HTML allowed)</label>

            <div class="editor-box" id="hcOverlayBox">
              <div class="editor-tools">
                <button type="button" class="et-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="et-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="et-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
                <span class="et-sep"></span>
                <button type="button" class="et-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="et-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                <div class="ms-auto code-switch" id="hcOverlayMode">
                  <button type="button" class="active" data-mode="text">Text</button>
                  <button type="button" data-mode="code">Code</button>
                </div>
              </div>

              <div id="hcOverlayEditor" class="editor-area" contenteditable="true"
                data-placeholder="Write overlay text (HTML allowed)…"></div>

              <textarea id="hcOverlayCode" class="editor-code" style="display:none;"
                placeholder="Paste HTML here…"></textarea>
            </div>

            <div class="mini-help">
              Tip: Use <b>Code</b> to paste long HTML exactly.
            </div>

            <input type="hidden" id="hcOverlayHidden" name="overlay_text">

            <hr class="my-3">

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Desktop Preview</label>
                <div class="d-flex align-items-center gap-2">
                  <img id="hcDesktopPreview" class="thumb" style="display:none;" alt="Desktop preview">
                  <div class="small text-muted" id="hcDesktopPreviewText">No image</div>
                  <button type="button" class="btn btn-light btn-sm ms-auto" id="hcOpenDesktop" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Mobile Preview</label>
                <div class="d-flex align-items-center gap-2">
                  <img id="hcMobilePreview" class="thumb" style="display:none;" alt="Mobile preview">
                  <div class="small text-muted" id="hcMobilePreviewText">No image</div>
                  <button type="button" class="btn btn-light btn-sm ms-auto" id="hcOpenMobile" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="hcSaveBtn" type="submit">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="hcToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="hcToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="hcToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="hcToastErrText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__HERO_CAROUSEL_PAGE_INIT__) return;
  window.__HERO_CAROUSEL_PAGE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  const API = '/api/hero-carousel';

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function slugify(s){
    return (s || '')
      .toString()
      .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
      .trim().toLowerCase()
      .replace(/['"`]/g,'')
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
  }

  function toLocal(dt){
    if (!dt) return '';
    const t = String(dt).replace(' ', 'T');
    return t.length >= 16 ? t.slice(0,16) : t;
  }

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const loading = $('hcLoading');
    const showLoading = (v) => { if (loading) loading.style.display = v ? 'flex' : 'none'; };

    const toastOk = $('hcToastOk') ? new bootstrap.Toast($('hcToastOk')) : null;
    const toastErr = $('hcToastErr') ? new bootstrap.Toast($('hcToastErr')) : null;
    const ok = (m) => { $('hcToastOkText').textContent = m || 'Done'; toastOk && toastOk.show(); };
    const err = (m) => { $('hcToastErrText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

    /* ✅ FIX: Cleanup stuck modal backdrops/body lock (safe no-op if none stuck) */
    function cleanupModalArtifacts(){
      try{
        // remove all backdrops if no modal is actually visible
        const anyShown = !!document.querySelector('.modal.show');
        if (!anyShown){
          document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
          document.body.classList.remove('modal-open');
          document.body.style.removeProperty('overflow');
          document.body.style.removeProperty('padding-right');
        }
      }catch(_){}
    }

    // safety net: whenever any of our modals fully close, ensure no leftover backdrop remains
    ['hcFilterModal','hcItemModal'].forEach((mid) => {
      const el = $(mid);
      if (!el) return;
      el.addEventListener('hidden.bs.modal', cleanupModalArtifacts);
    });

    /* ========= Permissions ========= */
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const full = ['admin','super_admin','director','principal'];
      const writers = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = full.includes(r);
      canDelete = full.includes(r);
      canEdit   = writers.includes(r);

      $('hcWriteControls').style.display = (canCreate || canEdit) ? 'flex' : 'none';
      $('hcBtnAdd').style.display = canCreate ? '' : 'none';
      $('hcBtnSaveSort').style.display = (canCreate || canEdit) ? '' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders() }, 8000);
        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role) ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      computePermissions();
    }

    /* ========= State ========= */
    const state = {
      perPage: parseInt($('hcPerPage').value || '20', 10) || 20,
      q: '',
      sort: 'sort_order',
      direction: 'asc',
      visible_now: '',
      inactiveStatus: 'draft',
      tabs: {
        active: { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash: { page:1, lastPage:1, items:[] }
      }
    };

    function currentTab(){
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#hc-tab-active';
      if (href === '#hc-tab-inactive') return 'inactive';
      if (href === '#hc-tab-trash') return 'trash';
      return 'active';
    }

    function statusBadge(s){
      s = (s || '').toLowerCase();
      if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
      if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
      return `<span class="badge badge-soft-muted">${esc(s||'—')}</span>`;
    }

    function buildListUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      if (state.q) params.set('q', state.q);

      params.set('sort', state.sort);
      params.set('direction', state.direction);

      if (state.visible_now !== '') params.set('visible_now', state.visible_now);

      if (tabKey === 'active'){
        params.set('status', 'published');
        return `${API}?${params.toString()}`;
      }

      if (tabKey === 'inactive'){
        params.set('status', state.inactiveStatus);
        return `${API}?${params.toString()}`;
      }

      // trash tab
      return `${API}/trash?${params.toString()}`;
    }

    function setInactiveStatusUI(){
      const tab = currentTab();
      $('inactiveStatusWrap').style.display = (tab === 'inactive') ? 'flex' : 'none';
    }

    /* ========= Render ========= */
    function renderPager(tabKey){
      const pagerEl = tabKey === 'active' ? $('hcPagerActive') : (tabKey === 'inactive' ? $('hcPagerInactive') : $('hcPagerTrash'));
      if (!pagerEl) return;

      const st = state.tabs[tabKey];
      const page = st.page;
      const totalPages = st.lastPage || 1;

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Prev', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerEl.innerHTML = html;
    }

    // ✅ match ContactInfo approach: DO NOT use data-bs-toggle, we will toggle manually with Popper "fixed"
    function rowActions(tabKey){
      let html = `
        <div class="dropdown text-end">
          <button type="button" class="btn btn-light btn-sm hc-dd-toggle"
            aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
      `;

      if (tabKey !== 'trash' && canEdit){
        html += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
      }

      if (tabKey !== 'trash'){
        if (canDelete){
          html += `<li><hr class="dropdown-divider"></li>
                   <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
        }
      } else {
        html += `<li><hr class="dropdown-divider"></li>
                 <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
        if (canDelete){
          html += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
        }
      }

      html += `</ul></div>`;
      return html;
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? $('hcTbodyActive') : (tabKey==='inactive' ? $('hcTbodyInactive') : $('hcTbodyTrash'));
      const empty = tabKey==='active' ? $('hcEmptyActive') : (tabKey==='inactive' ? $('hcEmptyInactive') : $('hcEmptyTrash'));
      const info  = tabKey==='active' ? $('hcInfoActive') : (tabKey==='inactive' ? $('hcInfoInactive') : $('hcInfoTrash'));

      const rows = state.tabs[tabKey].items || [];
      if (!rows.length){
        tbody.innerHTML = '';
        empty.style.display = '';
        renderPager(tabKey);
        info.textContent = '0 result(s)';
        return;
      }
      empty.style.display = 'none';

      if (tabKey === 'trash'){
        tbody.innerHTML = rows.map(r => `
          <tr data-uuid="${esc(r.uuid || '')}">
            <td class="fw-semibold">${esc(r.title || '—')}</td>
            <td class="col-slug"><code>${esc(r.slug || '—')}</code></td>
            <td>${esc(r.deleted_at || '—')}</td>
            <td>
              <input class="form-control form-control-sm hc-sort-input" type="number" min="0"
                value="${esc(String(r.sort_order ?? 0))}" data-id="${esc(String(r.id ?? ''))}">
            </td>
            <td class="text-end">${rowActions(tabKey)}</td>
          </tr>
        `).join('');
        renderPager(tabKey);
        return;
      }

      // active / inactive
      tbody.innerHTML = rows.map(r => {
        const d = r.image_url_full || r.image_url || '';
        const m = r.mobile_image_url_full || r.mobile_image_url || '';
        return `
          <tr data-uuid="${esc(r.uuid || '')}">
            <td>
              <div class="thumb-wrap">
                ${d ? `<img class="thumb" src="${esc(normalizeUrl(d))}" alt="${esc(r.alt_text || r.title || 'image')}">` : `<div class="thumb" style="display:flex;align-items:center;justify-content:center;color:#9aa4b2;">—</div>`}
                <div class="thumb-meta">
                  <div class="fw-semibold">${esc(r.title || 'Untitled')}</div>
                  <div class="muted">
                    ${m ? `<i class="fa fa-mobile-screen-button me-1"></i>mobile set` : `<i class="fa fa-mobile-screen-button me-1"></i>no mobile`}
                  </div>
                </div>
              </div>
            </td>
            <td class="col-slug"><code title="${esc(r.slug || '')}">${esc(r.slug || '—')}</code></td>
            <td>${statusBadge(r.status)}</td>
            <td>${esc(r.publish_at || '—')}</td>
            <td>${esc(r.expire_at || '—')}</td>
            <td>
              <input class="form-control form-control-sm hc-sort-input" type="number" min="0"
                value="${esc(String(r.sort_order ?? 0))}" data-id="${esc(String(r.id ?? ''))}">
            </td>
            <td>${esc(String(r.views_count ?? 0))}</td>
            <td>${esc(r.updated_at || '—')}</td>
            <td class="text-end">${rowActions(tabKey)}</td>
          </tr>
        `;
      }).join('');

      renderPager(tabKey);
    }

    /* ========= Load ========= */
    async function loadTab(tabKey){
      const tbody = (tabKey === 'active')
        ? $('hcTbodyActive')
        : (tabKey === 'inactive' ? $('hcTbodyInactive') : $('hcTbodyTrash'));
      const cols = tabKey==='trash' ? 5 : (tabKey==='inactive' ? 8 : 9);
      tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;

      try{
        const res = await fetchWithTimeout(buildListUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const info = tabKey==='active' ? $('hcInfoActive') : (tabKey==='inactive' ? $('hcInfoInactive') : $('hcInfoTrash'));
        info.textContent = (p.total != null) ? `${p.total} result(s)` : `${items.length} result(s)`;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function reloadCurrent(){ await loadTab(currentTab()); }

    /* ========= Pager click ========= */
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();
      const tab = a.dataset.tab;
      const p = parseInt(a.dataset.page, 10);
      if (!tab || Number.isNaN(p)) return;
      if (p === state.tabs[tab].page) return;
      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ========= Toolbar ========= */
    $('hcPerPage').addEventListener('change', () => {
      state.perPage = parseInt($('hcPerPage').value || '20', 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    $('hcSearch').addEventListener('input', debounce(() => {
      state.q = ($('hcSearch').value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    $('hcBtnReset').addEventListener('click', () => {
      state.perPage = 20;
      $('hcPerPage').value = '20';
      state.q = '';
      $('hcSearch').value = '';
      state.sort = 'sort_order';
      state.direction = 'asc';
      state.visible_now = '';
      state.inactiveStatus = 'draft';
      $('hcInactiveStatus').value = 'draft';

      $('hcFilterSort').value = 'sort_order';
      $('hcFilterVisibleNow').value = '';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    /* ✅ FIXED APPLY: close modal completely, remove stuck backdrop, then reload */
    $('hcApplyFilters').addEventListener('click', () => {
      const s = $('hcFilterSort').value || 'sort_order';
      state.sort = s.startsWith('-') ? s.slice(1) : s;
      state.direction = s.startsWith('-') ? 'desc' : 'asc';
      state.visible_now = ($('hcFilterVisibleNow').value ?? '');

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;

      const modalEl = $('hcFilterModal');
      let done = false;
      const afterClose = async () => {
        if (done) return;
        done = true;
        cleanupModalArtifacts();
        await reloadCurrent();
      };

      // run after bootstrap finishes transition
      modalEl?.addEventListener('hidden.bs.modal', afterClose, { once:true });

      try{
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      }catch(_){
        // if something odd happens, still attempt cleanup + reload
      }

      // fallback in case hidden event doesn't fire (rare, but fixes "stuck backdrop" setups)
      setTimeout(afterClose, 450);
    });

    $('hcInactiveStatus').addEventListener('change', () => {
      state.inactiveStatus = $('hcInactiveStatus').value || 'draft';
      state.tabs.inactive.page = 1;
      loadTab('inactive');
    });

    document.querySelector('a[href="#hc-tab-active"]')?.addEventListener('shown.bs.tab', () => { setInactiveStatusUI(); loadTab('active'); });
    document.querySelector('a[href="#hc-tab-inactive"]')?.addEventListener('shown.bs.tab', () => { setInactiveStatusUI(); loadTab('inactive'); });
    document.querySelector('a[href="#hc-tab-trash"]')?.addEventListener('shown.bs.tab', () => { setInactiveStatusUI(); loadTab('trash'); });

    /* ========= Overlay mini editor ========= */
    const overlay = {
      mode: 'text',
      tools: $('hcOverlayBox'),
      editor: $('hcOverlayEditor'),
      code: $('hcOverlayCode'),
      hidden: $('hcOverlayHidden'),
      modeWrap: $('hcOverlayMode')
    };

    function overlaySyncToHidden(){
      const html = overlay.mode === 'code' ? (overlay.code.value || '') : (overlay.editor.innerHTML || '');
      overlay.hidden.value = html.trim();
    }

    function overlaySetMode(m){
      overlay.mode = (m === 'code') ? 'code' : 'text';
      overlay.modeWrap.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.mode === overlay.mode));
      overlay.editor.style.display = (overlay.mode === 'text') ? '' : 'none';
      overlay.code.style.display = (overlay.mode === 'code') ? '' : 'none';
      if (overlay.mode === 'code'){
        overlay.code.value = overlay.editor.innerHTML || '';
        setTimeout(()=>overlay.code.focus(), 0);
      } else {
        overlay.editor.innerHTML = overlay.code.value || '';
        setTimeout(()=>overlay.editor.focus(), 0);
      }
      overlaySyncToHidden();
    }

    // prevent toolbar click from blurring editor
    overlay.tools.querySelector('.editor-tools')?.addEventListener('pointerdown', (e) => e.preventDefault());

    overlay.modeWrap.addEventListener('click', (e) => {
      const b = e.target.closest('button[data-mode]');
      if (!b) return;
      overlaySetMode(b.dataset.mode);
    });

    overlay.tools.addEventListener('click', (e) => {
      const btn = e.target.closest('.et-btn[data-cmd]');
      if (!btn) return;
      if (overlay.mode !== 'text') return;
      overlay.editor.focus({preventScroll:true});
      try{ document.execCommand(btn.dataset.cmd, false, null); }catch(_){}
      overlaySyncToHidden();
    });

    overlay.editor.addEventListener('input', overlaySyncToHidden);
    overlay.code.addEventListener('input', overlaySyncToHidden);

    /* ========= Modal + previews ========= */
    const itemModal = new bootstrap.Modal($('hcItemModal'));
    const saveBtn = $('hcSaveBtn');

    let saving = false;
    let slugDirty = false;
    let settingSlug = false;
    let deskObjUrl = null;
    let mobObjUrl = null;

    function setBtnLoading(btn, on){
      btn.disabled = !!on;
      btn.classList.toggle('btn-loading', !!on);
    }

    function clearPreview(which){
      if (which === 'desktop'){
        if (deskObjUrl){ try{ URL.revokeObjectURL(deskObjUrl); }catch(_){ } deskObjUrl=null; }
        $('hcDesktopPreview').style.display = 'none';
        $('hcDesktopPreview').removeAttribute('src');
        $('hcDesktopPreviewText').textContent = 'No image';
        $('hcOpenDesktop').style.display = 'none';
        $('hcOpenDesktop').onclick = null;
      } else {
        if (mobObjUrl){ try{ URL.revokeObjectURL(mobObjUrl); }catch(_){ } mobObjUrl=null; }
        $('hcMobilePreview').style.display = 'none';
        $('hcMobilePreview').removeAttribute('src');
        $('hcMobilePreviewText').textContent = 'No image';
        $('hcOpenMobile').style.display = 'none';
        $('hcOpenMobile').onclick = null;
      }
    }

    function setPreview(which, url){
      const u = normalizeUrl(url);
      if (!u){ clearPreview(which); return; }

      if (which === 'desktop'){
        $('hcDesktopPreview').style.display = '';
        $('hcDesktopPreview').src = u;
        $('hcDesktopPreviewText').textContent = u;
        $('hcOpenDesktop').style.display = '';
        $('hcOpenDesktop').onclick = () => window.open(u, '_blank', 'noopener');
      } else {
        $('hcMobilePreview').style.display = '';
        $('hcMobilePreview').src = u;
        $('hcMobilePreviewText').textContent = u;
        $('hcOpenMobile').style.display = '';
        $('hcOpenMobile').onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    function resetForm(){
      $('hcItemForm').reset();
      $('hcUuid').value = '';
      $('hcId').value = '';
      $('hcDesktopPath').value = '';
      $('hcMobilePath').value = '';
      $('hcDesktopRemove').checked = false;
      $('hcMobileRemove').checked = false;
      $('hcDesktopRemoveWrap').style.display = 'none';
      $('hcMobileRemoveWrap').style.display = 'none';

      slugDirty = false; settingSlug = false;

      overlay.editor.innerHTML = '';
      overlay.code.value = '';
      overlay.hidden.value = '';
      overlaySetMode('text');

      clearPreview('desktop');
      clearPreview('mobile');

      // unlock fields
      $('hcItemForm').querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'hcUuid' || el.id === 'hcId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });
      $('hcOverlayEditor').setAttribute('contenteditable','true');
      $('hcSaveBtn').style.display = '';
      $('hcItemForm').dataset.mode = 'edit';
      $('hcItemForm').dataset.intent = 'create';
    }

    function fillForm(r, viewOnly=false){
      $('hcUuid').value = r.uuid || '';
      $('hcId').value = r.id || '';

      $('hcTitleInput').value = r.title || '';
      $('hcSlugInput').value = r.slug || '';
      $('hcSortOrder').value = String(r.sort_order ?? 0);
      $('hcStatus').value = (r.status || 'draft');
      $('hcAltText').value = r.alt_text || '';

      $('hcPublishAt').value = toLocal(r.publish_at);
      $('hcExpireAt').value = toLocal(r.expire_at);

      // paths
      $('hcDesktopPath').value = r.image_url || '';
      $('hcMobilePath').value = r.mobile_image_url || '';

      // previews
      setPreview('desktop', r.image_url_full || r.image_url || '');
      setPreview('mobile', r.mobile_image_url_full || r.mobile_image_url || '');

      // overlay
      overlay.editor.innerHTML = (r.overlay_text || '');
      overlaySyncToHidden();

      // remove toggles show only when editing existing
      $('hcDesktopRemoveWrap').style.display = r.uuid ? '' : 'none';
      $('hcMobileRemoveWrap').style.display = r.uuid ? '' : 'none';

      slugDirty = true;

      if (viewOnly){
        $('hcItemForm').querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'hcUuid' || el.id === 'hcId') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        $('hcOverlayEditor').setAttribute('contenteditable','false');
        $('hcSaveBtn').style.display = 'none';
        $('hcItemForm').dataset.mode = 'view';
        $('hcItemForm').dataset.intent = 'view';
      } else {
        $('hcOverlayEditor').setAttribute('contenteditable','true');
        $('hcSaveBtn').style.display = '';
        $('hcItemForm').dataset.mode = 'edit';
        $('hcItemForm').dataset.intent = 'edit';
      }
    }

    $('hcTitleInput').addEventListener('input', debounce(() => {
      if ($('hcUuid').value) return;
      if (slugDirty) return;
      const t = $('hcTitleInput').value || '';
      const next = t.trim() ? slugify(t) : '';
      settingSlug = true;
      $('hcSlugInput').value = next;
      settingSlug = false;
    }, 140));

    $('hcSlugInput').addEventListener('input', () => {
      if ($('hcUuid').value) return;
      if (settingSlug) return;
      slugDirty = !!($('hcSlugInput').value || '').trim();
    });

    $('hcDesktopFile').addEventListener('change', () => {
      const f = $('hcDesktopFile').files?.[0];
      if (!f){ clearPreview('desktop'); return; }
      if (deskObjUrl){ try{ URL.revokeObjectURL(deskObjUrl); }catch(_){ } }
      deskObjUrl = URL.createObjectURL(f);
      setPreview('desktop', deskObjUrl);
    });

    $('hcMobileFile').addEventListener('change', () => {
      const f = $('hcMobileFile').files?.[0];
      if (!f){ clearPreview('mobile'); return; }
      if (mobObjUrl){ try{ URL.revokeObjectURL(mobObjUrl); }catch(_){ } }
      mobObjUrl = URL.createObjectURL(f);
      setPreview('mobile', mobObjUrl);
    });

    $('hcDesktopPath').addEventListener('input', debounce(() => {
      const v = $('hcDesktopPath').value || '';
      if (!v.trim()) return;
      setPreview('desktop', v);
    }, 200));

    $('hcMobilePath').addEventListener('input', debounce(() => {
      const v = $('hcMobilePath').value || '';
      if (!v.trim()) { clearPreview('mobile'); return; }
      setPreview('mobile', v);
    }, 200));

    $('hcBtnAdd').addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      $('hcItemTitle').textContent = 'Add Slide';
      $('hcItemForm').dataset.intent = 'create';
      itemModal.show();
    });

    $('hcItemModal').addEventListener('hidden.bs.modal', () => {
      if (deskObjUrl){ try{ URL.revokeObjectURL(deskObjUrl); }catch(_){ } deskObjUrl=null; }
      if (mobObjUrl){ try{ URL.revokeObjectURL(mobObjUrl); }catch(_){ } mobObjUrl=null; }
    });

    /* ========= Find row by uuid ========= */
    function findRow(uuid){
      const all = [...state.tabs.active.items, ...state.tabs.inactive.items, ...state.tabs.trash.items];
      return all.find(x => x?.uuid === uuid) || null;
    }

    // ---------- ✅ ACTION DROPDOWN FIX (copied behavior from ContactInfo) ----------
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.hc-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    // click outside -> close
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });

    // toggle click -> manual bootstrap dropdown with Popper "fixed" (escapes overflow clipping)
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.hc-dd-toggle');
      if (!toggle) return;

      e.preventDefault();
      e.stopPropagation();

      closeAllDropdownsExcept(toggle);

      try{
        const inst = bootstrap.Dropdown.getOrCreateInstance(toggle, {
          autoClose: true,
          popperConfig: (def) => {
            const base = def || {};
            const mods = Array.isArray(base.modifiers) ? base.modifiers.slice() : [];
            mods.push({ name:'preventOverflow', options:{ boundary:'viewport', padding:8 } });
            mods.push({ name:'flip', options:{ boundary:'viewport', padding:8 } });
            return { ...base, strategy:'fixed', modifiers: mods };
          }
        });
        inst.toggle();
      }catch(_){}
    });
    // ---------- end dropdown fix ----------

    /* ========= Actions ========= */
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const action = btn.dataset.action;
      if (!uuid) return;

      // close dropdown safely
      const toggle = btn.closest('.dropdown')?.querySelector('.hc-dd-toggle');
      if (toggle){
        try{ bootstrap.Dropdown.getInstance(toggle)?.hide(); }catch(_){}
      }

      const tab = currentTab();
      const row = findRow(uuid) || {};

      if (action === 'view' || action === 'edit'){
        if (action === 'edit' && !canEdit) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API}/${encodeURIComponent(uuid)}`, { headers: authHeaders() }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(js?.message || 'Failed to load item');

          const item = js?.item || js?.data || row;

          resetForm();
          $('hcItemTitle').textContent = action === 'view' ? 'View Slide' : 'Edit Slide';
          fillForm(item, action === 'view');
          itemModal.show();
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this slide?',
          text: 'This will move it to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API}/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');
          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{ showLoading(false); }
        return;
      }

      if (action === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this slide?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API}/${encodeURIComponent(uuid)}/restore`, {
            method: 'PUT',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');
          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{ showLoading(false); }
        return;
      }

      if (action === 'force'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete permanently?',
          text: 'This cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API}/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');
          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{ showLoading(false); }
        return;
      }
    });

    /* ========= Save Sort (visible rows) -> /reorder ========= */
    $('hcBtnSaveSort').addEventListener('click', async () => {
      if (!(canEdit || canCreate)) return;

      const tab = currentTab();
      if (tab === 'trash'){ err('Sort saving is disabled in Trash tab'); return; }

      const inputs = Array.from(document.querySelectorAll(`#${tab==='active'?'hcTbodyActive':'hcTbodyInactive'} .hc-sort-input`));
      const items = inputs
        .map(i => ({
          id: parseInt(i.dataset.id || '0', 10),
          sort_order: parseInt(i.value || '0', 10) || 0
        }))
        .filter(x => x.id > 0);

      if (!items.length) { err('Nothing to save'); return; }

      showLoading(true);
      try{
        const res = await fetchWithTimeout(`${API}/reorder`, {
          method: 'POST',
          headers: { ...authHeaders(), 'Content-Type':'application/json' },
          body: JSON.stringify({ items })
        }, 15000);
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Reorder failed');
        ok('Sort order saved');
        await loadTab(tab);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{ showLoading(false); }
    });

    /* ========= Submit (Create/Update) ========= */
    $('hcItemForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;

      if ($('hcItemForm').dataset.mode === 'view') return;

      const intent = $('hcItemForm').dataset.intent || 'create';
      const isEdit = (intent === 'edit') && !!$('hcUuid').value;

      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      overlaySyncToHidden();

      const title = ($('hcTitleInput').value || '').trim();
      const slug  = ($('hcSlugInput').value || '').trim();
      const status= ($('hcStatus').value || 'draft').trim();
      const alt   = ($('hcAltText').value || '').trim();
      const sort  = String(parseInt($('hcSortOrder').value || '0', 10) || 0);

      const desktopPath = ($('hcDesktopPath').value || '').trim();
      const desktopFile = $('hcDesktopFile').files?.[0] || null;

      if (!desktopFile && !desktopPath && !isEdit){
        err('Desktop image is required (upload or path/url).');
        return;
      }

      const fd = new FormData();
      if (title) fd.append('title', title);
      if (slug) fd.append('slug', slug);
      fd.append('status', status);
      fd.append('sort_order', sort);
      if (alt) fd.append('alt_text', alt);

      const pub = ($('hcPublishAt').value || '').trim();
      const exp = ($('hcExpireAt').value || '').trim();
      if (pub) fd.append('publish_at', pub);
      if (exp) fd.append('expire_at', exp);

      const overlayText = ($('hcOverlayHidden').value || '').trim();
      if (overlayText) fd.append('overlay_text', overlayText);

      if (desktopFile) fd.append('desktop_image', desktopFile);
      else if (desktopPath) fd.append('image_url', desktopPath);

      const mobilePath = ($('hcMobilePath').value || '').trim();
      const mobileFile = $('hcMobileFile').files?.[0] || null;
      if (mobileFile) fd.append('mobile_image', mobileFile);
      else if (mobilePath) fd.append('mobile_image_url', mobilePath);

      if (isEdit){
        fd.append('_method', 'PUT');
        if ($('hcDesktopRemove').checked) fd.append('desktop_image_remove', '1');
        if ($('hcMobileRemove').checked) fd.append('mobile_image_remove', '1');
      }

      const url = isEdit ? `${API}/${encodeURIComponent($('hcUuid').value)}` : `${API}`;

      saving = true;
      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        const res = await fetchWithTimeout(url, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 20000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false){
          let msg = js?.message || 'Save failed';
          if (js?.errors){
            const k = Object.keys(js.errors)[0];
            if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
          }
          throw new Error(msg);
        }

        ok(isEdit ? 'Updated' : 'Created');
        bootstrap.Modal.getInstance($('hcItemModal'))?.hide();

        state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    /* ========= Init ========= */
    (async () => {
      showLoading(true);
      try{
        setInactiveStatusUI();
        await fetchMe();
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.message || 'Init failed');
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>
@endpush
