{{-- resources/views/modules/achievement/manageAchievements.blade.php --}}
@section('title','Achievements')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  /* =========================
   * Achievements - Admin UI
   * (structured like Contact Info reference)
   * ========================= */

  /* Tabs */
  .ach-tabs.nav-tabs{border-color:var(--line-strong)}
  .ach-tabs .nav-link{color:var(--ink)}
  .ach-tabs .nav-link.active{
    background:var(--surface);
    border-color:var(--line-strong) var(--line-strong) var(--surface);
  }

  /* Card/Table */
  .ach-card{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    overflow:visible;
  }
  .ach-card .card-body{overflow:visible}
  .ach-table{--bs-table-bg:transparent}
  .ach-table thead th{
    font-weight:650;
    color:var(--muted-color);
    font-size:13px;
    border-bottom:1px solid var(--line-strong);
    background:var(--surface);
  }
  .ach-table thead.sticky-top{z-index:3}
  .ach-table tbody tr{border-top:1px solid var(--line-soft)}
  .ach-table tbody tr:hover{background:var(--page-hover)}
  .ach-muted{color:var(--muted-color)}
  .ach-small{font-size:12.5px}

  /* Horizontal scroll */
  .table-responsive{
    display:block;
    width:100%;
    max-width:100%;
    overflow-x:auto !important;
    overflow-y:visible !important;
    -webkit-overflow-scrolling:touch;
    position:relative;
  }
  .table-responsive > table{width:max-content; min-width:1260px;}
  .table-responsive th,.table-responsive td{white-space:nowrap;}

  /* Dropdown - keep high z-index */
  .table-responsive .dropdown{position:relative}
  .ach-dd-toggle{border-radius:10px}
  .dropdown-menu{
    border-radius:12px;
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-2);
    min-width:230px;
    z-index:99999; /* ✅ higher */
  }
  .dropdown-menu.show{display:block !important}
  .dropdown-item{display:flex;align-items:center;gap:.6rem}
  .dropdown-item i{width:16px;text-align:center}
  .dropdown-item.text-danger{color:var(--danger-color) !important}

  /* Soft badges */
  .badge-soft{
    display:inline-flex;align-items:center;gap:6px;
    padding:.35rem .55rem;border-radius:999px;font-size:12px;font-weight:600
  }
  .badge-soft-primary{
    background:color-mix(in oklab, var(--primary-color) 12%, transparent);
    color:var(--primary-color)
  }
  .badge-soft-success{
    background:color-mix(in oklab, var(--success-color, #16a34a) 12%, transparent);
    color:var(--success-color, #16a34a)
  }
  .badge-soft-warning{
    background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
    color:var(--warning-color, #f59e0b)
  }
  .badge-soft-muted{
    background:color-mix(in oklab, var(--muted-color) 10%, transparent);
    color:var(--muted-color)
  }
  .badge-soft-danger{
    background:color-mix(in oklab, var(--danger-color) 14%, transparent);
    color:var(--danger-color)
  }

  /* Loading overlay */
  .ach-loading{
    position:fixed; inset:0;
    background:rgba(0,0,0,.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
    backdrop-filter:blur(2px);
  }
  .ach-loading .box{
    background:var(--surface);
    padding:18px 20px;
    border-radius:14px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:0 10px 26px rgba(0,0,0,.3);
  }
  .ach-spin{
    width:38px;height:38px;border-radius:50%;
    border:4px solid rgba(148,163,184,.3);
    border-top:4px solid var(--primary-color);
    animation:achSpin 1s linear infinite;
  }
  @keyframes achSpin{to{transform:rotate(360deg)}}

  /* Toolbar */
  .ach-toolbar{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    padding:12px 12px;
  }
  .ach-toolbar .ach-search{min-width:280px; position:relative;}
  .ach-toolbar .ach-search input{padding-left:40px;}
  .ach-toolbar .ach-search i{
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%); opacity:.6;
  }
  @media (max-width: 768px){
    .ach-toolbar .ach-row{flex-direction:column; align-items:stretch !important;}
    .ach-toolbar .ach-search{min-width:100%;}
    .ach-toolbar .ach-actions{display:flex; gap:8px; flex-wrap:wrap;}
    .ach-toolbar .ach-actions .btn{flex:1; min-width:140px;}
  }

  /* Image preview in table */
  .img-cell{display:flex;align-items:center;gap:10px}
  .img-thumb{
    width:44px;height:44px;border-radius:10px;
    border:1px solid var(--line-soft);
    overflow:hidden;flex:0 0 44px;
    background:color-mix(in oklab, var(--muted-color) 10%, transparent)
  }
  .img-thumb img{width:100%;height:100%;object-fit:cover;display:block}
  .img-placeholder{
    width:44px;height:44px;border-radius:10px;
    border:1px dashed var(--line-soft);
    display:flex;align-items:center;justify-content:center;
    color:var(--muted-color);
    background:transparent
  }
  .img-cell .img-meta{display:flex;flex-direction:column;gap:2px}
  .img-cell .img-meta a{font-size:12.5px}
  .img-cell .img-meta .muted{font-size:12px;color:var(--muted-color)}

  /* =========================
     Mini RTE (same as your Achievements version)
  ========================= */
  .ach-rte{
    border:1px solid var(--line-strong);
    border-radius:14px;
    overflow:hidden;
    background:var(--surface);
  }
  .ach-rte .bar{
    display:flex;align-items:center;gap:6px;flex-wrap:wrap;
    padding:8px;border-bottom:1px solid var(--line-strong);
    background:color-mix(in oklab, var(--surface) 92%, transparent);
  }
  .ach-rte .btnx{
    border:1px solid var(--line-soft);
    background:transparent;
    color:var(--ink);
    padding:7px 9px;
    border-radius:10px;
    line-height:1;
    cursor:pointer;
    display:inline-flex;align-items:center;justify-content:center;
  }
  .ach-rte .btnx:hover{background:var(--page-hover)}
  .ach-rte .btnx.active{
    background:color-mix(in oklab, var(--primary-color) 14%, transparent);
    border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
  }
  .ach-rte .mode{
    margin-left:auto;display:flex;border:1px solid var(--line-soft);border-radius:12px;overflow:hidden
  }
  .ach-rte .mode button{
    border:0;background:transparent;color:var(--ink);
    padding:7px 12px;font-size:12px;cursor:pointer
  }
  .ach-rte .mode button.active{
    background:color-mix(in oklab, var(--primary-color) 12%, transparent);
    font-weight:800;
  }
  .ach-rte .area{position:relative}
  .ach-rte .editor{min-height:220px;padding:12px;outline:none}
  .ach-rte .editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}
  .ach-rte textarea{
    display:none;width:100%;min-height:220px;padding:12px;border:0;outline:none;resize:vertical;
    background:transparent;color:var(--ink);
    font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size:12.5px;line-height:1.45;
  }
  .ach-rte.code .editor{display:none}
  .ach-rte.code textarea{display:block}
</style>
@endpush

@section('content')
<div class="crs-wrap">

  {{-- Global Loading --}}
  <div id="achLoading" class="ach-loading" aria-hidden="true">
    <div class="box">
      <div class="ach-spin"></div>
      <div class="ach-small">Loading…</div>
    </div>
  </div>

  {{-- Top Toolbar --}}
  <div class="ach-toolbar mb-3">
    <div class="d-flex align-items-center justify-content-between gap-2 ach-row">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="ach-small ach-muted mb-0">Per Page</label>
          <select id="achPerPage" class="form-select" style="width:96px;">
            <option>10</option>
            <option selected>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        <div class="ach-search">
          <i class="fa fa-search"></i>
          <input id="achSearch" type="search" class="form-control" placeholder="Search by title / department / body…">
        </div>

        <button id="achBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#achFilterModal">
          <i class="fa fa-sliders me-1"></i>Filter
        </button>

        <button id="achBtnReset" class="btn btn-light">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
      </div>

      <div class="ach-actions" id="achWriteControls" style="display:none;">
        <button id="achBtnAdd" type="button" class="btn btn-primary">
          <i class="fa fa-plus me-1"></i> Add Achievement
        </button>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs ach-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#achTabActive" role="tab" aria-selected="true">
        <i class="fa-solid fa-trophy me-2"></i>Achievements
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#achTabDraft" role="tab" aria-selected="false">
        <i class="fa-solid fa-file-pen me-2"></i>Draft
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#achTabTrash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- PUBLISHED / ACTIVE --}}
    <div class="tab-pane fade show active" id="achTabActive" role="tabpanel">
      <div class="card ach-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ach-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">Image</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:240px;">Department</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:220px;">Published At</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="achTbodyActive">
                <tr><td colspan="8" class="text-center ach-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="achEmptyActive" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trophy mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ach-muted">No achievements found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ach-small ach-muted" id="achInfoActive">—</div>
            <nav><ul id="achPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- DRAFT --}}
    <div class="tab-pane fade" id="achTabDraft" role="tabpanel">
      <div class="card ach-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ach-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">Image</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:240px;">Department</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:220px;">Saved At</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="achTbodyDraft">
                <tr><td colspan="8" class="text-center ach-muted" style="padding:38px;">Click Draft tab to load…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="achEmptyDraft" class="p-4 text-center" style="display:none;">
            <i class="fa fa-file-pen mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ach-muted">No drafts found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ach-small ach-muted" id="achInfoDraft">—</div>
            <nav><ul id="achPagerDraft" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="achTabTrash" role="tabpanel">
      <div class="card ach-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ach-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">Image</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:240px;">Department</th>
                  <th style="width:220px;">Deleted At</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="achTbodyTrash">
                <tr><td colspan="7" class="text-center ach-muted" style="padding:38px;">Click Bin tab to load…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="achEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ach-muted">Bin is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ach-small ach-muted" id="achInfoTrash">—</div>
            <nav><ul id="achPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="achFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Achievements</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="achModalDept" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Featured</label>
            <select id="achModalFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Sort</label>
            <select id="achModalSort" class="form-select">
              <option value="created_at">Created At</option>
              <option value="published_at">Published At</option>
              <option value="title">Title</option>
              <option value="views_count">Views</option>
              <option value="id">ID</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Direction</label>
            <select id="achModalDir" class="form-select">
              <option value="desc">Desc</option>
              <option value="asc">Asc</option>
            </select>
          </div>

          <div class="col-12">
            <div class="form-text">
              Status is controlled by tabs: <b>Achievements</b> = Published, <b>Draft</b> = Draft.
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="achBtnApplyFilters" type="button">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="achItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="achItemForm" autocomplete="off" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="achItemModalTitle">Add Achievement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="achIdOrUuid">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Department</label>
            <select class="form-select" id="achDepartmentId">
              <option value="">Global (no department)</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="achStatus">
              <option value="draft" selected>Draft</option>
              <option value="published">Published</option>
            </select>
            <div class="form-text">Draft goes to Draft tab, Published goes to Achievements.</div>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="achFeatured">
              <label class="form-check-label" for="achFeatured">Featured on Home</label>
            </div>
          </div>

          <div class="col-md-3" id="achPublishedAtWrap" style="display:none;">
            <label class="form-label">Published At</label>
            <input type="datetime-local" class="form-control" id="achPublishedAt">
            <div class="form-text">Auto-set when status becomes Published (you can change).</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input class="form-control" id="achTitle" name="title" maxlength="255" placeholder="Achievement title">
          </div>

          <div class="col-md-3">
            <label class="form-label">Slug (optional)</label>
            <input class="form-control" id="achSlug" maxlength="160" placeholder="auto from title if empty">
          </div>

          <div class="col-md-6">
            <label class="form-label">Cover Image (optional)</label>
            <input type="file" class="form-control" id="achCoverImage" accept="image/*">
            <div class="form-text">Uploads as <code>cover_image</code>.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Attachments (optional)</label>
            <input type="file" class="form-control" id="achAttachments" multiple>
            <div class="form-text">Uploads as <code>attachments[]</code>.</div>
          </div>

          <div class="col-md-12" id="achCurrentImageWrap" style="display:none;">
            <label class="form-label">Current Image</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="img-thumb" style="width:54px;height:54px;">
                <img id="achCurrentImagePreview" src="" alt="preview">
              </div>
              <a href="#" target="_blank" rel="noopener" id="achCurrentImageLink" class="small">Open image</a>
              <span class="text-muted small" id="achCurrentImageText"></span>

              <div class="form-check ms-2" id="achImageRemoveWrap" style="display:none;">
                <input class="form-check-input" type="checkbox" id="achImageRemove">
                <label class="form-check-label" for="achImageRemove">Remove image</label>
              </div>
            </div>
          </div>

          <div class="col-md-12" id="achCurrentAttachmentsWrap" style="display:none;">
            <label class="form-label">Current Attachments</label>
            <div class="list-group" id="achCurrentAttachmentsList"></div>
            <div class="d-flex align-items-center gap-2 mt-2" id="achAttachmentsModeWrap" style="display:none;">
              <label class="text-muted small mb-0">When uploading new attachments:</label>
              <select class="form-select" id="achAttachmentsMode" style="max-width:180px;">
                <option value="append">Append</option>
                <option value="replace">Replace</option>
              </select>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Body <span class="text-danger">*</span></label>

            <div class="ach-rte" id="achRte">
              <div class="bar">
                <button type="button" class="btnx" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="btnx" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="btnx" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
                <span style="width:1px;height:24px;background:var(--line-soft);margin:0 4px"></span>
                <button type="button" class="btnx" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="btnx" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
                <span style="width:1px;height:24px;background:var(--line-soft);margin:0 4px"></span>
                <button type="button" class="btnx" data-cmd="createLink" title="Link"><i class="fa fa-link"></i></button>
                <button type="button" class="btnx" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                <div class="mode">
                  <button type="button" class="active" data-mode="text">Text</button>
                  <button type="button" data-mode="code">Code</button>
                </div>
              </div>

              <div class="area">
                <div id="achBodyEditor" class="editor" contenteditable="true" data-placeholder="Write achievement details…"></div>
                <textarea id="achBodyCode" spellcheck="false" placeholder="HTML code…"></textarea>
              </div>
            </div>

            <textarea id="achBodyHidden" name="body" hidden></textarea>
            <div class="form-text">HTML allowed. Use Text mode or edit raw Code.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Metadata (JSON) (optional)</label>
            <textarea class="form-control" id="achMetadata" rows="4" placeholder='{"type":"award","level":"state"}'></textarea>
            <div class="form-text">Keep it valid JSON. Empty = null.</div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="achSaveBtn" type="submit">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="achToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="achToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="achToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="achToastErrText">Something went wrong</div>
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
  if (window.__ACHIEVEMENTS_MODULE_INIT__) return;
  window.__ACHIEVEMENTS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  const esc = (str) => (str ?? '').toString().replace(/[&<>"']/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]));

  const num = (v, d=0) => {
    const n = parseInt(String(v ?? ''), 10);
    return Number.isFinite(n) ? n : d;
  };

  const normalizeLink = (src) => {
    const raw = (src ?? '');
    const s = (typeof raw === 'string' ? raw : String(raw)).trim().replace(/\\/g,'/');
    if(!s) return '';
    if(/^data:/i.test(s)) return s;
    if(/^blob:/i.test(s)) return s;
    if(/^https?:\/\//i.test(s)) return s;
    if(s.startsWith('//')) return s;
    if(s.startsWith('/')) return s;
    return '/' + s;
  };

  const dtLocalToServer = (v) => {
    const s = (v||'').toString().trim();
    if(!s) return null;
    return s.replace('T',' ') + ':00';
  };
  const serverToDtLocal = (v) => {
    const s=(v||'').toString().trim();
    if(!s) return '';
    const iso = s.includes('T') ? s : s.replace(' ', 'T');
    return iso.slice(0,16);
  };

  const nowLocalInput = () => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2,'0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  };

  function parseJsonOrThrow(txt){
    const s=(txt||'').trim(); if(!s) return null;
    try{
      const obj=JSON.parse(s);
      if(obj===null) return null;
      if(typeof obj!=='object') throw new Error('Metadata must be a JSON object/array');
      return obj;
    }catch(e){ throw new Error('Metadata JSON invalid: '+e.message); }
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try { return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally { clearTimeout(t); }
  }

  // --------- ✅ FIX: supports all common API response shapes ----------
  function normalizeListResponse(js, fallbackPage=1, fallbackPer=20){
    const items =
      (Array.isArray(js?.data) && js.data) ||
      (Array.isArray(js?.items) && js.items) ||
      (Array.isArray(js?.result) && js.result) ||
      (Array.isArray(js?.data?.data) && js.data.data) ||
      (Array.isArray(js?.data?.items) && js.data.items) ||
      (Array.isArray(js?.data?.results) && js.data.results) ||
      (Array.isArray(js) && js) ||
      [];

    let p = js?.pagination || js?.meta || js?.data?.pagination || js?.data?.meta || null;

    if (!p && js?.data && typeof js.data === 'object' && !Array.isArray(js.data)){
      const d = js.data;
      if ('current_page' in d || 'last_page' in d || 'total' in d) p = d;
    }
    if (!p && js && typeof js === 'object' && ('current_page' in js || 'last_page' in js || 'total' in js)) p = js;

    const page = num(p?.page ?? p?.current_page ?? js?.current_page, fallbackPage);
    const per_page = num(p?.per_page ?? p?.perPage ?? p?.page_size ?? p?.limit ?? js?.per_page, fallbackPer);

    const total = num(p?.total ?? js?.total, items.length);
    const last_page = num(p?.last_page ?? js?.last_page, Math.max(1, Math.ceil((total || items.length || 1) / (per_page || 1))));

    return { items, pagination: { page, per_page, total, last_page } };
  }

  function pickImageFromRow(r){
    const tryVal = (v) => {
      if(!v) return '';
      if(typeof v === 'string') return v.trim();
      if(typeof v === 'object'){
        const u = v.url || v.path || v.full_url || v.fullUrl || v.file_url || v.fileUrl || v.src || v.href || '';
        if(typeof u === 'string' && u.trim()) return u.trim();
      }
      return '';
    };

    const direct = [
      r.cover_image_url, r.cover_image,
      r.image_url, r.image,
      r.thumbnail_url, r.thumbnail,
      r.thumb_url, r.thumb,
      r.image_path, r.photo_url, r.banner_url
    ];
    for(const c of direct){
      const out = tryVal(c);
      if(out) return out;
    }

    const nested = [r.media, r.image_media, r.cover, r.thumbnail_media, r.thumb_media];
    for(const o of nested){
      const out = tryVal(o);
      if(out) return out;
    }

    return '';
  }

  function pickAttachmentsFromRow(r){
    let a = r.attachments ?? r.attachments_json ?? r.attachmentsJson ?? null;

    if (typeof a === 'string'){
      const s = a.trim();
      if (!s) return [];
      try { a = JSON.parse(s); } catch(_) { return []; }
    }

    if (Array.isArray(a)) return a;
    if (r.data?.attachments && Array.isArray(r.data.attachments)) return r.data.attachments;
    return [];
  }

  const pubValue = (r) => String(r?.published_at || r?.publish_at || '').trim();
  const statusValue = (r) => String(r?.status || '').toLowerCase().trim();

  function isPublished(r){
    if (statusValue(r) === 'published') return true;
    return !!pubValue(r);
  }
  function isDraft(r){
    if (statusValue(r) === 'draft') return true;
    return !isPublished(r);
  }

  function badgeFeatured(v){
    const on = ((+v) === 1) || v === true;
    return on
      ? `<span class="badge-soft badge-soft-primary"><i class="fa fa-star"></i> Yes</span>`
      : `<span class="badge-soft badge-soft-muted"><i class="fa fa-star"></i> No</span>`;
  }

  function badgeStatus(r){
    if (isPublished(r)){
      return `<span class="badge-soft badge-soft-success"><i class="fa fa-circle-check"></i> Published</span>`;
    }
    return `<span class="badge-soft badge-soft-warning"><i class="fa fa-circle-pause"></i> Draft</span>`;
  }

  function deptBadge(row){
    const name = row.department_title || row.department_name || row.department?.title || row.department?.name || '';
    if(name){
      return `<span class="badge-soft badge-soft-primary"><i class="fa fa-building"></i> ${esc(name)}</span>`;
    }
    return `<span class="badge-soft badge-soft-muted"><i class="fa fa-globe"></i> Global</span>`;
  }

  function imagePreviewCell(imgUrl, titleText){
    const src = normalizeLink(imgUrl);
    if(!src){
      return `
        <div class="img-cell">
          <div class="img-placeholder"><i class="fa fa-image"></i></div>
          <div class="img-meta"><div class="muted">No image</div></div>
        </div>`;
    }
    const fileName = (src.split('/').slice(-1)[0] || '');
    return `
      <div class="img-cell">
        <a class="img-thumb" href="${esc(src)}" target="_blank" rel="noopener" title="Open image">
          <img src="${esc(src)}" alt="${esc(titleText||'image')}" loading="eager" decoding="async">
        </a>
        <div class="img-meta">
          <a href="${esc(src)}" target="_blank" rel="noopener">Open</a>
          <div class="muted">${esc(fileName)}</div>
        </div>
      </div>`;
  }

  function renderPager(pagerEl, tabKey, page, totalPages){
    if(!pagerEl) return;

    const item=(p,label,dis=false,act=false)=>{
      if(dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if(act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
    };

    let html='';
    html += item(Math.max(1,page-1),'Previous',page<=1);
    const st=Math.max(1,page-2), en=Math.min(totalPages,page+2);
    for(let p=st;p<=en;p++) html += item(p,p,false,p===page);
    html += item(Math.min(totalPages,page+1),'Next',page>=totalPages);
    pagerEl.innerHTML = html;
  }

  function infoText(p, shown){
    const total = num(p?.total, 0);
    const page = num(p?.page, 1);
    const per  = num(p?.per_page, 20);
    if (!total) return '0 result(s)';
    const from = (page-1)*per + 1;
    const to   = (page-1)*per + (shown||0);
    return `Showing ${from} to ${to} of ${total} entries`;
  }

  // ✅ Dropdown actions html (adds Publish for draft rows)
  // ✅ Dropdown actions html (adds Publish for draft rows)
function rowActions(tabKey, canWrite, row){
  const draftRow = isDraft(row);

  let html = `
    <div class="dropdown text-end">
      <button type="button" class="btn btn-light btn-sm ach-dd-toggle" aria-expanded="false" title="Actions">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

  if (tabKey === 'active' || tabKey === 'draft'){
    html += `<li><button type="button" class="dropdown-item" data-action="edit" ${canWrite ? '' : 'disabled'}><i class="fa fa-pen-to-square"></i> Edit</button></li>`;

    // ✅ Publish only when row is Draft
    if (draftRow){
      html += `<li><button type="button" class="dropdown-item" data-action="publish" ${canWrite ? '' : 'disabled'}><i class="fa fa-circle-check"></i> Publish</button></li>`;
    }

    html += `<li><button type="button" class="dropdown-item" data-action="toggleFeatured" ${canWrite ? '' : 'disabled'}><i class="fa fa-star"></i> Toggle Featured</button></li>
             <li><hr class="dropdown-divider"></li>
             <li><button type="button" class="dropdown-item text-danger" data-action="delete" ${canWrite ? '' : 'disabled'}><i class="fa fa-trash"></i> Delete</button></li>`;
  } else {
    html += `<li><button type="button" class="dropdown-item" data-action="restore" ${canWrite ? '' : 'disabled'}><i class="fa fa-rotate-left"></i> Restore</button></li>
             <li><hr class="dropdown-divider"></li>
             <li><button type="button" class="dropdown-item text-danger" data-action="force" ${canWrite ? '' : 'disabled'}><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
  }

  html += `</ul></div>`;
  return html;
}

  document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    // loader + toasts
    const loadingEl = $('achLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('achToastOk');
    const toastErrEl = $('achToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('achToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('achToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json',
      ...(json ? { 'Content-Type': 'application/json' } : {})
    });

    // APIs
    const API = {
      me: '/api/users/me',
      departments: '/api/departments',

      list: (qs) => '/api/achievements' + (qs ? ('?' + qs) : ''),
      listByDept: (dept, qs) => `/api/departments/${encodeURIComponent(dept)}/achievements` + (qs ? ('?' + qs) : ''),

      one: (id) => `/api/achievements/${encodeURIComponent(id)}`,
      oneByDept: (dept, id) => `/api/departments/${encodeURIComponent(dept)}/achievements/${encodeURIComponent(id)}`,

      store: '/api/achievements',
      storeForDept: (dept) => `/api/departments/${encodeURIComponent(dept)}/achievements`,

      trash: (qs) => '/api/achievements-trash' + (qs ? ('?' + qs) : ''),

      update: (id) => `/api/achievements/${encodeURIComponent(id)}`,
      toggleFeatured: (id) => `/api/achievements/${encodeURIComponent(id)}/toggle-featured`,

      destroy: (id) => `/api/achievements/${encodeURIComponent(id)}`,
      restore: (id) => `/api/achievements/${encodeURIComponent(id)}/restore`,
      force: (id) => `/api/achievements/${encodeURIComponent(id)}/force`,
    };

    // permissions
    // permissions
const ACTOR = { role: '' };

// granular permissions (same as your snippet)
let canCreate = false, canEdit = false, canDelete = false, canPublish = false;

// keep your existing canWrite behavior for dropdown enable/disable
let canWrite = true;

function computePermissions(){
  const r = (ACTOR.role || '').toLowerCase();

  const createDeleteRoles = ['admin','super_admin','director','principal'];
  const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];

  canCreate  = createDeleteRoles.includes(r);
  canDelete  = createDeleteRoles.includes(r);
  canPublish = createDeleteRoles.includes(r);
  canEdit    = writeRoles.includes(r);

  // keep existing "write" flag used across the page
  canWrite = canEdit || canCreate || !r; // if role missing, keep same permissive fallback

  const wc = $('achWriteControls');
  if (wc) wc.style.display = canCreate ? 'flex' : 'none';

  // ✅ hide/show "Published" option in status dropdown
  updatePublishOption();
}
function updatePublishOption(){
  // Achievements status select
  if (!achStatus) return;

  const publishOption = achStatus.querySelector('option[value="published"]');
  if (publishOption){
    publishOption.style.display = canPublish ? '' : 'none';

    // if user can't publish but current value is published, force draft
    if (!canPublish && (achStatus.value || '').toLowerCase() === 'published'){
      achStatus.value = 'draft';
      applyStatusUI('draft'); // also hides PublishedAt field
    }
  }
}


    async function fetchMe(){
      try{
        const meRes = await fetchWithTimeout(API.me, { headers: authHeaders(false) }, 9000);
        if (meRes.ok){
          const js = await meRes.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // elements
    const perPageSel = $('achPerPage');
    const searchInput = $('achSearch');
    const btnReset = $('achBtnReset');
    const btnApplyFilters = $('achBtnApplyFilters');

    const modalDept = $('achModalDept');
    const modalFeatured = $('achModalFeatured');
    const modalSort = $('achModalSort');
    const modalDir = $('achModalDir');
    const filterModalEl = $('achFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    const tbodyA = $('achTbodyActive');
    const tbodyD = $('achTbodyDraft');
    const tbodyT = $('achTbodyTrash');

    const emptyA = $('achEmptyActive');
    const emptyD = $('achEmptyDraft');
    const emptyT = $('achEmptyTrash');

    const pagerA = $('achPagerActive');
    const pagerD = $('achPagerDraft');
    const pagerT = $('achPagerTrash');

    const infoA = $('achInfoActive');
    const infoD = $('achInfoDraft');
    const infoT = $('achInfoTrash');

    // modal form
    const itemModalEl = $('achItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('achItemModalTitle');
    const itemForm = $('achItemForm');
    const saveBtn = $('achSaveBtn');

    const achIdOrUuid = $('achIdOrUuid');
    const achDepartmentId = $('achDepartmentId');
    const achStatus = $('achStatus');
    const achPublishedAtWrap = $('achPublishedAtWrap');
    const achFeatured = $('achFeatured');
    const achPublishedAt = $('achPublishedAt');
    const achTitle = $('achTitle');
    const achSlug = $('achSlug');
    const achCoverImage = $('achCoverImage');
    const achAttachments = $('achAttachments');
    const achMetadata = $('achMetadata');

    const achCurrentImageWrap = $('achCurrentImageWrap');
    const achCurrentImagePreview = $('achCurrentImagePreview');
    const achCurrentImageLink = $('achCurrentImageLink');
    const achCurrentImageText = $('achCurrentImageText');
    const achImageRemoveWrap = $('achImageRemoveWrap');
    const achImageRemove = $('achImageRemove');

    const achCurrentAttachmentsWrap = $('achCurrentAttachmentsWrap');
    const achCurrentAttachmentsList = $('achCurrentAttachmentsList');
    const achAttachmentsModeWrap = $('achAttachmentsModeWrap');
    const achAttachmentsMode = $('achAttachmentsMode');

    // RTE
    const rteWrap = $('achRte');
    const rteEditor = $('achBodyEditor');
    const rteCode = $('achBodyCode');
    const bodyHidden = $('achBodyHidden');
    const rte = { mode:'text', enabled:true };

    function syncBodyToHidden(){
      const html = (rte.mode === 'code') ? (rteCode.value || '') : (rteEditor.innerHTML || '');
      bodyHidden.value = (html || '').trim();
    }
    function updateRteActive(){
      if (rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rteWrap?.querySelector(`.btnx[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }
    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rteWrap?.classList.toggle('code', rte.mode === 'code');
      rteWrap?.querySelectorAll('.mode button').forEach(b => b.classList.toggle('active', b.dataset.mode === rte.mode));

      const disableBar = (rte.mode === 'code') || !rte.enabled;
      rteWrap?.querySelectorAll('.bar .btnx').forEach(b => {
        b.disabled = disableBar;
        b.style.opacity = disableBar ? '0.55' : '';
        b.style.pointerEvents = disableBar ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rteCode.value = rteEditor.innerHTML || '';
        setTimeout(()=>{ try{ rteCode.focus(); }catch(_){ } }, 0);
      }else{
        rteEditor.innerHTML = rteCode.value || '';
        setTimeout(()=>{ try{ rteEditor.focus({preventScroll:true}); }catch(_){ try{ rteEditor.focus(); }catch(__){} } }, 0);
      }
      syncBodyToHidden();
      updateRteActive();
    }
    function setRteEnabled(on){
      rte.enabled = !!on;
      rteEditor?.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rteCode) rteCode.disabled = !on;

      const disableBar = (rte.mode === 'code') || !rte.enabled;
      rteWrap?.querySelectorAll('.bar .btnx').forEach(b => {
        b.disabled = disableBar;
        b.style.opacity = disableBar ? '0.55' : '';
        b.style.pointerEvents = disableBar ? 'none' : '';
      });
      rteWrap?.querySelectorAll('.mode button').forEach(b => {
        b.style.pointerEvents = on ? '' : 'none';
        b.style.opacity = on ? '' : '0.7';
      });
    }
    function getBodyHtml(){
      syncBodyToHidden();
      return (bodyHidden.value || '').toString().trim();
    }
    function bodyPlainText(html){
      return (html || '')
        .toString()
        .replace(/<style[\s\S]*?<\/style>/gi,' ')
        .replace(/<script[\s\S]*?<\/script>/gi,' ')
        .replace(/<[^>]*>/g,' ')
        .replace(/\s+/g,' ')
        .trim();
    }

    rteWrap?.querySelector('.bar')?.addEventListener('pointerdown', (e) => e.preventDefault());
    rteEditor?.addEventListener('input', () => { syncBodyToHidden(); updateRteActive(); });
    rteCode?.addEventListener('input', () => syncBodyToHidden());
    ['mouseup','keyup','click'].forEach(ev => rteEditor?.addEventListener(ev, updateRteActive));
    document.addEventListener('selectionchange', () => { if (document.activeElement === rteEditor) updateRteActive(); });

    document.addEventListener('click', (e) => {
      const modeBtn = e.target.closest('#achRte .mode button');
      if (modeBtn){ setRteMode(modeBtn.dataset.mode); return; }

      const btn = e.target.closest('#achRte .btnx');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      try{ rteEditor.focus({preventScroll:true}); }catch(_){ try{ rteEditor.focus(); }catch(__){} }

      const cmd = btn.getAttribute('data-cmd');
      if (cmd === 'createLink'){
        const url = prompt('Enter URL (https://...)');
        if (url) { try{ document.execCommand('createLink', false, url); }catch(_){ } }
      } else {
        try{ document.execCommand(cmd, false, null); }catch(_){ }
      }
      syncBodyToHidden();
      updateRteActive();
    });

    // Departments
    const DEPT_INDEX = new Map();
    async function loadDepartments(){
      const res = await fetchWithTimeout(API.departments, { headers: authHeaders(false) }, 15000);
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load departments');

      const rows = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
      const items = rows.filter(d => !d.deleted_at);

      const label = (d) => {
        const t = (d?.title || '').toString().trim();
        const n = (d?.name || '').toString().trim();
        return t || n || (`Department #${d?.id ?? ''}`.trim());
      };

      DEPT_INDEX.clear();
      items.forEach(d=>{
        const id = (d?.id ?? '').toString();
        if(id) DEPT_INDEX.set(id, id);
        const uuid = (d?.uuid ?? '').toString().trim();
        if(uuid) DEPT_INDEX.set(uuid, id);
        const slug = (d?.slug ?? '').toString().trim();
        if(slug) DEPT_INDEX.set(slug, id);
      });

      modalDept.innerHTML = `<option value="">All</option>` + items.map(d =>
        `<option value="${esc(String(d.id))}">${esc(label(d))}</option>`
      ).join('');

      achDepartmentId.innerHTML = `<option value="">Global (no department)</option>` + items.map(d =>
        `<option value="${esc(String(d.id))}">${esc(label(d))}</option>`
      ).join('');
    }

    // state
    const state = {
      perPage: num(perPageSel?.value, 20),
      filters: { q:'', department:'', featured:'', sort:'created_at', direction:'desc' },
      tabs: {
        active: { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        draft:  { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        trash:  { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
      },
      draftLoaded: false,
      trashLoaded: false
    };

    const getTabKey = () => {
      const a = document.querySelector('.ach-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#achTabActive';
      if (href === '#achTabTrash') return 'trash';
      if (href === '#achTabDraft') return 'draft';
      return 'active';
    };

    function buildQuery(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      if (tabKey === 'active' || tabKey === 'draft'){
        if (state.filters.featured !== '') params.set('featured', state.filters.featured);
        params.set('sort', state.filters.sort || 'created_at');
        params.set('direction', state.filters.direction || 'desc');

        // ✅ tab-controlled status
        params.set('published', tabKey === 'active' ? '1' : '0');
      }

      return params.toString();
    }

    function buildUrl(tabKey){
      if (tabKey === 'trash'){
        const params = new URLSearchParams();
        params.set('per_page', String(state.perPage));
        params.set('page', String(state.tabs.trash.page));
        const q = (state.filters.q || '').trim();
        if (q) params.set('q', q);
        return API.trash(params.toString());
      }

      const dept = (state.filters.department || '').trim();
      const qs = buildQuery(tabKey);
      return dept ? API.listByDept(dept, qs) : API.list(qs);
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyA : (tabKey==='draft' ? emptyD : emptyT);
      if (el) el.style.display = show ? '' : 'none';
    }

    function renderActive(){
      const rows = state.tabs.active.items || [];
      if (!tbodyA) return;

      if (!rows.length){
        tbodyA.innerHTML = '';
        setEmpty('active', true);
        renderPager(pagerA, 'active', state.tabs.active.pagination.page, state.tabs.active.pagination.last_page);
        if (infoA) infoA.textContent = infoText(state.tabs.active.pagination, 0);
        return;
      }
      setEmpty('active', false);

      tbodyA.innerHTML = rows.map(r => {
        const id = r.uuid || r.id || r.slug || '';
        const tt = r.title ? String(r.title) : '—';
        const img = pickImageFromRow(r);
        const published = r.published_at || r.publish_at || '';
        const views = r.views_count ?? r.view_count ?? 0;

        return `
          <tr data-id="${esc(id)}" data-tab="active">
            <td>${imagePreviewCell(img, tt)}</td>
            <td>
              <div class="fw-semibold">${esc(tt)}</div>
              <div class="ach-small ach-muted">${esc((r.slug||'') ? ('/' + String(r.slug)) : '')}</div>
            </td>
            <td>${deptBadge(r)}</td>
            <td>${badgeStatus(r)}</td>
            <td>${badgeFeatured(r.is_featured_home)}</td>
            <td>${esc(String(published || '—'))}</td>
            <td>${esc(String(views))}</td>
            <td class="text-end">${rowActions('active', canWrite, r)}</td>
          </tr>
        `;
      }).join('');

      renderPager(pagerA, 'active', state.tabs.active.pagination.page, state.tabs.active.pagination.last_page);
      if (infoA) infoA.textContent = infoText(state.tabs.active.pagination, rows.length);
    }

    function renderDraft(){
      const rows = state.tabs.draft.items || [];
      if (!tbodyD) return;

      if (!rows.length){
        tbodyD.innerHTML = '';
        setEmpty('draft', true);
        renderPager(pagerD, 'draft', state.tabs.draft.pagination.page, state.tabs.draft.pagination.last_page);
        if (infoD) infoD.textContent = infoText(state.tabs.draft.pagination, 0);
        return;
      }
      setEmpty('draft', false);

      tbodyD.innerHTML = rows.map(r => {
        const id = r.uuid || r.id || r.slug || '';
        const tt = r.title ? String(r.title) : '—';
        const img = pickImageFromRow(r);
        const savedAt = r.updated_at || r.created_at || '—';
        const views = r.views_count ?? r.view_count ?? 0;

        return `
          <tr data-id="${esc(id)}" data-tab="draft">
            <td>${imagePreviewCell(img, tt)}</td>
            <td>
              <div class="fw-semibold">${esc(tt)}</div>
              <div class="ach-small ach-muted">${esc((r.slug||'') ? ('/' + String(r.slug)) : '')}</div>
            </td>
            <td>${deptBadge(r)}</td>
            <td>${badgeStatus(r)}</td>
            <td>${badgeFeatured(r.is_featured_home)}</td>
            <td>${esc(String(savedAt || '—'))}</td>
            <td>${esc(String(views))}</td>
            <td class="text-end">${rowActions('draft', canWrite, r)}</td>
          </tr>
        `;
      }).join('');

      renderPager(pagerD, 'draft', state.tabs.draft.pagination.page, state.tabs.draft.pagination.last_page);
      if (infoD) infoD.textContent = infoText(state.tabs.draft.pagination, rows.length);
    }

    function renderTrash(){
      const rows = state.tabs.trash.items || [];
      if (!tbodyT) return;

      if (!rows.length){
        tbodyT.innerHTML = '';
        setEmpty('trash', true);
        renderPager(pagerT, 'trash', state.tabs.trash.pagination.page, state.tabs.trash.pagination.last_page);
        if (infoT) infoT.textContent = infoText(state.tabs.trash.pagination, 0);
        return;
      }
      setEmpty('trash', false);

      tbodyT.innerHTML = rows.map(r => {
        const id = r.uuid || r.id || r.slug || '';
        const tt = r.title ? String(r.title) : '—';
        const img = pickImageFromRow(r);
        const delAt = r.deleted_at || '—';

        return `
          <tr data-id="${esc(id)}" data-tab="trash">
            <td>${imagePreviewCell(img, tt)}</td>
            <td>
              <div class="fw-semibold">${esc(tt)}</div>
              <div class="ach-small ach-muted">${esc((r.slug||'') ? ('/' + String(r.slug)) : '')}</div>
            </td>
            <td>${deptBadge(r)}</td>
            <td>${esc(String(delAt))}</td>
            <td>${badgeStatus(r)}</td>
            <td>${badgeFeatured(r.is_featured_home)}</td>
            <td class="text-end">${rowActions('trash', canWrite, r)}</td>
          </tr>
        `;
      }).join('');

      renderPager(pagerT, 'trash', state.tabs.trash.pagination.page, state.tabs.trash.pagination.last_page);
      if (infoT) infoT.textContent = infoText(state.tabs.trash.pagination, rows.length);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyA : (tabKey==='draft' ? tbodyD : tbodyT);
      if (tbody){
        const cols = (tabKey==='trash') ? 7 : 8;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center ach-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const url = buildUrl(tabKey);
        const res = await fetchWithTimeout(url, { headers: authHeaders(false) }, 20000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || js?.error || 'Failed to load');

        const norm = normalizeListResponse(js, state.tabs[tabKey].page, state.perPage);

        let items = norm.items || [];
        let pagination = norm.pagination || { page:1, per_page:state.perPage, total:items.length, last_page:1 };

        // ✅ safety: enforce correct tab status even if API ignores "published"
        if (tabKey === 'active') items = items.filter(isPublished);
        if (tabKey === 'draft')  items = items.filter(isDraft);

        state.tabs[tabKey].items = items;
        state.tabs[tabKey].pagination = pagination;
        state.tabs[tabKey].page = pagination.page;
        state.tabs[tabKey].lastPage = pagination.last_page;

        if (tabKey === 'active') renderActive();
        else if (tabKey === 'draft') renderDraft();
        else renderTrash();
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].pagination = { page:1, per_page:state.perPage, total:0, last_page:1 };
        if (tabKey === 'active') renderActive();
        else if (tabKey === 'draft') renderDraft();
        else renderTrash();

        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
        console.error('Achievements load error:', e);
      }
    }

    // pager click
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page][data-tab]');
      if (!a) return;
      e.preventDefault();
      const tab = a.dataset.tab;
      const p = num(a.dataset.page, 1);
      if (!tab) return;
      if (p === state.tabs[tab].page) return;
      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = 1;
      if (state.draftLoaded) state.tabs.draft.page = 1;
      if (state.trashLoaded) state.tabs.trash.page = 1;
      loadTab(getTabKey());
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = num(perPageSel.value, 20);
      state.tabs.active.page = 1;
      if (state.draftLoaded) state.tabs.draft.page = 1;
      if (state.trashLoaded) state.tabs.trash.page = 1;
      loadTab(getTabKey());
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalDept) modalDept.value = state.filters.department || '';
      if (modalFeatured) modalFeatured.value = (state.filters.featured ?? '');
      if (modalSort) modalSort.value = state.filters.sort || 'created_at';
      if (modalDir) modalDir.value = state.filters.direction || 'desc';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.department = (modalDept?.value || '').trim();
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.sort = modalSort?.value || 'created_at';
      state.filters.direction = modalDir?.value || 'desc';

      state.tabs.active.page = 1;
      if (state.draftLoaded) state.tabs.draft.page = 1;
      if (state.trashLoaded) state.tabs.trash.page = 1;

      filterModal && filterModal.hide();
      loadTab(getTabKey());
    });

    btnReset?.addEventListener('click', () => {
      state.perPage = 20;
      state.filters = { q:'', department:'', featured:'', sort:'created_at', direction:'desc' };

      if (perPageSel) perPageSel.value = '20';
      if (searchInput) searchInput.value = '';
      if (modalDept) modalDept.value = '';
      if (modalFeatured) modalFeatured.value = '';
      if (modalSort) modalSort.value = 'created_at';
      if (modalDir) modalDir.value = 'desc';

      state.tabs.active.page = 1;
      if (state.draftLoaded) state.tabs.draft.page = 1;
      if (state.trashLoaded) state.tabs.trash.page = 1;

      loadTab(getTabKey());
    });

    // tabs
    document.querySelector('a[href="#achTabActive"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#achTabDraft"]')?.addEventListener('shown.bs.tab', () => {
      state.draftLoaded = true;
      loadTab('draft');
    });
    document.querySelector('a[href="#achTabTrash"]')?.addEventListener('shown.bs.tab', () => {
      state.trashLoaded = true;
      loadTab('trash');
    });

    // ---------- ✅ ACTION DROPDOWN FIX (Popper fixed strategy) ----------
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.ach-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.ach-dd-toggle');
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

    document.addEventListener('click', () => closeAllDropdownsExcept(null), { capture: true });

    // ---------- Status UI helpers ----------
    function applyStatusUI(status){
  const s = (status || 'draft').toLowerCase();
  if (s === 'published'){
    if (achPublishedAtWrap) achPublishedAtWrap.style.display = '';
    if (achPublishedAt && !String(achPublishedAt.value || '').trim()){
      achPublishedAt.value = nowLocalInput(); // ✅ auto set now
    }
  } else {
    if (achPublishedAt) achPublishedAt.value = '';
    if (achPublishedAtWrap) achPublishedAtWrap.style.display = 'none';
  }
}
achStatus?.addEventListener('change', () => applyStatusUI(achStatus.value));

    // ---------- Modal helpers ----------
    function resetForm(){
      itemForm.reset();
      achIdOrUuid.value = '';
      achCoverImage.value = '';
      achAttachments.value = '';
      achMetadata.value = '';
      achImageRemove.checked = false;

      if (achStatus) achStatus.value = 'draft';
      if (achPublishedAt) achPublishedAt.value = '';
      if (achPublishedAtWrap) achPublishedAtWrap.style.display = 'none';

      achCurrentImageWrap.style.display = 'none';
      achImageRemoveWrap.style.display = 'none';
      achCurrentImageLink.href = '#';
      achCurrentImageText.textContent = '';
      achCurrentImagePreview.src = '';

      achCurrentAttachmentsWrap.style.display = 'none';
      achCurrentAttachmentsList.innerHTML = '';
      achAttachmentsModeWrap.style.display = 'none';
      achAttachmentsMode.value = 'append';

      // RTE reset
      rteEditor.innerHTML = '';
      rteCode.value = '';
      bodyHidden.value = '';
      setRteEnabled(true);
      setRteMode('text');

      itemForm.dataset.intent = 'create';
      itemForm.dataset.mode = 'edit';
    }

    function setViewMode(viewOnly){
      Array.from(itemForm.querySelectorAll('input,select,textarea')).forEach(el=>{
        if (!el) return;
        if (el.type === 'file') el.disabled = !!viewOnly;
        else if (el.type === 'checkbox') el.disabled = !!viewOnly;
        else if (el.tagName === 'SELECT') el.disabled = !!viewOnly;
        else el.readOnly = !!viewOnly;
      });
      setRteEnabled(!viewOnly);
      if (saveBtn) saveBtn.style.display = viewOnly ? 'none' : '';
      achImageRemoveWrap.style.display = viewOnly ? 'none' : (achCurrentImageWrap.style.display === 'none' ? 'none' : '');
      achCurrentAttachmentsList.querySelectorAll('.att-remove').forEach(x=>{
        x.closest('.form-check').style.display = viewOnly ? 'none' : '';
        x.disabled = viewOnly;
        x.checked = false;
      });
      achAttachmentsModeWrap.style.display = viewOnly ? 'none' : (achCurrentAttachmentsWrap.style.display === 'none' ? 'none' : '');
    }

    function fillForm(r){
      achIdOrUuid.value = r.uuid || r.id || r.slug || '';

      const rawDept =
        (r.department_id ?? r.departmentId ?? '') ||
        (r.department?.id ?? '') ||
        (r.department_uuid ?? '') ||
        (r.department?.uuid ?? '') ||
        (r.department_slug ?? '') ||
        (r.department?.slug ?? '');

      const resolvedDeptId = rawDept ? (DEPT_INDEX.get(String(rawDept)) || String(rawDept)) : '';
      achDepartmentId.value = resolvedDeptId || '';

      achFeatured.checked = ((+r.is_featured_home) === 1);
      achTitle.value = r.title || '';
      achSlug.value = r.slug || '';

      // ✅ status dropdown + published time
      const publishedStr = (r.published_at || r.publish_at || '');
      const isPub = isPublished(r);
      if (achStatus) achStatus.value = isPub ? 'published' : 'draft';
      if (achPublishedAt) achPublishedAt.value = serverToDtLocal(publishedStr);
      applyStatusUI(achStatus?.value || (isPub ? 'published' : 'draft'));

      const html = (r.body || r.description || '');
      rteEditor.innerHTML = html;
      rteCode.value = html;
      bodyHidden.value = html;
      setRteMode('text');

      // metadata
      if(r.metadata && typeof r.metadata === 'object'){
        try{ achMetadata.value = JSON.stringify(r.metadata, null, 2); }catch(_){ achMetadata.value=''; }
      } else if (typeof r.metadata === 'string'){
        achMetadata.value = r.metadata;
      } else {
        achMetadata.value = '';
      }

      // image
      const img = normalizeLink(pickImageFromRow(r));
      if(img){
        achCurrentImageWrap.style.display = '';
        achImageRemoveWrap.style.display = '';
        achCurrentImageLink.href = img;
        achCurrentImageText.textContent = img;
        achCurrentImagePreview.src = img;
      }

      // attachments
      const atts = pickAttachmentsFromRow(r);
      if(atts.length){
        achCurrentAttachmentsWrap.style.display = '';
        achAttachmentsModeWrap.style.display = '';
        achCurrentAttachmentsList.innerHTML = atts.map((a, idx)=>{
          const url = normalizeLink(a.url || a.path || a.full_url || '');
          const name = a.name || (url ? url.split('/').pop() : ('Attachment ' + (idx+1)));
          const size = a.size ? (Math.round((+a.size)/1024) + ' KB') : '';
          const sub = [a.mime||'', size].filter(Boolean).join(' • ');
          const pathVal = (a.path || a.url || '').toString();
          return `
            <div class="list-group-item d-flex align-items-center justify-content-between gap-2">
              <div class="d-flex flex-column">
                <div class="fw-semibold small">${esc(name||'Attachment')}</div>
                <div class="text-muted small">${esc(sub || url || '')}</div>
              </div>
              <div class="d-flex align-items-center gap-2">
                ${url ? `<a class="btn btn-light btn-sm" href="${esc(url)}" target="_blank" rel="noopener"><i class="fa fa-arrow-up-right-from-square"></i></a>` : ''}
                <div class="form-check m-0">
                  <input class="form-check-input att-remove" type="checkbox" value="${esc(pathVal)}" id="ach_att_rm_${idx}">
                  <label class="form-check-label small" for="ach_att_rm_${idx}">Remove</label>
                </div>
              </div>
            </div>`;
        }).join('');
      }
    }

    async function fetchOne(identifier, deptHint=''){
      const dept = (deptHint || state.filters.department || '').trim();
      const url = dept ? API.oneByDept(dept, identifier) : API.one(identifier);
      const res = await fetchWithTimeout(url, { headers: authHeaders(false) }, 15000);
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js?.message || js?.error || 'Failed to fetch record');
      return js.item || js.data || js;
    }

    async function publishNow(identifier){
  const conf = await Swal.fire({
    title: 'Publish this achievement?',
    text: 'It will move from Draft to Achievements and become visible.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Publish',
    confirmButtonColor: '#16a34a'
  });
  if (!conf.isConfirmed) return;

  showLoading(true);
  try{
    const r = await fetchOne(identifier);

    const title = (r?.title || '').toString().trim();
    const body  = (r?.body || r?.description || '').toString();
    if (!title) throw new Error('Cannot publish: title missing');
    if (!bodyPlainText(body)) throw new Error('Cannot publish: body missing');

    let metaObj = null;
    if (r?.metadata && typeof r.metadata === 'object') metaObj = r.metadata;
    if (typeof r?.metadata === 'string') { try{ metaObj = JSON.parse(r.metadata); }catch(_){ metaObj = null; } }

    const fd = new FormData();

    const deptId = (r.department_id ?? r.departmentId ?? '') || (r.department?.id ?? '') || '';
    if (deptId) fd.append('department_id', String(deptId));

    fd.append('title', title);

    const slugVal = (r?.slug || '').toString().trim();
    if (slugVal) fd.append('slug', slugVal);

    const pubLocal  = nowLocalInput();
    const pubServer = dtLocalToServer(pubLocal);

    // ✅ send BOTH keys
    fd.append('published_at', pubServer || '');
    fd.append('publish_at',   pubServer || '');
    fd.append('status', 'published');

    fd.append('body', body);
    fd.append('is_featured_home', ((+r?.is_featured_home) === 1) ? '1' : '0');
    if (metaObj !== null) fd.append('metadata', JSON.stringify(metaObj));

    fd.append('_method', 'PUT');

    const res = await fetchWithTimeout(API.update(identifier), {
      method: 'POST',
      headers: authHeaders(false),
      body: fd
    }, 30000);

    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Publish failed');

    ok('Published');

    await loadTab('active');
    state.draftLoaded = true;
    await loadTab('draft');

    // ✅ switch to Achievements tab
    try{
      const link = document.querySelector('a[href="#achTabActive"]');
      link && bootstrap.Tab.getOrCreateInstance(link).show();
    }catch(_){}
  }catch(ex){
    err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
  }finally{
    showLoading(false);
  }
}


    // Add button
    $('achBtnAdd')?.addEventListener('click', () => {
      if (!canWrite) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Achievement';
      setViewMode(false);
      itemForm.dataset.intent = 'create';
      updatePublishOption(); 
      itemModal && itemModal.show();
      setTimeout(()=>{ try{ rteEditor.focus(); }catch(_){ } }, 150);
    });

    // Row actions
    document.addEventListener('click', async (e) => {
      const actionBtn = e.target.closest('button[data-action]');
      if (!actionBtn) return;

      const tr = actionBtn.closest('tr');
      const id = tr?.dataset?.id || '';
      const tab = tr?.dataset?.tab || 'active';
      const act = actionBtn.dataset.action || '';
      if (!id) return;

      const toggle = actionBtn.closest('.dropdown')?.querySelector('.ach-dd-toggle');
      if (toggle){ try{ bootstrap.Dropdown.getInstance(toggle)?.hide(); }catch(_){ } }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canWrite) return;

        showLoading(true);
        try{
          const data = await fetchOne(id);
          resetForm();
          fillForm(data || {});
          updatePublishOption(); 
          if (itemModalTitle) itemModalTitle.textContent = (act === 'view') ? 'View Achievement' : 'Edit Achievement';
          setViewMode(act === 'view');
          itemForm.dataset.intent = (act === 'view') ? 'view' : 'edit';
          itemForm.dataset.mode = (act === 'view') ? 'view' : 'edit';
          itemModal && itemModal.show();
        }catch(ex){
          err(ex.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'publish'){
        if (!canWrite){ err('You do not have permission for this action'); return; }
        await publishNow(id);
        return;
      }

      if (!canWrite){ err('You do not have permission for this action'); return; }

      if ((tab === 'active' || tab === 'draft') && act === 'toggleFeatured'){
        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.toggleFeatured(id), {
            method:'PATCH',
            headers: authHeaders(true),
            body: JSON.stringify({})
          }, 15000);
          const js = await res.json().catch(()=>({}));
          if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Toggle failed');
          ok('Updated');
          await loadTab('active');
          if (state.draftLoaded) await loadTab('draft');
        }catch(ex){
          err(ex.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if ((tab === 'active' || tab === 'draft') && act === 'delete'){
        const conf = await Swal.fire({
          title:'Move to Bin?',
          text:'This will soft delete the achievement.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonText:'Delete',
          confirmButtonColor:'#ef4444'
        });
        if(!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.destroy(id), { method:'DELETE', headers: authHeaders(false) }, 15000);
          const js = await res.json().catch(()=>({}));
          if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Delete failed');
          ok('Moved to Bin');
          await loadTab('active');
          if (state.draftLoaded) await loadTab('draft');
          if (state.trashLoaded) await loadTab('trash');
        }catch(ex){
          err(ex.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (tab === 'trash' && act === 'restore'){
        const conf = await Swal.fire({
          title:'Restore achievement?',
          icon:'question',
          showCancelButton:true,
          confirmButtonText:'Restore'
        });
        if(!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.restore(id), { method:'POST', headers: authHeaders(false) }, 15000);
          const js = await res.json().catch(()=>({}));
          if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Restore failed');
          ok('Restored');
          await loadTab('trash');
          await loadTab('active');
          if (state.draftLoaded) await loadTab('draft');
        }catch(ex){
          err(ex.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (tab === 'trash' && act === 'force'){
        const conf = await Swal.fire({
          title:'Delete permanently?',
          text:'This cannot be undone.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonText:'Delete Permanently',
          confirmButtonColor:'#ef4444'
        });
        if(!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.force(id), { method:'DELETE', headers: authHeaders(false) }, 15000);
          const js = await res.json().catch(()=>({}));
          if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Force delete failed');
          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex.message || 'Failed');
        }finally{
          showLoading(false);
        }
      }
    });

    // Submit (create / update)
    let saving = false;
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;

      if (itemForm.dataset.mode === 'view') return;
      if (!canWrite) { err('You do not have permission'); return; }

      if (!achTitle.value.trim()){ achTitle.focus(); return; }

      const bodyHtml = getBodyHtml();
      if (!bodyPlainText(bodyHtml)){
        err('Body is required');
        try{ rteEditor.focus(); }catch(_){}
        return;
      }

      let metaObj = null;
      try{ metaObj = parseJsonOrThrow(achMetadata.value); }
      catch(ex){ err(ex.message); achMetadata.focus(); return; }

      const isEdit = !!achIdOrUuid.value;
      const deptId = (achDepartmentId.value || '').trim();

      const endpoint = isEdit
        ? API.update(achIdOrUuid.value)
        : (deptId ? API.storeForDept(deptId) : API.store);

      // ✅ status & publish time behavior
      const status = (achStatus?.value || 'draft').toLowerCase();
      if (status === 'published'){
        if (!String(achPublishedAt.value || '').trim()){
          achPublishedAt.value = nowLocalInput(); // ✅ auto render
        }
      } else {
        // draft
        achPublishedAt.value = '';
      }

      saving = true;
      showLoading(true);
      try{
        const fd = new FormData();

        if (deptId) fd.append('department_id', deptId);
        fd.append('title', achTitle.value.trim());

        const slugVal = (achSlug.value || '').trim();
        if (slugVal) fd.append('slug', slugVal);

        fd.append('status', status);

        const pub = dtLocalToServer(achPublishedAt.value);

        // ✅ IMPORTANT FIX: Save publish time by sending BOTH keys
        if (status === 'published' && pub){
          fd.append('published_at', pub);
          fd.append('publish_at', pub);
        } else {
          // allow clearing to draft
          fd.append('published_at', '');
          fd.append('publish_at', '');
        }

        fd.append('body', bodyHtml);
        fd.append('is_featured_home', achFeatured.checked ? '1' : '0');

        if (metaObj !== null) fd.append('metadata', JSON.stringify(metaObj));

        // image remove support
        if (isEdit && achImageRemove.checked){
          fd.append('image_remove', '1');
          fd.append('cover_image_remove', '1');
        }

        // cover image upload
        const imgFile = achCoverImage?.files?.[0];
        if (imgFile){
          fd.append('cover_image', imgFile);
          fd.append('image', imgFile);
        }

        // attachments mode / remove
        if (isEdit && achCurrentAttachmentsWrap.style.display !== 'none'){
          fd.append('attachments_mode', achAttachmentsMode.value || 'append');
        }

        if (isEdit){
          const remove = [];
          achCurrentAttachmentsList.querySelectorAll('.att-remove:checked').forEach(x=>{
            const v = (x.value || '').toString().trim();
            if(v) remove.push(v);
          });
          remove.forEach(p => fd.append('attachments_remove[]', p));
        }

        const files = Array.from(achAttachments?.files || []);
        for (const f of files){
          fd.append('attachments[]', f);
        }

        if (isEdit){
          fd.append('_method', 'PUT');
        }

        const res = await fetchWithTimeout(endpoint, {
          method: 'POST',
          headers: authHeaders(false),
          body: fd
        }, 30000);

        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false){
          let msg = js.error || js.message || 'Save failed';
          if(js.errors){
            const k = Object.keys(js.errors)[0];
            if(k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
          }
          throw new Error(msg);
        }

        if (!isEdit && status === 'draft'){
          ok('Saved as Draft (see Draft tab)');
        } else {
          ok(isEdit ? 'Achievement updated' : 'Achievement created');
        }

        itemModal && itemModal.hide();

        // refresh lists so item appears in correct tab
        await loadTab('active');
        state.draftLoaded = true;
        await loadTab('draft');
        if (state.trashLoaded) await loadTab('trash');
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        console.error('Achievement save error:', ex);
      }finally{
        saving = false;
        showLoading(false);
      }
    });

    // init
    showLoading(true);
    try{
      await fetchMe();
      await loadDepartments();
      setRteEnabled(true);
      setRteMode('text');

      await loadTab('active');
    }catch(ex){
      err(ex.message || 'Initialization failed');
      console.error(ex);
    }finally{
      showLoading(false);
    }
  });
})();
</script>
@endpush
