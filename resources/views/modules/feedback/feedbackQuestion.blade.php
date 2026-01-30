{{-- resources/views/modules/feedbacks/manageFeedbackQuestions.blade.php --}}
@section('title','Feedback Questions')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown .dd-toggle{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:230px;z-index:5000}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible}

/* Shell */
.fq-wrap{padding:14px 4px}

/* Toolbar panel */
.fq-toolbar.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
.table-wrap .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* Column helpers */
th.col-title, td.col-title{width:420px;max-width:420px}
td.col-title{overflow:hidden}
td.col-title .sub{display:block;font-size:12.5px;color:var(--muted-color);max-width:390px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

th.col-group, td.col-group{width:260px;max-width:260px}
td.col-group{overflow:hidden}
td.col-group .pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:color-mix(in oklab, var(--primary-color) 10%, transparent);color:var(--primary-color);border:1px solid color-mix(in oklab, var(--primary-color) 18%, var(--line-soft));font-size:12px;font-weight:700;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
td.col-group .pill i{opacity:.85}

/* Badges */
.badge-soft-success{background:color-mix(in oklab, var(--success-color) 12%, transparent);color:var(--success-color)}
.badge-soft-warning{background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);color:var(--warning-color, #f59e0b)}
.badge-soft-muted{background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--muted-color)}

/* Empty */
.empty{color:var(--muted-color)}

/* Button loading state */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{content:'';position:absolute;width:16px;height:16px;top:50%;left:50%;margin:-8px 0 0 -8px;border:2px solid transparent;border-top:2px solid currentColor;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Responsive toolbar */
@media (max-width: 768px){
  .fq-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .fq-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Horizontal scroll */
.table-responsive > .table{width:max-content;min-width:1200px;}
.table-responsive th,
.table-responsive td{white-space:nowrap;}

.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.rte-row{margin-bottom:14px;}
.rte-wrap{border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;background:var(--surface);}
.rte-toolbar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:8px;border-bottom:1px solid var(--line-strong);background:color-mix(in oklab, var(--surface) 92%, transparent);}
.rte-btn{border:1px solid var(--line-soft);background:transparent;color:var(--ink);padding:7px 9px;border-radius:10px;line-height:1;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;user-select:none;}
.rte-btn:hover{background:var(--page-hover)}
.rte-btn.active{background:color-mix(in oklab, var(--primary-color) 14%, transparent);border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));}
.rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}

.rte-tabs{margin-left:auto;display:flex;border:1px solid var(--line-soft);border-radius:0;overflow:hidden;}
.rte-tabs .tab{border:0;border-right:1px solid var(--line-soft);border-radius:0;padding:7px 12px;font-size:12px;cursor:pointer;background:transparent;color:var(--ink);line-height:1;user-select:none;}
.rte-tabs .tab:last-child{border-right:0}
.rte-tabs .tab.active{background:color-mix(in oklab, var(--primary-color) 12%, transparent);font-weight:700;}
.rte-area{position:relative}
.rte-editor{min-height:200px;padding:12px 12px;outline:none;}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.rte-editor b, .rte-editor strong{font-weight:800}
.rte-editor i, .rte-editor em{font-style:italic}
.rte-editor u{text-decoration:underline}
.rte-editor h1{font-size:20px;margin:8px 0}
.rte-editor h2{font-size:18px;margin:8px 0}
.rte-editor h3{font-size:16px;margin:8px 0}
.rte-editor ul, .rte-editor ol{padding-left:22px}
.rte-editor p{margin:0 0 10px}
.rte-editor a{color:var(--primary-color);text-decoration:underline}
.rte-editor code{padding:2px 6px;border-radius:0;background:color-mix(in oklab, var(--muted-color) 14%, transparent);border:1px solid var(--line-soft);font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:12.5px;}
.rte-editor pre{padding:10px 12px;border-radius:0;background:color-mix(in oklab, var(--muted-color) 10%, transparent);border:1px solid var(--line-soft);overflow:auto;margin:8px 0;}
.rte-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre;}
.rte-code{display:none;width:100%;min-height:200px;padding:12px 12px;border:0;outline:none;resize:vertical;background:transparent;color:var(--ink);font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:12.5px;line-height:1.45;}
.rte-wrap.mode-code .rte-editor{display:none;}
.rte-wrap.mode-code .rte-code{display:block;}

/* Metadata box */
.meta-box{border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);overflow:hidden;}
.meta-box .meta-top{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);}
.meta-box textarea{width:100%;min-height:150px;border:0;outline:none;resize:vertical;padding:12px;background:transparent;color:var(--ink);font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:12.5px;line-height:1.45;}

/* ✅ FIX: Force global loading overlay controllable */
#globalLoading.loading-overlay{ display:none !important; }
#globalLoading.loading-overlay.is-show{ display:flex !important; }
</style>
@endpush

@section('content')
<div class="fq-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 fq-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:320px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by question / group…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAddItem">
              <i class="fa fa-plus me-1"></i> Add Question
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th class="col-title">Question</th>
                  <th class="col-group">Group</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Sort</th>
                  <th style="width:170px;">Publish</th>
                  <th style="width:170px;">Expire</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-active">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-circle-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active questions found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-active">—</div>
            <nav><ul id="pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="tab-inactive" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th class="col-title">Question</th>
                  <th class="col-group">Group</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Sort</th>
                  <th style="width:170px;">Publish</th>
                  <th style="width:170px;">Expire</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-inactive">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive questions found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-inactive">—</div>
            <nav><ul id="pager-inactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="tab-trash" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th class="col-title">Question</th>
                  <th class="col-group">Group</th>
                  <th style="width:170px;">Deleted</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-trash">
                <tr><td colspan="4" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-trash" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-trash">—</div>
            <nav><ul id="pager-trash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Group (quick pick)</label>
            <select id="modal_group_pick" class="form-select">
              <option value="">All</option>
            </select>
            <div class="form-text">This list is auto-built from your existing questions.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Group (text contains)</label>
            <input id="modal_group_text" class="form-control" placeholder="e.g. Teaching / Course / Infrastructure">
            <div class="form-text">If you choose a group above, this will auto-fill.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="group_title">Group A-Z</option>
              <option value="-group_title">Group Z-A</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="sort_order">Sort Order (Asc)</option>
              <option value="-sort_order">Sort Order (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="status">Status (Asc)</option>
              <option value="-status">Status (Desc)</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="itemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">Add Question</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">

          <div class="col-lg-6">
            <div class="row g-3">

              {{-- ✅ UPDATED: Group title dropdown + Other --}}
              <div class="col-12">
                <label class="form-label">Group Title <span class="text-danger">*</span></label>

                <select class="form-select" id="group_title_pick" required>
                  <option value="">Select group…</option>
                  {{-- options injected by JS from /api/feedback-questions/group-titles --}}
                  <option value="__other__">Other (add new)…</option>
                </select>

                <div id="groupTitleOtherWrap" style="display:none;margin-top:10px;">
                  <input class="form-control" id="group_title" maxlength="255" placeholder="Type new group title…">
                  <div id="groupTitleDupMsg" class="form-text text-danger" style="display:none;">
                    This group title already exists — please select it from the dropdown.
                  </div>
                  <div class="form-text">This will be saved as a new group title (unique).</div>
                </div>

                <div class="form-text">This will be used for filtering & grouping in UI.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Question Title <span class="text-danger">*</span></label>
                <input class="form-control" id="title" required maxlength="255" placeholder="e.g. The teacher explains concepts clearly.">
              </div>

              <div class="col-12">
                <label class="form-label">Hint (optional)</label>
                <input class="form-control" id="hint" maxlength="255" placeholder="e.g. Think about clarity and examples.">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Sort Order (optional)</label>
                <input class="form-control" id="sort_order" inputmode="numeric" placeholder="e.g., 10">
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At (optional)</label>
                <input type="datetime-local" class="form-control" id="publish_at">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At (optional)</label>
                <input type="datetime-local" class="form-control" id="expire_at">
              </div>

              <div class="col-12">
                <div class="meta-box">
                  <div class="meta-top">
                    <div class="fw-semibold"><i class="fa fa-brackets-curly me-2"></i>Metadata (JSON) (optional)</div>
                    <button type="button" class="btn btn-light btn-sm" id="btnFormatMeta">
                      <i class="fa fa-wand-magic-sparkles me-1"></i>Format
                    </button>
                  </div>
                  <textarea id="metadata" placeholder='Example: {"type":"rating","max":5,"required":true}'></textarea>
                </div>
                <div class="form-text">Must be valid JSON, otherwise it will be saved as null.</div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            {{-- RTE for Description --}}
            <div class="rte-row">
              <label class="form-label">Question Description (optional) (HTML allowed)</label>

              <div class="rte-wrap" id="descWrap">
                <div class="rte-toolbar" data-for="description">
                  <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-block="h1" title="Heading 1">H1</button>
                  <button type="button" class="rte-btn" data-block="h2" title="Heading 2">H2</button>
                  <button type="button" class="rte-btn" data-block="h3" title="Heading 3">H3</button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="rte-tabs">
                    <button type="button" class="tab active" data-mode="text">Text</button>
                    <button type="button" class="tab" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="rte-area">
                  <div id="descEditor" class="rte-editor" contenteditable="true" data-placeholder="Write additional details…"></div>
                  <textarea id="descCode" class="rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                    placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="rte-help">Use <b>Text</b> for rich editing or switch to <b>Code</b> to paste HTML.</div>
              <input type="hidden" id="description" name="description">
            </div>

            <div class="alert alert-light mb-0" style="border:1px dashed var(--line-soft);border-radius:14px;">
              <div class="small text-muted">
                <i class="fa fa-circle-info me-1"></i>
                Tip: Your API supports <b>status</b> and also legacy <b>active/is_active/isActive</b>. This page sends both for safety.
              </div>
            </div>

          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__FEEDBACK_QUESTIONS_MODULE_INIT__) return;
  window.__FEEDBACK_QUESTIONS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // =========================
  // ✅ API Map (Feedback Questions)
  // =========================
  const API = {
    me:        () => '/api/users/me',
    list:      () => '/api/feedback-questions',
    trashList: () => '/api/feedback-questions/trash',
    current:   () => '/api/feedback-questions/current',

    // ✅ NEW: group titles (saved list)
    groupTitles: () => '/api/feedback-questions/group-titles',

    create:    () => '/api/feedback-questions',
    update:    (id) => `/api/feedback-questions/${encodeURIComponent(id)}`,
    remove:    (id) => `/api/feedback-questions/${encodeURIComponent(id)}`,
    restore:   (id) => `/api/feedback-questions/${encodeURIComponent(id)}/restore`,
    force:     (id) => `/api/feedback-questions/${encodeURIComponent(id)}/force`,
    toggle:    (id) => `/api/feedback-questions/${encodeURIComponent(id)}`
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally {
      clearTimeout(t);
    }
  }

  function prettyDate(s){
    const v = (s ?? '').toString().trim();
    return v || '—';
  }

  function safeJsonParse(str){
    try{ return { ok:true, value: JSON.parse(str) }; }
    catch(e){ return { ok:false, value:null }; }
  }

  // =========================
  // ✅ Dropdown handling (manual)
  // =========================
  function hideAllDropdowns(exceptEl=null){
    document.querySelectorAll('.dd-toggle').forEach(t => {
      if (exceptEl && t === exceptEl) return;
      try { bootstrap.Dropdown.getInstance(t)?.hide(); } catch(_){}
    });
  }

  function getDropdownInstance(toggleEl){
    return bootstrap.Dropdown.getOrCreateInstance(toggleEl, {
      boundary: 'viewport',
      popperConfig: { strategy: 'fixed' }
    });
  }

  function bindGlobalDropdownClosers(){
    window.addEventListener('resize', () => hideAllDropdowns(), { passive:true });
    window.addEventListener('scroll', () => hideAllDropdowns(), { passive:true, capture:true });
    document.querySelectorAll('.table-responsive').forEach(w => {
      w.addEventListener('scroll', () => hideAllDropdowns(), { passive:true });
    });
    document.addEventListener('click', (e) => {
      const inside = e.target.closest('.dropdown');
      const isToggle = e.target.closest('.dd-toggle');
      if (!inside && !isToggle) hideAllDropdowns();
    });
  }

  // ✅ normalize + uniqueness helpers for group titles
  function normalizeGroupTitle(v){
    return (v ?? '').toString().trim().replace(/\s+/g,' ');
  }
  function lowerNorm(v){
    return normalizeGroupTitle(v).toLowerCase();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => {
      if (!globalLoading) return;
      globalLoading.classList.toggle('is-show', !!v);
    };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');
    const btnApplyFilters = $('btnApplyFilters');
    const writeControls = $('writeControls');
    const btnAddItem = $('btnAddItem');

    const tbodyActive = $('tbody-active');
    const tbodyInactive = $('tbody-inactive');
    const tbodyTrash = $('tbody-trash');

    const emptyActive = $('empty-active');
    const emptyInactive = $('empty-inactive');
    const emptyTrash = $('empty-trash');

    const pagerActive = $('pager-active');
    const pagerInactive = $('pager-inactive');
    const pagerTrash = $('pager-trash');

    const infoActive = $('resultsInfo-active');
    const infoInactive = $('resultsInfo-inactive');
    const infoTrash = $('resultsInfo-trash');

    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('modal_status');
    const modalSort = $('modal_sort');
    const modalGroupPick = $('modal_group_pick');
    const modalGroupText = $('modal_group_text');

    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');

    // ✅ group title UI pieces
    const groupTitlePick = $('group_title_pick');
    const groupTitleOtherWrap = $('groupTitleOtherWrap');
    const groupTitleDupMsg = $('groupTitleDupMsg');
    const groupTitleInput = $('group_title');

    const titleInput = $('title');
    const hintInput = $('hint');
    const statusSel = $('status');
    const sortOrderInput = $('sort_order');
    const publishAtInput = $('publish_at');
    const expireAtInput = $('expire_at');

    const metaText = $('metadata');
    const btnFormatMeta = $('btnFormatMeta');

    // ---------- permissions ----------
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createDeleteRoles = ['admin','super_admin','director','principal'];
      const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = createDeleteRoles.includes(r);
      canDelete = createDeleteRoles.includes(r);
      canEdit   = writeRoles.includes(r);
      if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout(API.me(), { headers: authHeaders() }, 8000);
        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // ---------- state ----------
    const state = {
      filters: { q:'', status:'', group_title:'', sort:'-updated_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      },
      groups: [],                 // discovered from loaded items
      groupTitlesSaved: []        // ✅ from /api/feedback-questions/group-titles
    };

    // ✅ group title pick rebuild + UI sync
    function rebuildGroupTitlePick(current=null){
      if (!groupTitlePick) return;

      const list = Array.isArray(state.groupTitlesSaved) ? state.groupTitlesSaved : [];
      const opts = list.map(t => `<option value="${esc(t)}">${esc(t)}</option>`).join('');

      const keep = current ?? groupTitlePick.value ?? '';
      groupTitlePick.innerHTML =
        `<option value="">Select group…</option>` +
        opts +
        `<option value="__other__">Other (add new)…</option>`;

      if (keep === '__other__'){
        groupTitlePick.value = '__other__';
      } else if (keep && list.includes(keep)){
        groupTitlePick.value = keep;
      } else if (keep && keep !== '' && !list.includes(keep)){
        groupTitlePick.value = '__other__';
      } else {
        groupTitlePick.value = '';
      }

      syncGroupTitleUI();
    }

    function isDuplicateGroupTitle(title){
      const t = lowerNorm(title);
      if (!t) return false;
      return (state.groupTitlesSaved || []).some(x => lowerNorm(x) === t);
    }

    function syncGroupTitleUI(){
      if (!groupTitlePick || !groupTitleInput || !groupTitleOtherWrap) return;

      const pick = (groupTitlePick.value || '').trim();

      // Other selected -> show input, require input, do NOT overwrite typed value
      if (pick === '__other__'){
        groupTitleOtherWrap.style.display = '';
        groupTitleInput.required = true;

        // live duplicate hint
        const cur = normalizeGroupTitle(groupTitleInput.value);
        const dup = cur ? isDuplicateGroupTitle(cur) : false;
        if (groupTitleDupMsg) groupTitleDupMsg.style.display = dup ? '' : 'none';

        return;
      }

      // existing selected -> hide input, set input value (used for submit)
      groupTitleOtherWrap.style.display = 'none';
      groupTitleInput.required = false;
      if (groupTitleDupMsg) groupTitleDupMsg.style.display = 'none';

      const resolved = normalizeGroupTitle(pick);
      groupTitleInput.value = resolved;
    }

    async function fetchGroupTitles(){
      try{
        const res = await fetchWithTimeout(API.groupTitles(), { headers: authHeaders() }, 10000);
        if (!res.ok) return;

        const js = await res.json().catch(()=> ({}));
        const arr = Array.isArray(js?.data) ? js.data : [];
        const set = new Set();
        arr.forEach(x => {
          const v = normalizeGroupTitle(x);
          if (v) set.add(v);
        });
        state.groupTitlesSaved = Array.from(set).sort((a,b)=>a.localeCompare(b));
        rebuildGroupTitlePick(groupTitlePick?.value || '');
      }catch(_){}
    }

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-active';
      if (href === '#tab-inactive') return 'inactive';
      if (href === '#tab-trash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const g = (state.filters.group_title || '').trim();
      if (g) params.set('group_title', g);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey !== 'trash') {
        let status = (state.filters.status || '').trim();
        if (!status) status = (tabKey === 'inactive') ? 'inactive' : 'active';
        params.set('status', status);
        params.set('active', status === 'active' ? '1' : '0');
      }

      const base = (tabKey === 'trash') ? API.trashList() : API.list();
      return `${base}?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function statusBadge(status){
      const s = (status || '').toString().toLowerCase().trim();
      if (s === 'active') return `<span class="badge badge-soft-success">Active</span>`;
      if (s === 'inactive') return `<span class="badge badge-soft-warning">Inactive</span>`;
      if (!s) return `<span class="badge badge-soft-muted">—</span>`;
      return `<span class="badge badge-soft-muted">${esc(s)}</span>`;
    }

    function groupPill(groupTitle){
      const g = (groupTitle || '—').toString().trim();
      if (!g) return `<span class="badge badge-soft-muted">—</span>`;
      return `<span class="pill" title="${esc(g)}"><i class="fa-solid fa-layer-group"></i>${esc(g)}</span>`;
    }

    function renderPager(tabKey){
      const pagerEl = tabKey === 'active' ? pagerActive : (tabKey === 'inactive' ? pagerInactive : pagerTrash);
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
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerEl.innerHTML = html;
    }

    function collectGroups(){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || []),
      ];
      const set = new Set();
      all.forEach(x => {
        const g = (x?.group_title || '').toString().trim();
        if (g) set.add(g);
      });
      const groups = Array.from(set).sort((a,b)=>a.localeCompare(b));
      state.groups = groups;

      if (modalGroupPick){
        modalGroupPick.innerHTML = `<option value="">All</option>` + groups.map(g => `<option value="${esc(g)}">${esc(g)}</option>`).join('');
      }
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      const rows = state.tabs[tabKey].items || [];
      if (!tbody) return;

      hideAllDropdowns();

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        collectGroups();
        return;
      }
      setEmpty(tabKey, false);

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || r.id || '';
        const qTitle = (r.title || '—').toString();
        const hint = (r.hint || '').toString().trim();
        const groupTitle = (r.group_title || '—').toString();
        const status = (r.status || '').toString().trim();
        const sort = (r.sort_order ?? 0);
        const publish = r.publish_at ? prettyDate(r.publish_at) : '—';
        const expire  = r.expire_at ? prettyDate(r.expire_at) : '—';
        const updated = prettyDate(r.updated_at || r.created_at || '');
        const deleted = prettyDate(r.deleted_at || '');

        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm dd-toggle"
              data-dd="1"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">`;

        actions += `<li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (canEdit && tabKey !== 'trash'){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
          if (tabKey === 'active'){
            actions += `<li><button type="button" class="dropdown-item" data-action="mark_inactive"><i class="fa fa-circle-pause"></i> Mark Inactive</button></li>`;
          } else if (tabKey === 'inactive'){
            actions += `<li><button type="button" class="dropdown-item" data-action="mark_active"><i class="fa fa-circle-check"></i> Mark Active</button></li>`;
          }
        }

        if (tabKey !== 'trash'){
          if (canDelete){
            actions += `<li><hr class="dropdown-divider"></li>
              <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
          }
        } else {
          actions += `<li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
          if (canDelete){
            actions += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
          }
        }

        actions += `</ul></div>`;

        if (tabKey === 'trash'){
          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="col-title fw-semibold">
                ${esc(qTitle)}
                ${hint ? `<span class="sub"><i class="fa-regular fa-lightbulb me-1"></i>${esc(hint)}</span>` : `<span class="sub">—</span>`}
              </td>
              <td class="col-group">${groupPill(groupTitle)}</td>
              <td>${esc(deleted)}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td class="col-title fw-semibold">
              ${esc(qTitle)}
              ${hint ? `<span class="sub"><i class="fa-regular fa-lightbulb me-1"></i>${esc(hint)}</span>` : `<span class="sub">—</span>`}
            </td>
            <td class="col-group">${groupPill(groupTitle)}</td>
            <td>${statusBadge(status)}</td>
            <td>${esc(String(sort ?? 0))}</td>
            <td>${esc(String(publish))}</td>
            <td>${esc(String(expire))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
      collectGroups();
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 4 : 8;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const url = buildUrl(tabKey);
        const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const total = p.total ?? null;
        const infoTxt = total !== null ? `${total} result(s)` : '—';
        if (tabKey === 'active' && infoActive) infoActive.textContent = infoTxt;
        if (tabKey === 'inactive' && infoInactive) infoInactive.textContent = infoTxt;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = infoTxt;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // ---------- pager ----------
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

    // ---------- filters ----------
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalStatus) modalStatus.value = state.filters.status || '';
      if (modalSort) modalSort.value = state.filters.sort || '-updated_at';

      if (modalGroupPick){
        modalGroupPick.value = state.groups.includes(state.filters.group_title) ? state.filters.group_title : '';
      }
      if (modalGroupText) modalGroupText.value = state.filters.group_title || '';
    });

    modalGroupPick?.addEventListener('change', () => {
      const v = (modalGroupPick.value || '').trim();
      if (modalGroupText) modalGroupText.value = v;
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = modalStatus?.value || '';
      state.filters.sort = modalSort?.value || '-updated_at';
      state.filters.group_title = (modalGroupText?.value || '').trim();

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', group_title:'', sort:'-updated_at' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalGroupPick) modalGroupPick.value = '';
      if (modalGroupText) modalGroupText.value = '';
      if (modalSort) modalSort.value = '-updated_at';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- RTE ----------
    const rte = {
      wrap: $('descWrap'),
      toolbar: document.querySelector('#descWrap .rte-toolbar'),
      editor: $('descEditor'),
      code: $('descCode'),
      hidden: $('description'),
      mode: 'text',
      enabled: true
    };

    function ensurePreHasCode(html){
      return (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner) => {
        if (/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
        return `<pre><code>${inner}</code></pre>`;
      });
    }

    function rteFocus(){
      try { rte.editor?.focus({ preventScroll:true }); }
      catch(_) { try { rte.editor?.focus(); } catch(__){} }
    }

    function placeCaretAtMarker(marker){
      const sel = window.getSelection();
      if (!sel || !marker) return;
      const range = document.createRange();
      range.setStartAfter(marker);
      range.collapse(true);
      sel.removeAllRanges();
      sel.addRange(range);
      marker.remove();
    }

    function insertHtmlWithCaret(html){
      rteFocus();
      const markerId = 'rte_caret_' + Math.random().toString(16).slice(2);
      document.execCommand('insertHTML', false, html + `<span id="${markerId}">\u200b</span>`);
      const marker = document.getElementById(markerId);
      if (marker) placeCaretAtMarker(marker);
    }

    function syncRteToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('mode-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === rte.mode);
      });

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
        setTimeout(()=>{ try{ rte.code?.focus(); }catch(_){ } }, 0);
      } else {
        rte.editor.innerHTML = ensurePreHasCode(rte.code.value || '');
        setTimeout(()=>{ rteFocus(); }, 0);
      }
    }

    rte.toolbar?.addEventListener('pointerdown', (e) => { e.preventDefault(); });
    rte.editor?.addEventListener('input', () => { syncRteToCode(); });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest('#descWrap .rte-tabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#descWrap .rte-toolbar .rte-btn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, `<${block}>`); }catch(_){}
        syncRteToCode();
        return;
      }

      if (insert === 'code'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<code>${esc(sel.toString())}</code>`);
        } else {
          insertHtmlWithCaret('<code></code>');
        }
        syncRteToCode();
        return;
      }

      if (insert === 'pre'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<pre><code>${esc(sel.toString())}</code></pre>`);
        } else {
          insertHtmlWithCaret('<pre><code></code></pre>');
        }
        syncRteToCode();
        return;
      }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncRteToCode();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      if (rte.editor) rte.editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });
      rte.wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.style.pointerEvents = on ? '' : 'none';
        t.style.opacity = on ? '' : '0.7';
      });
    }

    // ---------- metadata ----------
    btnFormatMeta?.addEventListener('click', () => {
      const raw = (metaText?.value || '').trim();
      if (!raw) return;
      const parsed = safeJsonParse(raw);
      if (!parsed.ok){ err('Metadata JSON is invalid'); return; }
      metaText.value = JSON.stringify(parsed.value, null, 2);
      ok('Formatted');
    });

    // ✅ group title pick listeners
    groupTitlePick?.addEventListener('change', () => {
      syncGroupTitleUI();
      if (groupTitlePick.value === '__other__'){
        setTimeout(()=>{ try{ groupTitleInput?.focus(); }catch(_){ } }, 0);
      }
    });

    groupTitleInput?.addEventListener('input', debounce(() => {
      if (!groupTitlePick || groupTitlePick.value !== '__other__') return;
      const cur = normalizeGroupTitle(groupTitleInput.value);
      const dup = cur ? isDuplicateGroupTitle(cur) : false;
      if (groupTitleDupMsg) groupTitleDupMsg.style.display = dup ? '' : 'none';
    }, 120));

    // ---------- modal helpers ----------
    let saving = false;

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';

      // reset group title ui
      if (groupTitleInput) groupTitleInput.value = '';
      rebuildGroupTitlePick(''); // will call syncGroupTitleUI
      if (groupTitleOtherWrap) groupTitleOtherWrap.style.display = 'none';
      if (groupTitleDupMsg) groupTitleDupMsg.style.display = 'none';
      if (groupTitleInput) groupTitleInput.required = false;

      if (rte.editor) rte.editor.innerHTML = '';
      if (rte.code) rte.code.value = '';
      if (rte.hidden) rte.hidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      if (metaText) metaText.value = '';

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      if (itemForm){
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'create';
      }
    }

    function fillFormFromRow(r, viewOnly=false){
      itemUuid.value = r.uuid || r.id || '';
      itemId.value = r.id || '';

      // ✅ group title set
      const gt = normalizeGroupTitle(r.group_title || '');
      if (groupTitleInput) groupTitleInput.value = gt;

      // pick if exists, else Other
      if (groupTitlePick){
        if (gt && (state.groupTitlesSaved || []).includes(gt)){
          rebuildGroupTitlePick(gt);
        } else if (gt){
          rebuildGroupTitlePick('__other__');
          if (groupTitleOtherWrap) groupTitleOtherWrap.style.display = '';
          if (groupTitleInput) groupTitleInput.required = true;
        } else {
          rebuildGroupTitlePick('');
        }
        syncGroupTitleUI();
      }

      if (titleInput) titleInput.value = (r.title || '').toString();
      if (hintInput) hintInput.value = (r.hint || '').toString();

      if (statusSel) statusSel.value = ((r.status || 'active').toString().toLowerCase().trim() === 'inactive') ? 'inactive' : 'active';
      if (sortOrderInput) sortOrderInput.value = (r.sort_order ?? 0).toString();

      const pub = (r.publish_at ?? '')?.toString?.() || '';
      if (publishAtInput) publishAtInput.value = pub ? pub.replace(' ', 'T').slice(0,16) : '';

      const exp = (r.expire_at ?? '')?.toString?.() || '';
      if (expireAtInput) expireAtInput.value = exp ? exp.replace(' ', 'T').slice(0,16) : '';

      const descHtml = (r.description ?? '') || '';
      if (rte.editor) rte.editor.innerHTML = ensurePreHasCode(descHtml);
      syncRteToCode();
      setRteMode('text');

      // metadata
      let m = r.metadata ?? null;
      if (typeof m === 'string') {
        const parsed = safeJsonParse(m);
        if (parsed.ok) m = parsed.value;
      }
      if (m && typeof m === 'object'){
        metaText.value = JSON.stringify(m, null, 2);
      } else {
        metaText.value = '';
      }

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'itemUuid' || el.id === 'itemId') return;
          if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        setRteEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        setRteEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    function findRowByUuid(uuid){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => String(x?.uuid || x?.id) === String(uuid)) || null;
    }

    btnAddItem?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Question';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    async function toggleActive(uuid, makeActive){
      const fd = new FormData();
      fd.append('_method', 'PATCH');
      fd.append('status', makeActive ? 'active' : 'inactive');

      const v = makeActive ? '1' : '0';
      fd.append('active', v);
      fd.append('is_active', v);
      fd.append('isActive', v);

      showLoading(true);
      try{
        const res = await fetchWithTimeout(API.toggle(uuid), {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 15000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');

        ok(makeActive ? 'Marked active' : 'Marked inactive');
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    // =========================
    // ✅ Dropdown click handler (manual)
    // =========================
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('button.dd-toggle[data-dd="1"]');
      if (!toggle) return;
      if (e.target.closest('button[data-action]')) return;

      e.preventDefault();
      e.stopPropagation();

      hideAllDropdowns(toggle);
      const inst = getDropdownInstance(toggle);
      inst.toggle();
    });

    // ---------- row actions ----------
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
      if (toggle) { try { getDropdownInstance(toggle).hide(); } catch (_) {} }

      const row = findRowByUuid(uuid);

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;
        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Question' : 'Edit Question';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'mark_inactive'){
        if (!canEdit) return;
        const conf = await Swal.fire({
          title: 'Mark this question inactive?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Mark Inactive'
        });
        if (!conf.isConfirmed) return;
        await toggleActive(uuid, false);
        return;
      }

      if (act === 'mark_active'){
        if (!canEdit) return;
        const conf = await Swal.fire({
          title: 'Mark this question active?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Mark Active'
        });
        if (!conf.isConfirmed) return;
        await toggleActive(uuid, true);
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Delete this question?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.remove(uuid), {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.restore(uuid), {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'force'){
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
          const res = await fetchWithTimeout(API.force(uuid), {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // ---------- submit (create/edit) ----------
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (itemForm.dataset.mode === 'view') return;

        const intent = itemForm.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!itemUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        // ✅ resolve group title from dropdown/other
        let groupTitle = '';
        const pick = (groupTitlePick?.value || '').trim();

        if (pick && pick !== '__other__'){
          groupTitle = normalizeGroupTitle(pick);
          if (groupTitleInput) groupTitleInput.value = groupTitle; // keep compatibility
        } else {
          groupTitle = normalizeGroupTitle(groupTitleInput?.value || '');
          if (pick === '__other__'){
            if (!groupTitle){ err('Group title is required'); groupTitleInput?.focus(); return; }

            // ✅ frontend uniqueness: prevent adding same title again
            if (isDuplicateGroupTitle(groupTitle)){
              if (groupTitleDupMsg) groupTitleDupMsg.style.display = '';
              err('This group title already exists. Please select it from the dropdown.');
              groupTitleInput?.focus();
              return;
            }
          } else {
            // pick empty
            err('Group title is required');
            groupTitlePick?.focus();
            return;
          }
        }

        const title = (titleInput?.value || '').trim();
        const hint = (hintInput?.value || '').trim();
        const statusUi = (statusSel?.value || 'active').trim().toLowerCase();
        const sortOrder = (sortOrderInput?.value || '').trim();
        const pub = (publishAtInput?.value || '').trim();
        const exp = (expireAtInput?.value || '').trim();

        const rawDesc = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const cleanDesc = ensurePreHasCode(rawDesc).trim();
        if (rte.hidden) rte.hidden.value = cleanDesc;

        if (!groupTitle){ err('Group title is required'); groupTitlePick?.focus(); return; }
        if (!title){ err('Question title is required'); titleInput.focus(); return; }

        // metadata
        let metaToSend = null;
        const metaRaw = (metaText?.value || '').trim();
        if (metaRaw){
          const parsed = safeJsonParse(metaRaw);
          if (!parsed.ok){ err('Metadata must be valid JSON'); metaText.focus(); return; }
          metaToSend = parsed.value;
        }

        const fd = new FormData();
        fd.append('group_title', groupTitle);
        fd.append('title', title);
        if (hint) fd.append('hint', hint);

        if (sortOrder) fd.append('sort_order', sortOrder);
        if (pub) fd.append('publish_at', pub.replace('T',' ')+':00');
        if (exp) fd.append('expire_at', exp.replace('T',' ')+':00');
        if (cleanDesc) fd.append('description', cleanDesc);

        fd.append('status', statusUi);

        const activeVal = (statusUi === 'inactive') ? '0' : '1';
        fd.append('active', activeVal);
        fd.append('is_active', activeVal);
        fd.append('isActive', activeVal);

        if (metaToSend !== null) fd.append('metadata', JSON.stringify(metaToSend));

        const url = isEdit ? API.update(itemUuid.value) : API.create();
        if (isEdit) fd.append('_method', 'PATCH');

        setBtnLoading(saveBtn, true);
        showLoading(true);

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

        // ✅ keep dropdown list fresh (frontend add + also refetch for certainty)
        if (!isDuplicateGroupTitle(groupTitle)){
          const set = new Set(state.groupTitlesSaved.map(x => normalizeGroupTitle(x)));
          set.add(groupTitle);
          state.groupTitlesSaved = Array.from(set).sort((a,b)=>a.localeCompare(b));
          rebuildGroupTitlePick(groupTitle);
        }
        await fetchGroupTitles();

        ok(isEdit ? 'Updated' : 'Created');
        itemModal && itemModal.hide();

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

    // ---------- init ----------
    (async () => {
      showLoading(true);
      try{
        bindGlobalDropdownClosers();
        await fetchMe();
        await fetchGroupTitles(); // ✅ load saved group titles for dropdown
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        rebuildGroupTitlePick(''); // ensure UI consistent on first open
      }catch(ex){
        err(ex?.message || 'Initialization failed');
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>
@endpush
