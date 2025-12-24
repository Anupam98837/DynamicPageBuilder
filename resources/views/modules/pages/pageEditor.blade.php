@php
  $editId = request()->query('id');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder Editor</title>
     <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      
     * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-sans);
  background: var(--bg-body);
  color: var(--text-color);
  line-height: 1.5;
}

/* FORM CONTAINER */
.form-container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 24px;
}

/* PAGE META FORM */
#pageMetaForm {
  background: var(--surface);
  border-radius: 12px;
  box-shadow: var(--shadow-2);
  overflow: hidden;
  border: 1px solid var(--line-soft);
}

/* META ROW */
.meta-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr) repeat(3, auto);
  gap: 20px;
  padding: 24px;
  border-bottom: 1px solid var(--line-soft);
  background: var(--surface-2);
  align-items: start;
  
}

.meta-field {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.meta-field.title {
  grid-column: 1 / 3;
}
.meta-field.description{
  grid-column: 1 / -1;  /* full width across all grid columns */
  grid-row: 2;
}


.meta-field:nth-child(3) {
  grid-column: 3;
  grid-row: 1;
}

.meta-field:nth-child(4) {
  grid-column: 4;
  grid-row: 1;
}

.meta-field.toggle {
  grid-column: 5;
  grid-row: 1;
  justify-self: end;
}

/* FORM ELEMENTS */
label {
  font-size: var(--fs-13);
  font-weight: 600;
  color: var(--muted-color);
  margin-bottom: 6px;
  display: block;
}

input[type="text"],
select,
textarea {
  padding: 10px 12px;
  border: 1px solid var(--line-medium);
  border-radius: 6px;
  font-size: var(--fs-14);
  background: var(--surface);
  transition: var(--transition);
  width: 100%;
  font-family: inherit;
}

input[type="text"]:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: var(--accent-color);
  box-shadow: var(--ring);
}

textarea {
  min-height: 80px;
  resize: vertical;
}

/* TOGGLE */
.toggle-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 8px;
}

input[type="checkbox"] {
  width: 36px;
  height: 20px;
  appearance: none;
  background: var(--line-medium);
  border-radius: 10px;
  position: relative;
  cursor: pointer;
  transition: var(--transition);
}

input[type="checkbox"]:checked {
  background: var(--accent-color);
}

input[type="checkbox"]::before {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  background: var(--surface);
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: transform 0.2s;
}

input[type="checkbox"]:checked::before {
  transform: translateX(16px);
}

.toggle-wrap span {
  font-size: var(--fs-13);
  color: var(--muted-color);
}

/* TABS */
.ce-tabs {
  display: flex;
  background: var(--surface);
  border-bottom: 1px solid var(--line-soft);
  padding: 0 24px;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.ce-tab-btn {
  padding: 12px 20px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: var(--fs-14);
  color: var(--muted-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition);
  margin-bottom: -1px;
}

.ce-tab-btn:hover {
  color: var(--accent-color);
}

.ce-tab-btn.ce-active {
  color: var(--accent-color);
  border-bottom-color: var(--accent-color);
  font-weight: 500;
}

.ce-tab-btn.ce-action {
  margin-left: auto;
  background: var(--accent-color);
  color: var(--surface);
  border-radius: var(--radius-1);
  padding: 10px 20px;
  border: none;
  margin-top: 8px;
  margin-bottom: 8px;
}

.ce-tab-btn.ce-action:hover {
  background: var(--secondary-color);
}

.ce-tab-btn#ceSaveDB {
  background: var(--success-color);
}

.ce-tab-btn#ceSaveDB:hover {
  background: #059669;
}

/* TAB PANES */
.ce-tab-pane {
  display: none;
  min-height: 700px;
}

.ce-tab-pane.ce-active {
  display: block;
}

/* EDITOR LAYOUT */
.ce-editor {
  display: flex;
  min-height: 700px;
  background: var(--surface-2);
}

/* INSPECTOR */
.ce-inspector {
  width: 280px;
  background: var(--surface);
  border-right: 1px solid var(--line-soft);
  display: flex;
  flex-direction: column;
}

.ce-panel-header {
  padding: 16px 20px;
  font-weight: 600;
  color: var(--text-color);
  border-bottom: 1px solid var(--line-soft);
  background: var(--surface-2);
}

.ce-inspector-actions {
  padding: 12px 20px;
  border-bottom: 1px solid var(--line-soft);
  display: flex;
  gap: 8px;
  background: var(--surface);
}

/* BUTTONS */
.ce-btn-sm {
  padding: 8px 12px;
  background: var(--surface-3);
  border: 1px solid var(--line-medium);
  border-radius: 4px;
  font-size: var(--fs-13);
  cursor: pointer;
  color: var(--muted-color);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: var(--transition);
}

.ce-btn-sm:hover {
  background: var(--line-soft);
  border-color: var(--line-strong);
}

.ce-primary {
  background: var(--accent-color);
  color: var(--surface);
  border-color: var(--accent-color);
}

.ce-primary:hover {
  background: var(--secondary-color);
}

.ce-inspector-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.ce-muted {
  color: var(--muted-color);
  font-style: italic;
  text-align: center;
  padding: 20px;
}

/* CANVAS AREA */
.ce-canvas-wrap {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
  background: var(--surface-2);
}

.ce-device-preview {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  background: var(--surface);
  padding: 8px;
  border-radius: 8px;
  border: 1px solid var(--line-soft);
  width: fit-content;
}

.ce-device-btn {
  padding: 8px 16px;
  background: var(--surface-3);
  border: 1px solid var(--line-medium);
  border-radius: 4px;
  font-size: var(--fs-13);
  cursor: pointer;
  color: var(--muted-color);
  display: flex;
  align-items: center;
  gap: 8px;
}

.ce-device-btn.active {
  background: var(--accent-color);
  color: var(--surface);
  border-color: var(--accent-color);
}

.ce-canvas {
  background: var(--surface);
  border-radius: var(--radius-1);
  border: 1px solid var(--line-soft);
  min-height: 600px;
  position: relative;
  margin: 0 auto;
  transition: width 0.3s;
}

.ce-canvas.desktop {
  width: 100%;
  max-width: 1200px;
}

.ce-canvas::before {
  content: attr(data-placeholder);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--muted-color);
  font-size: var(--fs-14);
}

#ceCanvasExport {
  display: none;
}

/* COMPONENTS PANEL */
.ce-components-panel {
  width: 280px;
  background: var(--surface);
  border-left: 1px solid var(--line-soft);
  display: flex;
  flex-direction: column;
}

.ce-comp-tabs {
  display: flex;
  border-bottom: 1px solid var(--line-soft);
  padding: 0 20px;
}

.ce-comp-tab-btn {
  padding: 12px 16px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: var(--fs-13);
  color: var(--muted-color);
  cursor: pointer;
  font-weight: 500;
}

.ce-comp-tab-btn.ce-active {
  color: var(--accent-color);
  border-bottom-color: var(--accent-color);
}

.ce-components-list {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  display: none;
}

.ce-components-list.ce-active {
  display: block;
}

.ce-component {
  background: var(--surface);
  border: 1px solid var(--line-soft);
  border-radius: 6px;
  padding: 12px 16px;
  margin-bottom: 10px;
  cursor: move;
  font-size: var(--fs-14);
  color: var(--text-color);
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 10px;
}

.ce-component:hover {
  border-color: var(--accent-color);
  background: var(--primary-light);
  transform: translateY(-2px);
  box-shadow: var(--shadow-1);
}

.ce-component i {
  color: var(--muted-color);
  width: 18px;
}

/* CODE TAB */
.ce-code-wrap {
  display: flex;
  height: 700px;
  padding: 24px;
  gap: 24px;
}

.ce-code-left,
.ce-code-right {
  flex: 1;
  display: flex;
  flex-direction: column;
}

#ceCodeArea {
  flex: 1;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: var(--fs-13);
  padding: 16px;
  border: 1px solid var(--line-medium);
  border-radius: 6px;
  resize: none;
  background: var(--surface-2);
  color: var(--text-color);
}

#ceCodeArea:focus {
  outline: none;
  border-color: var(--accent-color);
  box-shadow: var(--ring);
}

.ce-code-actions {
  padding: 16px 24px;
  border-top: 1px solid var(--line-soft);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: var(--surface);
}

/* MEDIA TAB */
#tab-media {
  padding: 24px;
}

/* MODAL */
#ceModal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}

.ce-modal-box {
  background: var(--surface);
  border-radius: 12px;
  padding: 24px;
  width: 90%;
  max-width: 800px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
}

#ceExport {
  flex: 1;
  font-family: monospace;
  padding: 16px;
  border: 1px solid var(--line-medium);
  border-radius: 6px;
  resize: none;
  min-height: 300px;
  font-size: var(--fs-13);
  background: var(--surface-2);
}

/* TEXT EDITOR STYLES */
.ce-text-toolbar {
  margin-bottom: 6px;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5em;
  align-items: center;
}

.ce-text-toolbar button,
.ce-text-toolbar select,
.ce-text-toolbar input[type="color"] {
  margin-right: 4px;
  padding: 2px 6px;
  font-size: var(--fs-14);
  border: 1px solid var(--line-medium);
  background: var(--surface);
  border-radius: 4px;
  cursor: pointer;
}

.ce-text-toolbar .ce-font-controls {
  display: flex;
  gap: 0.25em;
}

.ce-text-toolbar select {
  padding: 0.2em 0.4em;
}

.ce-text-toolbar input[type="color"] {
  width: 1.5em;
  height: 1.5em;
  padding: 0;
  border: none;
  border-radius: 0;
}

.ce-text-area {
  border: 1px solid var(--line-medium);
  padding: 8px;
  min-height: 80px;
  margin-top: 0.5em;
  border-radius: 4px;
  outline: none;
}

/* BLOCK STYLES */
#ceCanvasEdit .ce-block {
  position: relative;
}

#ceCanvasEdit .ce-block:hover::after,
#ceCanvasEdit .ce-block.ce-selected::after {
  content: "";
  position: absolute;
  inset: 0;
  outline: 1px dotted var(--accent-color);
  pointer-events: none;
  z-index: 2;
}

.ce-block-handle {
  position: absolute;
  right: 6px;
  top: 6px;
  background: var(--accent-color);
  color: var(--surface);
  border-radius: 3px;
  padding: 2px 6px;
  font-size: 11px;
  display: flex;
  gap: 6px;
  align-items: center;
  box-shadow: var(--shadow-1);
  opacity: 0;
  pointer-events: none;
  transition: opacity .12s;
  z-index: 3;
}

#ceCanvasEdit .ce-block:hover .ce-block-handle {
  opacity: 1;
  pointer-events: auto;
}

.ce-block-handle span {
  cursor: pointer;
  line-height: 1;
}

.ce-block-handle .ce-remove {
  color: var(--danger-light);
}

.ce-block-handle .ce-dup {
  color: var(--success-color);
}

.ce-block-handle .ce-up,
.ce-block-handle .ce-down {
  color: var(--surface);
}

.ce-slot {
  min-height: 24px;
}

.ce-add-inside {
  margin-top: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: var(--fs-13);
  color: var(--accent-color);
  cursor: pointer;
  width: 100%;
}

/* ADD POPUP */
.ce-add-popup {
  position: absolute;
  background: var(--surface);
  border: 1px solid var(--line-soft);
  border-radius: 6px;
  box-shadow: var(--shadow-2);
  padding: 12px;
  width: 220px;
  z-index: 9999;
  font-size: var(--fs-13);
}

.ce-add-popup h4 {
  margin: 0 0 8px 0;
  font-size: var(--fs-13);
  color: var(--text-color);
  font-weight: 600;
}

.ce-add-popup button {
  display: block;
  width: 100%;
  text-align: left;
  border: 0;
  background: none;
  padding: 6px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.ce-add-popup button:hover {
  background: var(--surface-3);
  color: var(--accent-color);
}

/* DROP MARKER */
.ce-drop-marker {
  height: 4px;
  background: var(--accent-color);
  margin: 4px 0;
  border-radius: 2px;
  opacity: 0;
  transition: opacity .1s linear;
}

.ce-drop-marker.active {
  opacity: 1;
}

/* RESPONSIVE */
@media (max-width: 1400px) {
  .meta-row {
    grid-template-columns: 1fr 1fr auto auto;
  }
  
  .meta-field.toggle {
    grid-column: 1;
    grid-row: 3;
    justify-self: start;
  }
  
  .meta-field.description {
    grid-column: 2 / 5;
    grid-row: 3;
  }
}

@media (max-width: 1200px) {
  .ce-editor {
    flex-direction: column;
  }
  
  .ce-inspector,
  .ce-components-panel {
    width: 100%;
    border-right: none;
    border-left: none;
    border-bottom: 1px solid var(--line-soft);
  }
  
  .ce-inspector {
    order: 3;
  }
  
  .ce-code-wrap {
    flex-direction: column;
    height: auto;
  }
}

@media (max-width: 768px) {
  .form-container {
    padding: 12px;
  }
  
  .meta-row {
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 20px;
  }
  
  .meta-field.title,
  .meta-field.description,
  .meta-field:nth-child(3),
  .meta-field:nth-child(4),
  .meta-field.toggle {
    grid-column: 1;
    grid-row: auto;
  }
  
  .ce-tabs {
    padding: 0 16px;
    flex-wrap: wrap;
  }
  
  .ce-tab-btn {
    padding: 10px 12px;
    font-size: var(--fs-13);
  }
  
  .ce-tab-btn.ce-action {
    margin-left: 0;
    margin-top: 4px;
    order: 1;
  }
}
/* =========================================================
   FIX: Bootstrap .row inside .meta-row grid
   (Your <div class="row g-3"> was inside a CSS grid cell)
========================================================= */
.meta-row .row.g-3{
  grid-column: 1 / -1;
  margin: 0;
}
.meta-row .row.g-3 > [class*="col-"]{
  padding-left: 0;
  padding-right: 0;
}
.meta-row .row.g-3 .form-control,
.meta-row .row.g-3 .form-select{
  width: 100%;
}

/* Make all "extra" fields span nicely on desktop */
@media (min-width: 992px){
  .meta-row .row.g-3{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  .meta-row .row.g-3 .col-md-6{
    width: auto;
    max-width: none;
  }
}

/* =========================================================
   FORM ACTIONS (Bottom Save Bar)
========================================================= */
.form-actions{
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-top: 1px solid var(--line-soft);
  background: var(--surface);
  position: sticky;
  bottom: 0;
  z-index: 20;
}

/* status text/pill */
.save-status{
  flex: 1;
  min-width: 0;
  font-size: var(--fs-13);
  color: var(--muted-color);
  display: flex;
  align-items: center;
  gap: 10px;
}
.save-status .pill{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid var(--line-soft);
  background: var(--surface-2);
  color: var(--muted-color);
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.save-status.is-success .pill{
  border-color: rgba(16,185,129,.35);
  background: rgba(16,185,129,.10);
  color: var(--success-color);
}
.save-status.is-error .pill{
  border-color: rgba(220,38,38,.35);
  background: rgba(220,38,38,.08);
  color: var(--danger-color);
}

/* Buttons in bottom bar (non-bootstrap) */
.btn-save,
.btn-cancel{
  appearance: none;
  border: 1px solid var(--line-medium);
  background: var(--surface);
  color: var(--text-color);
  border-radius: 10px;
  padding: 10px 14px;
  font-size: var(--fs-14);
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: var(--transition);
  user-select: none;
}
.btn-cancel:hover{
  background: var(--surface-2);
  border-color: var(--line-strong);
}
.btn-save{
  background: var(--accent-color);
  border-color: var(--accent-color);
  color: #fff;
}
.btn-save:hover{
  background: var(--secondary-color);
  border-color: var(--secondary-color);
}
.btn-save:disabled,
.btn-cancel:disabled{
  opacity: .6;
  cursor: not-allowed;
}
.btn-save:focus-visible,
.btn-cancel:focus-visible{
  outline: none;
  box-shadow: var(--ring);
  border-color: var(--accent-color);
}

/* Stack buttons on mobile */
@media (max-width: 640px){
  .form-actions{
    flex-wrap: wrap;
    gap: 10px;
  }
  .btn-save, .btn-cancel{
    width: 100%;
    justify-content: center;
  }
}

/* =========================================================
   CODE TAB polish
========================================================= */
.ce-code-pane{
  background: var(--surface-2);
}
#ceCodePreview{
  background: #fff;
  border: 1px solid var(--line-medium) !important;
  border-radius: 10px !important;
  box-shadow: var(--shadow-1);
}
html.theme-dark #ceCodePreview{
  background: var(--surface);
}

/* Code pane header spacing */
.ce-code-left .ce-panel-header,
.ce-code-right .ce-panel-header{
  color: var(--text-color);
}

/* =========================================================
   CANVAS placeholder behavior
   (hide placeholder when content exists)
========================================================= */
.ce-canvas:not(:empty)::before{
  display: none;
}

/* Canvas inner spacing + nicer default */
#ceCanvasEdit{
  padding: 16px;
}
#ceCanvasEdit .ce-block{
  border: 1px dashed transparent;
  border-radius: 10px;
  padding: 10px 12px;
}
#ceCanvasEdit .ce-block.ce-selected{
  border-color: rgba(99,102,241,.35);
  background: rgba(99,102,241,.06);
}

/* Slot (drop target) visible */
#ceCanvasEdit .ce-slot{
  min-height: 28px;
  border: 1px dashed var(--line-medium);
  border-radius: 10px;
  padding: 10px;
  background: var(--surface);
}

/* =========================================================
   INSPECTOR: common field styles (for your JS-generated UI)
========================================================= */
.ce-inspector-body .ce-prop-group{
  margin-bottom: 14px;
}
.ce-inspector-body .ce-prop-label{
  font-size: var(--fs-13);
  font-weight: 600;
  color: var(--muted-color);
  margin-bottom: 6px;
  display: block;
}
.ce-inspector-body .ce-prop-input,
.ce-inspector-body input[type="text"],
.ce-inspector-body input[type="number"],
.ce-inspector-body select,
.ce-inspector-body textarea{
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--line-medium);
  border-radius: 10px;
  background: var(--surface);
  color: var(--text-color);
  font-size: var(--fs-13);
}
.ce-inspector-body textarea{
  min-height: 90px;
  resize: vertical;
}
.ce-inspector-body .ce-prop-input:focus,
.ce-inspector-body input:focus,
.ce-inspector-body select:focus,
.ce-inspector-body textarea:focus{
  outline: none;
  border-color: var(--accent-color);
  box-shadow: var(--ring);
}

/* =========================================================
   MEDIA TAB container polish
========================================================= */
#tab-media{
  background: var(--surface);
  border-top: 1px solid var(--line-soft);
  padding: 18px;
}
#tab-media .container,
#tab-media .container-fluid{
  max-width: 100%;
}

/* =========================================================
   MODAL polish (your custom modal)
========================================================= */
#ceModal{
  backdrop-filter: blur(2px);
}
.ce-modal-box{
  border: 1px solid var(--line-soft);
  box-shadow: var(--shadow-2);
}
#ceExport{
  color: var(--text-color);
  background: var(--surface-2);
}

/* =========================================================
   META DESCRIPTION – RTE
========================================================= */

.ce-rte {
  min-height: 120px;
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid var(--line-medium);
  background: var(--surface);
  font-size: var(--fs-14);
  font-family: var(--font-sans);
  line-height: 1.6;
  color: var(--text-color);
  overflow-y: auto;
}

/* Focus */
.ce-rte:focus {
  outline: none;
  border-color: var(--accent-color);
  box-shadow: var(--ring);
}

/* Placeholder */
.ce-rte:empty::before {
  content: attr(data-placeholder);
  color: var(--muted-color);
  pointer-events: none;
}

/* RTE content rules */
.ce-rte p { margin: 0 0 6px 0; }
.ce-rte strong { font-weight: 600; }
.ce-rte em { font-style: italic; }
.ce-rte a { color: var(--accent-color); text-decoration: underline; }
.ce-rte ul, .ce-rte ol { padding-left: 18px; margin: 6px 0; }

/* Restrict heavy content */
.ce-rte img,
.ce-rte video,
.ce-rte iframe {
  display: none !important;
}

/* SEO safety: warn when too long */
.ce-rte[data-overlimit="true"] {
  border-color: var(--danger-color);
  box-shadow: 0 0 0 2px rgba(220,38,38,.15);
}
/* =========================================================
   INSPECTOR PROPERTY TABS (ce-prop-tab-btn)
========================================================= */

.ce-prop-tabs{
  display:flex;
  align-items:center;
  gap: 4px;
  padding: 10px 12px;
  border-bottom: 1px solid var(--line-soft);
  background: var(--surface);
  position: sticky;
  top: 0;
  z-index: 5;
}

.ce-prop-tab-btn{
  appearance:none;
  border: 0;
  background: transparent;
  color: var(--muted-color);
  padding: 10px 12px;
  font-size: var(--fs-13);
  font-weight: 600;
  cursor: pointer;
  border-bottom: 2px solid transparent; /* underline style */
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: -1px; /* sit on the border-bottom */
  user-select: none;
}

.ce-prop-tab-btn:hover{
  color: var(--accent-color);
}

/* ACTIVE */
.ce-prop-tab-btn.active,
.ce-prop-tab-btn.ce-active{
  color: var(--accent-color);
  border-bottom-color: var(--accent-color);
}

/* Optional: focus ring */
.ce-prop-tab-btn:focus-visible{
  outline: none;
  box-shadow: var(--ring);
  border-radius: 8px;
}

/* Optional: icon sizing inside button */
.ce-prop-tab-btn i{
  font-size: 14px;
  width: 16px;
  text-align: center;
}
/* =========================================================
   UNIT TOGGLE (e.g., px / % / rem)
========================================================= */
.ce-unit-toggle{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px;
  background: var(--surface-2);
  border: 1px solid var(--line-soft);
  border-radius: 10px;
}

.ce-unit-toggle .ce-typo-btn,
.ce-unit-toggle button{
  appearance: none;
  border: 1px solid transparent;
  background: transparent;
  color: var(--muted-color);
  padding: 6px 10px;
  font-size: var(--fs-13);
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: var(--transition);
  line-height: 1;
}

.ce-unit-toggle .ce-typo-btn:hover,
.ce-unit-toggle button:hover{
  color: var(--accent-color);
  background: var(--surface);
  border-color: var(--line-soft);
}

/* active state (supports both .active and .ce-active) */
.ce-unit-toggle .active,
.ce-unit-toggle .ce-active{
  background: var(--accent-color);
  color: #fff;
  border-color: var(--accent-color);
  box-shadow: 0 6px 16px rgba(0,0,0,.10);
}

/* =========================================================
   TYPOGRAPHY TOOLS BAR (bold/italic/underline, align, etc.)
========================================================= */
.ce-typo-tools{
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  background: var(--surface);
  border: 1px solid var(--line-soft);
  border-radius: 12px;
  box-shadow: var(--shadow-1);
  margin-bottom: 12px;
}

.ce-typo-tools .ce-sep{
  width: 1px;
  height: 22px;
  background: var(--line-soft);
  margin: 0 2px;
}

/* =========================================================
   TYPO BUTTON
========================================================= */
.ce-typo-btn{
  appearance: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  padding: 8px 10px;
  min-height: 34px;
  min-width: 34px;

  border: 1px solid var(--line-medium);
  background: var(--surface);
  color: var(--text-color);

  border-radius: 10px;
  cursor: pointer;
  transition: var(--transition);
  user-select: none;
}

.ce-typo-btn i{ font-size: 14px; }

.ce-typo-btn:hover{
  border-color: var(--accent-color);
  background: var(--surface-2);
  color: var(--accent-color);
}

/* Active toggle state */
.ce-typo-btn.active,
.ce-typo-btn.ce-active{
  background: rgba(99,102,241,.12); /* safe fallback look */
  border-color: rgba(99,102,241,.35);
  color: var(--accent-color);
}

/* If you want active to look "solid" */
.ce-typo-btn.active.is-solid,
.ce-typo-btn.ce-active.is-solid{
  background: var(--accent-color);
  border-color: var(--accent-color);
  color: #fff;
}

/* Disabled */
.ce-typo-btn:disabled{
  opacity: .55;
  cursor: not-allowed;
}

/* Focus ring */
.ce-typo-btn:focus-visible{
  outline: none;
  box-shadow: var(--ring);
  border-color: var(--accent-color);
}
/* =========================================================
   ALIGN BUTTONS (ce-align-btn)
========================================================= */
.ce-align-btn{
  appearance: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  padding: 8px 10px;
  min-height: 34px;
  min-width: 34px;

  border: 1px solid var(--line-medium);
  background: var(--surface);
  color: var(--text-color);

  border-radius: 10px;
  cursor: pointer;
  transition: var(--transition);
  user-select: none;
}

.ce-align-btn i{ font-size: 14px; }

.ce-align-btn:hover{
  border-color: var(--accent-color);
  background: var(--surface-2);
  color: var(--accent-color);
}

/* Active state */
.ce-align-btn.active,
.ce-align-btn.ce-active{
  background: rgba(99,102,241,.12);
  border-color: rgba(99,102,241,.35);
  color: var(--accent-color);
}

/* Optional: focus ring */
.ce-align-btn:focus-visible{
  outline: none;
  box-shadow: var(--ring);
  border-color: var(--accent-color);
}

/* Optional: disabled */
.ce-align-btn:disabled{
  opacity: .55;
  cursor: not-allowed;
}

.pbx-btn{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 6px;
  text-decoration: none;
  margin-bottom: 8px;
  font-weight: 800;
  font-size: 13px;
  color: #fff;
  background: var(--accent-color);
  border: 1px solid var(--accent-color);
  border-radius: 12px;
  transition: var(--transition);
  box-shadow: 0 10px 22px color-mix(in srgb, var(--accent-color) 22%, transparent);
}
.pbx-btn:hover{
  background: var(--secondary-color);
  border-color: var(--secondary-color);
  transform: translateY(-1px);
}
.pbx-btn:focus-visible{
  outline: none;
  box-shadow: var(--ring);
}
.pbx-header{
  top: 0;
  z-index: 90;
  background: var(--surface);
  border-bottom: 1px solid var(--line-soft);
  box-shadow: var(--shadow-1);
}

.pbx-inner{
  max-width: 1600px;
  margin: 0 auto;
  padding: 12px 24px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 14px;
}

/* Brand (left) */
.pbx-brand{
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--text-color);
  min-width: 280px;
}



/* ✅ Logo image */
.pbx-logo{
  height: 45px;
  width: auto;
  display: block;
  position: relative;
  z-index: 1;
  /* Optional polish (still minimal) */
  filter: drop-shadow(0 8px 18px color-mix(in srgb, var(--accent-color) 14%, transparent));
}

/* Brand text */
.pbx-brandText{
  display: flex;
  flex-direction: column;
  line-height: 1.15;
  min-width: 0;
}
.pbx-brandName{
  font-weight: 900;
  font-size: 14px;
  letter-spacing: .2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 320px;
}
.pbx-brandSub{
  margin-top: 2px;
  font-weight: 700;
  font-size: 12px;
  color: color-mix(in srgb, var(--text-color) 62%, transparent);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 320px;
}

/* Center: make it vertical so subtitle sits below title */
.pbx-center{
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-width: 0;
}

/* Subtitle under title (slightly more centered-friendly) */
.pbx-centerSub{
  margin: 0;                 /* remove default p margin */
  text-align: center;
  max-width: 520px;          /* wider than brand area */
}

/* Mobile: align left like your existing responsive behavior */
@media (max-width: 992px){
  .pbx-center{
    align-items: flex-start;
  }
  .pbx-centerSub{
    text-align: left;
  }
}


.pbx-title{
  display: inline-flex;
  align-items: center;
  gap: 10px;

  font-weight: 900;
  font-size: 14px;
  letter-spacing: .2px;
  color: var(--text-color);

  padding: 8px 14px;
  border-radius: 999px;

  border: 1px solid color-mix(in srgb, var(--accent-color) 18%, var(--line-soft));
  background: linear-gradient(
    135deg,
    color-mix(in srgb, var(--primary-color) 9%, transparent),
    color-mix(in srgb, var(--accent-color) 10%, transparent)
  );
  /* box-shadow: 0 10px 22px color-mix(in srgb, var(--accent-color) 10%, transparent); */

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* Right side */
.pbx-actions{
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  min-width: 280px;
}

/* Accent line */
.pbx-accent{
  height: 3px;
  width: 100%;
  background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--secondary-color));
}

/* Hover polish */
.pbx-brand:hover .pbx-logoWrap{
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 992px){
  .pbx-inner{
    grid-template-columns: 1fr auto;
    grid-template-areas:
      "brand actions"
      "center center";
    gap: 10px;
  }
  .pbx-brand{ grid-area: brand; min-width: 0; }
  .pbx-actions{ grid-area: actions; min-width: 0; }
  .pbx-center{ grid-area: center; justify-content: flex-start; }
  .pbx-brandName, .pbx-brandSub{ max-width: 220px; }
}

@media (max-width: 520px){
  .pbx-inner{ padding: 10px 14px; }
  .pbx-brandSub{ display:none; }
  .pbx-logoWrap{ width: 42px; height: 42px; border-radius: 12px; }
  .pbx-title{ padding: 7px 12px; }
}

    </style>
</head>
<body>
  <!-- pageBuilderHeader.html (pure HTML + CSS) -->
<header class="pbx-header" role="banner" aria-label="Page builder header">
  <div class="pbx-inner">

    <!-- Brand (left) -->
    <a class="pbx-brand" href="/" aria-label="Company Home">
      <span class="pbx-logoWrap" aria-hidden="true">
        <!-- ✅ Your logo image here -->
        <img class="pbx-logo" src="/assets/media/images/web/logo.png" alt="Company logo">
      </span>

      
    </a>

    <!-- Center (you can keep title OR replace with another logo) -->
    <!-- Center -->
<div class="pbx-center">
  <div class="pbx-title">Page Builder</div>
  <p class="pbx-brandSub pbx-centerSub">Create your Page Here</p>
</div>


    <!-- Right side (put buttons here later) -->
    <div class="pbx-actions">
      <!-- Example:
      <button class="pbx-actionBtn">Save</button>
      -->
    </div>

  </div>
  <div class="pbx-accent" aria-hidden="true"></div>
</header>

    <div class="form-container">
       <a class="pbx-btn" href="/pages/manage" title="Back to pages">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back</span>
        </a>
<form
  id="pageMetaForm"
  class="page-meta-form"
  method="POST"
  action="#"
   @if($editId)
    data-page-id="{{ $editId }}"
  @endif>
            <!-- Meta Fields Section -->
            <div class="meta-row">
                <div class="meta-field title">
                    <label for="pageTitle">Page Title</label>
                    <input type="text" id="pageTitle" name="title" placeholder="Enter page title" required>
                </div>

               <div class="meta-field description">
  <label for="metaDescription">Meta Description</label>

  <!-- RTE UI -->
  <div
    id="metaDescriptionEditor"
    class="ce-rte"
    contenteditable="true"
    data-placeholder="Enter meta description for SEO"
  ></div>

  <!-- Actual form value -->
  <textarea
    id="metaDescription"
    name="meta_description"
    hidden
  ></textarea>
</div>
<div class="row g-3">

  {{-- Includable ID --}}
  <div class="col-md-6">
    <label for="includable_id" class="form-label">
      Includable ID
      <span class="text-muted small">(optional • must be unique)</span>
    </label>
    <input
      type="text"
      name="includable_id"
      id="includable_id"
      class="form-control"
      placeholder="e.g. footer_links, dept_sidebar"
      maxlength="120"
      value="{{ old('includable_id', $page->includable_id ?? '') }}"
    >
    <small class="text-muted">
      Used when this page is included as a reusable partial.
    </small>
  </div>

  {{-- Layout Key --}}
  <div class="col-md-6">
    <label for="layout_key" class="form-label">
      Layout Key
      <span class="text-muted small">(optional)</span>
    </label>
    <input
      type="text"
      name="layout_key"
      id="layout_key"
      class="form-control"
      placeholder="e.g. default, fullwidth, landing"
      maxlength="100"
      value="{{ old('layout_key', $page->layout_key ?? '') }}"
    >
    <small class="text-muted">
      Controls which layout template is used to render the page.
    </small>
  </div>

</div>
<div class="meta-field">
  <label for="pageType">Page Type</label>
  <select id="pageType" name="page_type" class="form-select" required>
    <option value="page" selected>Page</option>
    <option value="fragment">Fragment</option>
    <option value="redirect">Redirect</option>
  </select>
</div>

                <div class="meta-field">
                    <label for="pageStatus">Status</label>
                    <select id="pageStatus" name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>

                <div class="meta-field">
                    <label for="departmentId">Department</label>
                    <select id="departmentId" name="department_id">
                        <option value="">Select department</option>
                        <!-- Options would be populated dynamically -->
                    </select>
                </div>

                <div class="meta-field toggle">
                    <label>&nbsp;</label>
                    <div class="toggle-wrap">
                        <input type="checkbox" id="submenuExists" name="submenu_exists">
                        <span>Has submenu</span>
                    </div>
                </div>
            </div>
           <div style="margin-bottom:10px;margin-top:10px; text-align:center;">
  <label style="font-weight:600;color:#111827;font-size:18px; ">
    Content HTML
  </label>
  <div style="font-size:12px;color:#6b7280;">
    Build the main page content below
  </div>
</div>

            <!-- Tabs Navigation -->
            <div class="ce-tabs" id="topTabs">
                <button type="button" class="ce-tab-btn ce-active" data-tab="editor">
                    <i class="fa-solid fa-pen-ruler"></i> Editor
                </button>
                <button type="button" class="ce-tab-btn" data-tab="code">
                    <i class="fa-solid fa-code"></i> Code
                </button>
                <button type="button" class="ce-tab-btn" data-tab="media">
                    <i class="fa-solid fa-photo-film"></i> Media
                </button>

                <button type="button" class="ce-tab-btn ce-action" id="ceSave">
                    <i class="fa-solid fa-file-export"></i> Export HTML
                </button>
                
            </div>

            <!-- Editor Tab -->
            <div class="ce-tab-pane ce-active" id="tab-editor">
                <div class="ce-editor">
                    <!-- Inspector Panel -->
                    <aside class="ce-inspector">
                        <div class="ce-panel-header">Properties</div>
                        <div class="ce-inspector-actions">
                            <button type="button" id="ceUndo" class="ce-btn-sm" title="Undo (Ctrl+Z)">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <button type="button" id="ceRedo" class="ce-btn-sm" title="Redo (Ctrl+Shift+Z)">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                        </div>
                        <div class="ce-inspector-body" id="ceInspector">
                            <p class="ce-muted">Select a block to edit its properties.</p>
                        </div>
                    </aside>

                    <!-- Main Canvas Area -->
                    <main class="ce-canvas-wrap">
                        <div class="ce-device-preview">
                            <button type="button" class="ce-device-btn active" data-device="desktop">
                                <i class="fa-solid fa-desktop"></i> Desktop
                            </button>
                            <button type="button" class="ce-device-btn" data-device="tablet">
                                <i class="fa-solid fa-tablet"></i> Tablet
                            </button>
                            <button type="button" class="ce-device-btn" data-device="mobile">
                                <i class="fa-solid fa-mobile"></i> Mobile
                            </button>
                        </div>
                        
                        <div id="ceCanvasEdit" class="ce-canvas desktop" data-placeholder="Drag components here"></div>
                        
                        <!-- Hidden canvases for export -->
                        <div id="ceCanvasExport" class="ce-canvas"></div>
                        <textarea id="ceDraftExport" style="display:none;"></textarea>
                    </main>

                    <!-- Components Panel -->
                    <aside class="ce-components-panel">
                        <div class="ce-panel-header">Components</div>

                        <div class="ce-comp-tabs">
                            <button type="button" class="ce-comp-tab-btn ce-active" data-list="elements">Elements</button>
                            <button type="button" class="ce-comp-tab-btn" data-list="sections">Sections</button>
                        </div>

                        <div class="ce-components-list ce-active" id="list-elements">
                            <div class="ce-component" draggable="true" data-key="ce-personalized-greeting" 
                                 data-html="<p style='margin:0 0 12px 0; line-height:1.5; font-size:1rem;'>Hi %%first_name%%,</p>">
                                <i class="fa-solid fa-hand-wave"></i> Personalized Greeting
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-heading"   
                                 data-html="<h2 style='margin:0 0 12px 0;'>Your Heading</h2>">
                                <i class="fa-solid fa-heading"></i> Heading
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-paragraph" 
                                 data-html="<p style='margin:0 0 12px 0;line-height:1.5;'>Your paragraph text goes here…</p>">
                                <i class="fa-solid fa-font"></i> Paragraph
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-ul"        
                                 data-html="<ul style='margin:0 0 12px 18px;padding:0;'><li>List item 1</li><li>List item 2</li></ul>">
                                <i class="fa-solid fa-list-ul"></i> Unordered List
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-ol"        
                                 data-html="<ol style='margin:0 0 12px 18px;padding:0;'><li>List item 1</li><li>List item 2</li></ol>">
                                <i class="fa-solid fa-list-ol"></i> Ordered List
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-image"     
                                 data-html="<img src='https://placehold.co/600x200' alt='image' style='max-width:100%;height:auto;display:inline-block;margin:0 0 12px 0;'/>">
                                <i class="fa-solid fa-image"></i> Image
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-button"    
                                 data-html="<a href='#' style='display:inline-block;padding:10px 18px;background:#6366f1;color:#fff;border:1px solid #6366f1;border-radius:4px;text-decoration:none;margin:0 0 12px 0;'>Click me</a>">
                                <i class="fa-solid fa-link"></i> Button
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-divider"   
                                 data-html="<hr style='border:0;border-top:2px solid #e5e7eb;margin:16px 0;'/>">
                                <i class="fa-solid fa-minus"></i> Divider
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-html"      
                                 data-html="<div style='margin:0 0 12px 0;'>Custom HTML here</div>">
                                <i class="fa-solid fa-code"></i> Custom HTML
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-social-links" 
                                 data-html="<div class='cs-social-links' style='text-align:center;margin:12px 0;'><a href='https://facebook.com/' target='_blank' style='display:inline-block;margin:0 6px;'><svg width='22' height='22' viewBox='0 0 24 24' fill='#1877F2' xmlns='http://www.w3.org/2000/svg'><path d='M22 12.07C22 6.49 17.52 2 12 2S2 6.49 2 12.07c0 5.01 3.66 9.16 8.44 9.93v-7.03H7.9V12h2.54V9.8c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.97h-2.34V22c4.78-.77 8.44-4.92 8.44-9.93z'/></svg></a></div>">
                                <i class="fab fa-share-alt"></i> Social Links
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-footer" 
                                 data-html="<div style='padding:12px;background:#f3f4f6;font-size:12px;text-align:center;color:#666;'>&copy; 2025 Your Company · <a href='#' style='color:inherit;text-decoration:underline;'>Unsubscribe</a></div>">
                                <i class="fa-solid fa-flag"></i> Footer
                            </div>
                        </div>

                        <div class="ce-components-list" id="list-sections">
                            <div class="ce-component" draggable="true" data-key="ce-section-1" 
                                 data-html="<section style='margin:0 auto;padding:0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;'><div class='ce-section-slot' style='flex:1;min-width:100%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>">
                                <i class="fa-solid fa-square"></i> 1 Column
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-section-2" 
                                 data-html="<section style='margin:0 auto;padding:0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;'><div class='ce-section-slot' style='flex:1;min-width:49%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:49%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>">
                                <i class="fa-solid fa-table-columns"></i> 2 Columns
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-section-3" 
                                 data-html="<section style='margin:0 auto;padding:0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;'><div class='ce-section-slot' style='flex:1;min-width:32%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:32%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:32%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>">
                                <i class="fa-solid fa-border-all"></i> 3 Columns
                            </div>
                            <div class="ce-component" draggable="true" data-key="ce-section-4" 
                                 data-html="<section style='margin:0 auto;padding:0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;'><div class='ce-section-slot' style='flex:1;min-width:23%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:23%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:23%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1;min-width:23%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>">
                                <i class="fa-solid fa-grip-lines"></i> 4 Columns
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Code Tab -->
            <div class="ce-tab-pane ce-code-pane" id="tab-code">
                <div class="ce-code-wrap">
                    <div class="ce-code-left">
                        <div class="ce-panel-header" style="border:none;background:transparent;padding:0 0 8px 0;">Preview</div>
                        <iframe id="ceCodePreview" style="width:100%;height:100%;border:1px solid #cbd5e1;border-radius:6px;background:#fff;"></iframe>
                    </div>
                    <div class="ce-code-right">
                        <textarea id="ceCodeArea" name="codeContent" placeholder="Edit HTML code here..."></textarea>
                    </div>
                </div>
                <div class="ce-code-actions">
                    <button type="button" class="ce-btn-sm" id="ceCodeRefresh">
                        <i class="fa-solid fa-rotate-right"></i> Refresh
                    </button>
                    <button type="button" class="ce-btn-sm ce-primary" id="ceCodeApply">
                        <i class="fa-solid fa-check"></i> Apply Changes
                    </button>
                </div>
            </div>

            <!-- Media Tab -->
            <div class="ce-tab-pane" id="tab-media" style="background:#fff;overflow:auto;">
  @include('modules.media.manageMedia')
</div>
            <!-- Save Button Bar -->
<div class="form-actions">
  <div class="save-status" id="saveStatus"></div>
  
  <button type="button" class="btn-cancel" id="btnCancel">
    <i class="fa-solid fa-xmark"></i> Cancel
  </button>
  
  <button type="submit" class="btn-save" id="btnSave">
    <i class="fa-solid fa-floppy-disk"></i>
    <span id="saveText">Save Page</span>
  </button>
</div>
        </form>

        <!-- Export Modal (outside form to prevent submission) -->
        <div id="ceModal">
            <div class="ce-modal-box">
                <h3 style="margin-top:0;margin-bottom:12px;font-size:18px;">Exported HTML</h3>
                <textarea id="ceExport" readonly></textarea>
                <div style="text-align:right;margin-top:10px;">
                    <button id="ceCloseModal" class="ce-btn-sm">
                        <i class="fa-solid fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

(function() {
  // Wait for DOM to ensure all elements exist
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pageMetaForm');
    if (!form) return;
          const pageId = form.dataset.pageId;

    const btnSave = document.getElementById('btnSave');
    const saveText = document.getElementById('saveText');
    const saveStatus = document.getElementById('saveStatus');
    const btnCancel = document.getElementById('btnCancel');

    let hasUnsavedChanges = false;
    
    // Monitor form changes
    form.addEventListener('input', () => {
      hasUnsavedChanges = true;
      if (saveStatus) {
        saveStatus.textContent = '';
        saveStatus.className = 'save-status';
      }
    });

    // Cancel button
    if (btnCancel) {
      btnCancel.addEventListener('click', () => {
        if (hasUnsavedChanges) {
          if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
            window.location.href = '/pages';
          }
        } else {
          window.location.href = '/pages';
        }
      });
    }

    // Warn before leaving
    window.addEventListener('beforeunload', (e) => {
      if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
      }
    });

    function setLoading(loading) {
      if (!btnSave || !saveText) return;
      btnSave.disabled = loading;
      saveText.innerHTML = loading 
        ? '<span class="spinner"></span> Saving...' 
        : 'Save Page';
    }

    function showToast(message, type = 'success') {
      const existingToast = document.querySelector('.toast');
      if (existingToast) existingToast.remove();

      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      
      const icon = type === 'success' 
        ? '<svg class="toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#10b981"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : '<svg class="toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#ef4444"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
      
      toast.innerHTML = `
        ${icon}
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
      `;
      
      document.body.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 5000);
    }

    // Form submission
    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      setLoading(true);
      if (saveStatus) {
        saveStatus.textContent = 'Saving...';
        saveStatus.className = 'save-status';
      }

      try {
        // Sync meta description
        const editor = document.getElementById('metaDescriptionEditor');
        const textarea = document.getElementById('metaDescription');
        if (editor && textarea) {
          textarea.value = editor.innerText.trim();
        }

        // Get form data
        const formData = new FormData(form);
        
        // Add builder content
        if (window.CEBuilder) {
          formData.append('content_html', window.CEBuilder.getHTML());
          formData.append('draft_html', window.CEBuilder.getDraft());
        }

        // Convert to JSON
        const jsonData = {};
        formData.forEach((value, key) => {
          if (key !== 'submenu_exists') {
            jsonData[key] = value;
          }
        });

        // Handle checkbox
        const submenuCheckbox = document.getElementById('submenuExists');
        jsonData.submenu_exists = submenuCheckbox?.checked ? 'yes' : 'no';

        // Clean up empty values
        ['department_id', 'includable_id', 'layout_key'].forEach(key => {
          if (!jsonData[key] || jsonData[key] === '') {
            delete jsonData[key];
          }
        });

        // Get token
        const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';

        // Determine URL
        const pageId = form.dataset.pageId;
        const url = pageId ? `/api/pages/${pageId}` : '/api/pages';
        const method = pageId ? 'PUT' : 'POST';

        console.log('Submitting:', method, url, jsonData);

        // Send request
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token ? { 'Authorization': 'Bearer ' + token } : {})
          },
          body: JSON.stringify(jsonData)
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error('Server response:', errorData);
          throw new Error(errorData.message || 'Failed to save page');
        }

        const result = await response.json();
        console.log('Save successful:', result);
        
        // Success
        // Success
hasUnsavedChanges = false;

// ✅ remove the "saved img" / tick text completely
if (saveStatus) {
  saveStatus.textContent = '';
  saveStatus.className = 'save-status';
}

// ✅ SweetAlert success popup
if (window.Swal) {
  Swal.fire({
    icon: 'success',
    title: 'Saved!',
    text: 'Page saved successfully.',
    timer: 1400,
    showConfirmButton: false
  });
} else {
  alert('Page saved successfully.');
}


      } catch (error) {
        console.error('Save error:', error);
        if (saveStatus) {
          saveStatus.textContent = '✕ Failed to save';
          saveStatus.className = 'save-status error';
        }
        showToast(error.message || 'Failed to save page', 'error');
      } finally {
        setLoading(false);
      }
    });

    // Keyboard shortcut
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (btnSave && !btnSave.disabled) {
          form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }
      }
    });
  });
})();

    // holds the original import’s doctype (e.g. <!DOCTYPE html>)
let importedDocDoctype = '';
// holds everything inside the original <head>…</head>
let importedDocHead = '';
(function(){
  const state = {
    editEl:null,
    outEl:null,
    markerEl:null,
    selectedBlock:null,
    undoStack:[],
    redoStack:[],
    saving:false,
    currentUnit: 'px', // 'px' or '%'
    currentDevice: 'desktop' // 'desktop', 'tablet', 'mobile'
  };
  
  /* ===== HISTORY ===== */
  function pushHistory(){
    if(state.saving) return;
    state.undoStack.push(state.editEl.innerHTML);
    if(state.undoStack.length>100) state.undoStack.shift();
    state.redoStack.length=0;
  }
  function undo(){
    if(!state.undoStack.length) return;
    state.redoStack.push(state.editEl.innerHTML);
    const last=state.undoStack.pop();
    state.editEl.innerHTML=last;
    rebindAll();
    syncExport();
  }
  function redo(){
    if(!state.redoStack.length) return;
    state.undoStack.push(state.editEl.innerHTML);
    const last=state.redoStack.pop();
    state.editEl.innerHTML=last;
    rebindAll();
    syncExport();
  }

  /* ===== TABS ===== */
  document.getElementById('topTabs').addEventListener('click', (e)=>{
    const btn=e.target.closest('.ce-tab-btn');
    if(!btn) return;
    if(btn.id==='ceSave'){ exportHTML(); return; }
    const tab=btn.dataset.tab;
    document.querySelectorAll('.ce-tab-btn[data-tab]').forEach(b=>b.classList.remove('ce-active'));
    btn.classList.add('ce-active');
    document.querySelectorAll('.ce-tab-pane').forEach(p=>p.classList.remove('ce-active'));
    document.getElementById('tab-'+tab).classList.add('ce-active');
    if(tab==='code'){ loadCodeFromExport(); }
  });

  /* COMPONENT TABS */
  document.querySelectorAll('.ce-comp-tab-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.ce-comp-tab-btn').forEach(b=>b.classList.remove('ce-active'));
      btn.classList.add('ce-active');
      const list=btn.dataset.list;
      document.querySelectorAll('.ce-components-list').forEach(l=>l.classList.remove('ce-active'));
      document.getElementById('list-'+list).classList.add('ce-active');
    });
  });

  /* DEVICE PREVIEW */
  document.querySelectorAll('.ce-device-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const device = btn.dataset.device;
      state.currentDevice = device;
      document.querySelectorAll('.ce-device-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('ceCanvasEdit').className = 'ce-canvas ' + device;
    });
  });

  /* ===== HELPERS ===== */
  const isUI = n => n.closest && (n.closest('.ce-block-handle') || n.classList.contains('ce-add-inside'));
  function getClosestBlock(el){
    while(el && el!==state.editEl){
      if(el.classList && el.classList.contains('ce-block')) return el;
      el=el.parentElement;
    }
    return null;
  }
  function deselect(){
    if(state.selectedBlock){
      state.selectedBlock.classList.remove('ce-selected');
      state.selectedBlock=null;
      renderInspector(null);
    }
  }
function syncMetaDescription() {
  document.getElementById('metaDescription').value =
    document.getElementById('metaDescriptionEditor').innerText.trim();
}

  function createBlock(html,key){
    const wrap=document.createElement('div');
    wrap.className='ce-block';
    wrap.dataset.blockId='b'+Date.now()+Math.random().toString(36).slice(2);
    wrap.dataset.key=key;
    wrap.innerHTML=html;

    const handle=document.createElement('div');
    handle.className='ce-block-handle';
    handle.innerHTML=`
      <span class="ce-up" title="Move Up">▲</span>
      <span class="ce-down" title="Move Down">▼</span>
      <span class="ce-dup" title="Duplicate">⧉</span>
      <span class="ce-remove" title="Remove">✕</span>
    `;
    wrap.appendChild(handle);

    if(wrap.querySelector('.ce-slot') && !wrap.querySelector('.ce-add-inside')){
      const addBtn=document.createElement('span');
      addBtn.className='ce-add-inside';
      addBtn.innerHTML=`<i class="fa-solid fa-plus"></i> Add content`;
      const slot=wrap.querySelector('.ce-slot');
      slot.insertAdjacentElement('afterend',addBtn);
    }

    if(key==='ce-column') wrap.style.flex='1';
    attachBlockEvents(wrap);
    bindAddInside(wrap);
    bindHandle(handle, wrap);
    return wrap;
  }

  function bindHandle(handle, block){
    handle.querySelector('.ce-remove').onclick = e=>{
      e.stopPropagation(); pushHistory();
      if(state.selectedBlock===block) deselect();
      block.remove(); syncExport();
    };
    handle.querySelector('.ce-dup').onclick = e=>{
      e.stopPropagation(); pushHistory();
      const clone=block.cloneNode(true);
      clone.dataset.blockId='b'+Date.now()+Math.random().toString(36).slice(2);
      block.insertAdjacentElement('afterend',clone);
      attachBlockEvents(clone);
      bindAddInside(clone);
      clone.querySelectorAll('.ce-block-handle').forEach(h=>bindHandle(h, getClosestBlock(h)));
      syncExport();
    };
    handle.querySelector('.ce-up').onclick = e=>{
      e.stopPropagation(); moveBlock(block,'up');
    };
    handle.querySelector('.ce-down').onclick = e=>{
      e.stopPropagation(); moveBlock(block,'down');
    };
  }

  function moveBlock(block, dir){
    const sib = dir==='up' ? block.previousElementSibling : block.nextElementSibling;
    if(!sib) return;
    pushHistory();
    sib.insertAdjacentElement(dir==='up'?'beforebegin':'afterend', block);
    syncExport();
  }

  function attachBlockEvents(block) {
  // Select / highlight
  block.addEventListener('click', onSelectBlock);

  // Enable dragging of existing blocks (for reorder)
  block.setAttribute('draggable', 'true');
  block.addEventListener('dragstart', onDragStartBlock);

  // NO more dragover() or drop() here!
}


  function bindAddInside(container){
    container.querySelectorAll('.ce-add-inside').forEach(btn=>{
      btn.onclick = function(e){
        e.stopPropagation();
        openAddPopup(btn);
      };
    });
  }

  /* ===== ADD POPUP ===== */
  function openAddPopup(anchor) {
  closePopup();

  const items = Array.from(document.querySelectorAll('#list-elements .ce-component')).map(el => ({
    key: el.dataset.key,
    label: el.textContent.trim()
  }));

  const popup = document.createElement('div');
  popup.className = 'ce-add-popup';
  popup.innerHTML = `<h4>Add element</h4>${items.map(i => `<button data-key="${i.key}">${i.label}</button>`).join('')}`;
  document.body.appendChild(popup);

  const rect = anchor.getBoundingClientRect();
  const margin = 6;

  // Default position: below anchor
  let top = rect.bottom + window.scrollY + margin;
  let left = rect.left + window.scrollX;

  // Temporarily apply the position to measure height
  popup.style.top = top + 'px';
  popup.style.left = left + 'px';

  // Measure the popup after rendering
  const popupHeight = popup.offsetHeight;
  const viewportBottom = window.scrollY + window.innerHeight;

  // If popup would overflow at the bottom, flip to top
  if (top + popupHeight > viewportBottom) {
    top = rect.top + window.scrollY - popupHeight - margin;
    popup.style.top = top + 'px';
  }

  // Handle item clicks
  popup.addEventListener('click', e => {
    if (e.target.tagName === 'BUTTON') {
      const key = e.target.dataset.key;
      const data = getComponentDataByKey(key);
      if (!data) return;

      pushHistory();

      if (key === 'ce-html') {
        const html = prompt('Enter custom HTML:', '<div>Custom</div>');
        if (html !== null) {
          data.html = html;
        } else {
          closePopup();
          return;
        }
      }

      const block = createBlock(data.html, data.key);

      // Slot resolution (column-safe)
      let slot = null;
      const sectionSlot = anchor.closest('.ce-section-slot');
      if (sectionSlot) {
        slot = sectionSlot.querySelector('.ce-slot');
      }

      slot = slot || anchor.previousElementSibling || anchor.parentElement.querySelector('.ce-slot') || anchor.parentElement || state.editEl;

      slot.appendChild(block);
      closePopup();
      syncExport();
    }
  });

  popup.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', closePopup, { once: true });
}


  function closePopup(){
    const p=document.querySelector('.ce-add-popup');
    if(p) p.remove();
  }
  function getComponentDataByKey(key){
    const el=document.querySelector(`.ce-component[data-key="${key}"]`);
    if(!el) return null;
    return {key:el.dataset.key, html:el.dataset.html};
  }

  /* ===== INSPECTOR ===== */
  function renderInspector(block){
    const panel=document.getElementById('ceInspector');
    panel.innerHTML='';

    if(!block){
      panel.innerHTML='<p class="ce-muted">Select a block to edit.</p>';
      return;
    }

    const tabs=document.createElement('div');
    tabs.className='ce-prop-tabs';
    tabs.innerHTML=`
      <button class="ce-prop-tab-btn ce-active" data-prop="style">Styling</button>
      <button class="ce-prop-tab-btn" data-prop="content">Content</button>
      ${block.querySelector('.ce-slot')?'<button class="ce-prop-tab-btn" data-prop="actions">Actions</button>':''}
    `;
    const stylePane=document.createElement('div');stylePane.className='ce-prop-pane ce-active';
    const contentPane=document.createElement('div');contentPane.className='ce-prop-pane';
    const actionsPane=document.createElement('div');actionsPane.className='ce-prop-pane';

    panel.appendChild(tabs);
    panel.appendChild(stylePane);
    panel.appendChild(contentPane);
    if(block.querySelector('.ce-slot')) panel.appendChild(actionsPane);

    tabs.addEventListener('click',e=>{
      const btn=e.target.closest('.ce-prop-tab-btn');
      if(!btn) return;
      tabs.querySelectorAll('.ce-prop-tab-btn').forEach(b=>b.classList.remove('ce-active'));
      btn.classList.add('ce-active');
      const which=btn.dataset.prop;
      stylePane.classList.toggle('ce-active', which==='style');
      contentPane.classList.toggle('ce-active', which==='content');
      actionsPane.classList.toggle('ce-active', which==='actions');
    });

    const contentHTML=getInnerHTMLWithoutUI(block);
    const tagNames=['H1','H2','H3','H4','H5','H6','P','A','SPAN','BUTTON','LI'];
    const textNodes=block.querySelectorAll(tagNames.join(','));

    // Add unit toggle
    const unitToggle = document.createElement('div');
    unitToggle.className = 'ce-unit-toggle';
    unitToggle.innerHTML = `
      <button class="${state.currentUnit === 'px' ? 'active' : ''}" data-unit="px">PX</button>
      <button class="${state.currentUnit === '%' ? 'active' : ''}" data-unit="%">%</button>
    `;
    stylePane.appendChild(unitToggle);
    
    unitToggle.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        state.currentUnit = btn.dataset.unit;
        unitToggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // Re-render inspector to update units
        renderInspector(block);
      });
    });

    addAlignField(stylePane, block);
    addSpacingField(stylePane, block);
    addRadiusField(stylePane, block);
    addColorBorderField(stylePane, block);
    if(textNodes.length){ addTypographyField(stylePane, textNodes); }
    textNodes.forEach(node=>{
      if(/^H[1-6]$/.test(node.tagName)){ addHeadingLevelField(stylePane, node, block); }
    });
    block.querySelectorAll('a,button').forEach((el,i)=>addButtonStyleField(stylePane, el, i));
    // also let people edit the actual link & target
    block.querySelectorAll('a').forEach((el,i)=>addButtonContentField(contentPane, el, i));

    addImageStyleEditors(stylePane, block);

    addTextEditors(contentPane, textNodes);
    addListEditors(contentPane, block);
    addImageContentEditors(contentPane, block);
    if(block.dataset.key==='ce-html'){ addCustomHTMLField(contentPane, block, contentHTML); }
    if (block.dataset.key === 'ce-social-links') {
  // wrapper for all social link controls
  const socialWrapper = document.createElement('div');
  socialWrapper.className = 'ce-field';
  socialWrapper.innerHTML = `<label>Social Links Layout</label>`;
  contentPane.appendChild(socialWrapper);

  // Ensure layout container is flex for justify-content and alignment
  const flexContainer = block.querySelector('.cs-social-links') || block;
  flexContainer.style.display = 'flex';
  flexContainer.style.alignItems = 'center';

  // Gap control
  const gapField = document.createElement('div');
  gapField.className = 'ce-field';
  const firstLink = block.querySelector('a');
  let currentGap = 12;
  if (firstLink) {
    const computed = getComputedStyle(firstLink);
    currentGap = parseInt(computed.marginRight) || currentGap;
  }
  gapField.innerHTML = `
    <label>Icon Gap (px)</label>
    <div style="display:flex;gap:8px;align-items:center;">
      <input type="number" min="0" value="${currentGap}" style="width:70px;" id="socialGapInput" />
      <div class="small text-muted">Space between icons</div>
    </div>`;
  gapField.querySelector('#socialGapInput').addEventListener('input', e => {
    pushHistory();
    const gap = e.target.value + 'px';
    block.querySelectorAll('a').forEach((a, idx, arr) => {
      a.style.marginRight = idx === arr.length - 1 ? '0' : gap;
    });
    syncExport();
  });
  contentPane.appendChild(gapField);

  // Then existing per-icon URL / color / remove logic:
  const links = ['facebook', 'twitter', 'instagram', 'linkedin'];
  links.forEach(name => {
    const a = block.querySelector(`a[href*="${name}.com"]`);
    if (!a) return;
    const i = a.querySelector('i');
    const fld = document.createElement('div');
    fld.className = 'ce-field';

    const labelUrl = name.charAt(0).toUpperCase() + name.slice(1) + ' URL';
    const urlInput = document.createElement('input');
    urlInput.type = 'text';
    urlInput.value = a.href;
    urlInput.placeholder = `https://${name}.com/…`;
    urlInput.addEventListener('input', e => {
      pushHistory();
      a.href = e.target.value;
      syncExport();
    });

    const labelColor = 'Icon Color';
    const colorInput = document.createElement('input');
    colorInput.type = 'color';
    const currentColor = window.getComputedStyle(i || a).color;
    colorInput.value = rgb2hex(currentColor);
    colorInput.addEventListener('input', e => {
      pushHistory();
      if (i) i.style.color = e.target.value;
      else a.style.color = e.target.value;
      syncExport();
    });

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'ce-btn-sm';
    removeBtn.innerHTML = 'Remove';
    removeBtn.addEventListener('click', () => {
      pushHistory();
      a.remove();
      syncExport();
      renderInspector(block); // re-render to reflect removal
    });

    const title = document.createElement('div');
    title.innerHTML = `<strong>${name.charAt(0).toUpperCase() + name.slice(1)}</strong>`;
    fld.appendChild(title);
    fld.innerHTML += `<label>${labelUrl}</label>`;
    fld.appendChild(urlInput);
    fld.appendChild(document.createElement('br'));
    fld.innerHTML += `<label>${labelColor}</label>`;
    fld.appendChild(colorInput);
    fld.appendChild(document.createElement('br'));
    fld.appendChild(removeBtn);

    contentPane.appendChild(fld);
  });
}




    if(block.querySelector('.ce-slot')){
      const f=document.createElement('div');f.className='ce-field';
      f.innerHTML=`<button class="ce-btn-sm ce-primary" id="propAddBtn"><i class="fa-solid fa-plus"></i> Add element here</button>`;
      f.querySelector('#propAddBtn').addEventListener('click',()=>openAddPopup(block.querySelector('.ce-add-inside')||block));
      actionsPane.appendChild(f);
    }
  }

  /* === styling/content helpers === */
  function addAlignField(panel, block){
    const isSocial = block.dataset.key === 'ce-social-links';
    let currentAlign = 'left';

    if (isSocial) {
      const flexContainer = block.querySelector('.cs-social-links') || block;
      // ensure it's flex so justify-content will apply
      flexContainer.style.display = 'flex';
      flexContainer.style.alignItems = 'center';
      const justify = getComputedStyle(flexContainer).justifyContent;
      if (justify === 'center') currentAlign = 'center';
      else if (justify === 'flex-end') currentAlign = 'right';
      else currentAlign = 'left';
    } else {
      currentAlign = getComputedStyle(block).textAlign || 'left';
    }

    const field = document.createElement('div');
    field.className = 'ce-field';
    field.innerHTML = `
      <label>Horizontal Align</label>
      <div class="ce-align-group">
        <button class="ce-align-btn ${currentAlign === 'left' ? 'active' : ''}" data-align="left"><i class="fa-solid fa-align-left"></i></button>
        <button class="ce-align-btn ${currentAlign === 'center' ? 'active' : ''}" data-align="center"><i class="fa-solid fa-align-center"></i></button>
        <button class="ce-align-btn ${currentAlign === 'right' ? 'active' : ''}" data-align="right"><i class="fa-solid fa-align-right"></i></button>
      </div>`;

    field.querySelectorAll('.ce-align-btn').forEach(b => {
      b.addEventListener('click', () => {
        pushHistory();
        const align = b.dataset.align;
        field.querySelectorAll('.ce-align-btn').forEach(x => x.classList.remove('active'));
        b.classList.add('active');

        if (isSocial) {
          const flexContainer = block.querySelector('.cs-social-links') || block;
          if (align === 'center') flexContainer.style.justifyContent = 'center';
          else if (align === 'right') flexContainer.style.justifyContent = 'flex-end';
          else flexContainer.style.justifyContent = 'flex-start';
        } else {
          block.style.textAlign = align;
        }
        syncExport();
      });
    });

    panel.appendChild(field);
  }


  function addSpacingField(panel, block){
    const cs=getComputedStyle(block);
    const mt=parseInt(cs.marginTop)||0, mr=parseInt(cs.marginRight)||0, mb=parseInt(cs.marginBottom)||0, ml=parseInt(cs.marginLeft)||0;
    const pt=parseInt(cs.paddingTop)||0, pr=parseInt(cs.paddingRight)||0, pb=parseInt(cs.paddingBottom)||0, pl=parseInt(cs.paddingLeft)||0;
    
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Margin (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:6px;">
        <input type="number" value="${mt}" data-prop="marginTop" placeholder="Top">
        <input type="number" value="${mr}" data-prop="marginRight" placeholder="Right">
        <input type="number" value="${mb}" data-prop="marginBottom" placeholder="Bottom">
        <input type="number" value="${ml}" data-prop="marginLeft" placeholder="Left">
      </div>
      <label>Padding (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
        <input type="number" value="${pt}" data-prop="paddingTop" placeholder="Top">
        <input type="number" value="${pr}" data-prop="paddingRight" placeholder="Right">
        <input type="number" value="${pb}" data-prop="paddingBottom" placeholder="Bottom">
        <input type="number" value="${pl}" data-prop="paddingLeft" placeholder="Left">
      </div>`;
    
    field.querySelectorAll('input').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory(); 
        const value = e.target.value ? e.target.value + state.currentUnit : '';
        block.style[e.target.dataset.prop] = value;
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addRadiusField(panel, block){
    const cs=getComputedStyle(block);
    const tl=parseInt(cs.borderTopLeftRadius)||0,tr=parseInt(cs.borderTopRightRadius)||0,br=parseInt(cs.borderBottomRightRadius)||0,bl=parseInt(cs.borderBottomLeftRadius)||0;
    block.style.overflow = (tl||tr||br||bl) ? 'hidden' : '';
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Border Radius (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
        <input type="number" value="${tl}" data-prop="borderTopLeftRadius" placeholder="Top Left">
        <input type="number" value="${tr}" data-prop="borderTopRightRadius" placeholder="Top Right">
        <input type="number" value="${br}" data-prop="borderBottomRightRadius" placeholder="Bottom Right">
        <input type="number" value="${bl}" data-prop="borderBottomLeftRadius" placeholder="Bottom Left">
      </div>`;
    field.querySelectorAll('input').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory(); 
        const value = e.target.value ? e.target.value + state.currentUnit : '';
        block.style[e.target.dataset.prop] = value; 
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addColorBorderField(panel, block){
    const cs=getComputedStyle(block);
    const txtColor = rgb2hex(cs.color);
    const bgColor  = rgb2hex(cs.backgroundColor);
    const borderColor = rgb2hex(cs.borderColor);
    const borderWidth = parseInt(cs.borderWidth)||0;
    const borderStyle = cs.borderStyle || 'none';
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Text / Background</label>
      <div class="ce-color-pair">
        <input type="color" value="${txtColor}" title="Text color">
        <input type="color" value="${bgColor}"  title="Background color">
      </div>
      <label style="margin-top:10px;">Border</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <input type="number" min="0" value="${borderWidth}" style="width:60px;" title="Width (px)">
        <select style="flex:1;">
          <option value="none"   ${borderStyle==='none'?'selected':''}>none</option>
          <option value="solid"  ${borderStyle==='solid'?'selected':''}>solid</option>
          <option value="dashed" ${borderStyle==='dashed'?'selected':''}>dashed</option>
          <option value="dotted" ${borderStyle==='dotted'?'selected':''}>dotted</option>
        </select>
        <input type="color" value="${borderColor}" title="Border color" style="width:34px;height:34px;padding:0;border:none;">
      </div>`;
    const [txtInp,bgInp]=field.querySelectorAll('.ce-color-pair input');
    const inputs = field.querySelectorAll('input,select');
    const bWidth=inputs[2], bStyle=inputs[3], bColor=inputs[4];
    txtInp.addEventListener('input',e=>{ pushHistory(); block.style.color=e.target.value; syncExport();});
    bgInp.addEventListener('input',e=>{ pushHistory(); block.style.backgroundColor=e.target.value; syncExport();});
    bWidth.addEventListener('input',e=>{ pushHistory(); block.style.borderWidth=e.target.value+'px'; syncExport();});
    bStyle.addEventListener('change',e=>{ pushHistory(); block.style.borderStyle=e.target.value; syncExport();});
    bColor.addEventListener('input',e=>{ pushHistory(); block.style.borderColor=e.target.value; syncExport();});
    panel.appendChild(field);
  }

  function addTypographyField(panel, nodes){
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Typography</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <select class="ce-font-family" style="flex:1;">
          <option value="">Default</option>
          <option value="Inter, sans-serif">Inter</option>
          <option value="Poppins, sans-serif">Poppins</option>
          <option value="Arial, sans-serif">Arial</option>
          <option value="Georgia, serif">Georgia</option>
          <option value="'Times New Roman', serif">Times New Roman</option>
        </select>
        <input type="number" class="ce-font-size" min="8" max="96" placeholder="px" style="width:70px;" />
      </div>
      <div class="ce-typo-tools">
        <button class="ce-typo-btn" data-style="bold"><i class="fa-solid fa-bold"></i></button>
        <button class="ce-typo-btn" data-style="italic"><i class="fa-solid fa-italic"></i></button>
        <button class="ce-typo-btn" data-style="underline"><i class="fa-solid fa-underline"></i></button>
      </div>`;
    const ff=field.querySelector('.ce-font-family');
    const fs=field.querySelector('.ce-font-size');
    ff.addEventListener('change',e=>{ pushHistory(); nodes.forEach(n=>n.style.fontFamily=e.target.value||''); syncExport();});
    fs.addEventListener('input',e=>{ pushHistory(); nodes.forEach(n=> n.style.fontSize=e.target.value?e.target.value+'px':''); syncExport();});
    field.querySelectorAll('.ce-typo-btn').forEach(btn=>{
      btn.addEventListener('click',()=>{
        pushHistory();
        const st=btn.dataset.style;
        btn.classList.toggle('active');
        nodes.forEach(n=>{
          if(st==='bold') n.style.fontWeight=btn.classList.contains('active')?'700':'';
          if(st==='italic') n.style.fontStyle=btn.classList.contains('active')?'italic':'';
          if(st==='underline') n.style.textDecoration=btn.classList.contains('active')?'underline':'';
        });
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addHeadingLevelField(panel,node,block){
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Heading Level</label>
      <select class="ce-heading-level">
        <option value="H1">H1</option><option value="H2">H2</option><option value="H3">H3</option>
        <option value="H4">H4</option><option value="H5">H5</option><option value="H6">H6</option>
      </select>`;
    const sel=field.querySelector('.ce-heading-level');
    sel.value=node.tagName;
    sel.addEventListener('change',e=>{
      pushHistory();
      const newTag=e.target.value;
      const newNode=document.createElement(newTag);
      Array.from(node.attributes).forEach(a=>newNode.setAttribute(a.name,a.value));
      newNode.innerHTML=node.innerHTML;
      node.parentNode.replaceChild(newNode,node);
      renderInspector(block);
      syncExport();
    });
    panel.appendChild(field);
  }

  function addButtonStyleField(panel, el, idx){
    const cs=getComputedStyle(el);
    const bg=rgb2hex(cs.backgroundColor), color=rgb2hex(cs.color), bW=parseInt(cs.borderWidth)||0, bCol=rgb2hex(cs.borderColor), bSty=cs.borderStyle||'none', bRad=parseInt(cs.borderTopLeftRadius)||0;
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Button ${idx+1} Colors</label>
      <div class="ce-color-pair" style="margin-bottom:6px;">
        <input type="color" value="${bg}" title="Background">
        <input type="color" value="${color}" title="Text">
      </div>
      <label>Border</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <input type="number" min="0" value="${bW}" style="width:60px;" data-btn-prop="borderWidth">
        <select style="flex:1;" data-btn-prop="borderStyle">
          <option value="none" ${bSty==='none'?'selected':''}>none</option>
          <option value="solid" ${bSty==='solid'?'selected':''}>solid</option>
          <option value="dashed" ${bSty==='dashed'?'selected':''}>dashed</option>
          <option value="dotted" ${bSty==='dotted'?'selected':''}>dotted</option>
          <option value="double" ${bSty==='double'?'selected':''}>double</option>
        </select>
        <input type="color" value="${bCol}" style="width:34px;height:34px;padding:0;border:none;" data-btn-prop="borderColor">
      </div>
      <label>Border Radius (${state.currentUnit})</label>
      <input type="number" min="0" value="${bRad}" data-btn-prop="borderRadius">`;
    const [bgInp,txtInp]=field.querySelectorAll('.ce-color-pair input');
    bgInp.addEventListener('input',e=>{ pushHistory(); el.style.backgroundColor=e.target.value; el.style.borderColor=e.target.value; syncExport();});
    txtInp.addEventListener('input',e=>{ pushHistory(); el.style.color=e.target.value; syncExport();});
    field.querySelectorAll('[data-btn-prop]').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory();
        const p=e.target.dataset.btnProp;
        let v=e.target.value;
        if(p==='borderWidth'||p==='borderRadius') {
          v = v ? v + (p === 'borderRadius' ? state.currentUnit : 'px') : '';
        }
        el.style[p]=v; syncExport();
      });
      if(inp.tagName==='SELECT'){
        inp.addEventListener('change',e=>{ pushHistory(); el.style[e.target.dataset.btnProp]=e.target.value; syncExport();});
      }
    });
    panel.appendChild(field);
  }

  function addImageStyleEditors(panel, block){
    const imgs=block.querySelectorAll('img');
    imgs.forEach((img, idx)=>{
      if(isUI(img)) return;
      const parent = img.parentElement;
      const parentAlign = getComputedStyle(parent).textAlign;
      let current = parentAlign==='center' ? 'center' : (parentAlign==='right' ? 'right' : 'left');
      const alignWrap=document.createElement('div');alignWrap.className='ce-field';
      alignWrap.innerHTML=`<label>Image ${idx+1} Align</label>
        <div class="ce-img-align">
          <button class="ce-img-align-btn ${current==='left'?'active':''}" data-img-align="left"><i class="fa-solid fa-align-left"></i></button>
          <button class="ce-img-align-btn ${current==='center'?'active':''}" data-img-align="center"><i class="fa-solid fa-align-center"></i></button>
          <button class="ce-img-align-btn ${current==='right'?'active':''}" data-img-align="right"><i class="fa-solid fa-align-right"></i></button>
        </div>`;
      alignWrap.querySelectorAll('.ce-img-align-btn').forEach(btn=>{
        btn.addEventListener('click',()=>{
          pushHistory();
          const val=btn.dataset.imgAlign;
          img.style.display='inline-block';
          parent.style.textAlign=val;
          alignWrap.querySelectorAll('.ce-img-align-btn').forEach(x=>x.classList.remove('active'));
          btn.classList.add('active');
          syncExport();
        });
      });
      panel.appendChild(alignWrap);
    });
  }

  // replace your existing addTextEditors with this:
  function addTextEditors(panel, nodes) {
  nodes.forEach((node, idx) => {
    if (isUI(node)) return;

    const wrap = document.createElement('div');
    wrap.className = 'ce-field';
    wrap.innerHTML = `
      <label>Text ${idx + 1}</label>
      <div class="ce-text-toolbar">
        <button type="button" data-cmd="bold"><b>B</b></button>
        <button type="button" data-cmd="italic"><i>I</i></button>
        <button type="button" data-cmd="underline"><u>U</u></button>
        <button type="button" data-cmd="strikethrough"><s>S</s></button>
        <button type="button" data-cmd="createLink">🔗</button>
        <input type="color" data-cmd="foreColor" title="Text color">

        <div class="ce-font-controls">
          <select data-cmd="fontName">
            <option value="Arial">Arial</option>
            <option value="Georgia">Georgia</option>
            <option value="Tahoma">Tahoma</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Verdana">Verdana</option>
          </select>
          <select data-cmd="fontSize">
            <option value="1">10px</option>
            <option value="2">13px</option>
            <option value="3" selected>16px</option>
            <option value="4">18px</option>
            <option value="5">24px</option>
            <option value="6">32px</option>
            <option value="7">48px</option>
          </select>
        </div>
      </div>
      <div class="ce-text-area" contenteditable="true">${node.innerHTML}</div>
    `;

    // toolbar buttons
    wrap.querySelectorAll('.ce-text-toolbar button').forEach(btn => {
      btn.addEventListener('click', () => {
        const cmd = btn.dataset.cmd;
        let val = null;
        if (cmd === 'createLink') {
          val = prompt('Enter URL:', 'https://');
          if (!val) return;
        }
        document.execCommand(cmd, false, val);
        wrap.querySelector('.ce-text-area').focus();
      });
    });

    // selects & color picker
    wrap.querySelectorAll('.ce-text-toolbar select, .ce-text-toolbar input[type="color"]')
        .forEach(ctrl => {
      ctrl.addEventListener('change', e => {
        document.execCommand(ctrl.dataset.cmd, false, e.target.value);
        wrap.querySelector('.ce-text-area').focus();
      });
    });

    // sync back into the real node
    const editable = wrap.querySelector('.ce-text-area');
    editable.addEventListener('input', () => {
      pushHistory();
      node.innerHTML = editable.innerHTML;
      syncExport();
    });

    panel.appendChild(wrap);
  });
}




  function addListEditors(panel, block){
    const lists = block.querySelectorAll('ul,ol');
    lists.forEach((list, idx)=>{
      if(isUI(list)) return;
      const field=document.createElement('div');field.className='ce-field';
      field.innerHTML=`<label>List ${idx+1} Items</label>
        <div class="ce-list-items" style="margin-bottom:6px;"></div>
        <button type="button" class="ce-btn-sm ce-primary ce-add-li"><i class="fa-solid fa-plus"></i> Add item</button>`;
      const itemsWrap = field.querySelector('.ce-list-items');

      function makeRow(li){
        const row=document.createElement('div');row.style.display='flex';row.style.gap='6px';row.style.marginBottom='4px';
        row.innerHTML=`<input type="text" value="${li.textContent}" style="flex:1;">
          <button type="button" class="ce-btn-sm ce-del-li"><i class="fa-solid fa-trash"></i></button>`;
        row.querySelector('input').addEventListener('input',e=>{
          pushHistory(); li.textContent=e.target.value; syncExport();
        });
        row.querySelector('.ce-del-li').addEventListener('click',()=>{
          pushHistory(); li.remove(); row.remove(); syncExport();
        });
        return row;
      }

      Array.from(list.children).forEach(li=>{
        if(li.tagName!=='LI') return;
        itemsWrap.appendChild(makeRow(li));
      });

      field.querySelector('.ce-add-li').addEventListener('click',()=>{
        pushHistory();
        const li=document.createElement('li');li.textContent='New item';
        list.appendChild(li);
        itemsWrap.appendChild(makeRow(li));
        syncExport();
      });

      panel.appendChild(field);
    });
  }

  function addImageContentEditors(panel, block){
    const imgs=block.querySelectorAll('img');
    imgs.forEach((img, idx)=>{
      if(isUI(img)) return;
      const wrap=document.createElement('div');wrap.className='ce-field';
      wrap.innerHTML=`<label>Image ${idx+1} URL</label><input type="text" value="${img.src}">`;
      wrap.querySelector('input').addEventListener('input',e=>{
        pushHistory(); img.src=e.target.value; syncExport();
      });
      panel.appendChild(wrap);

      const sizeWrap=document.createElement('div'); sizeWrap.className='ce-field';
      const w=img.getAttribute('width')||''; const h=img.getAttribute('height')||'';
      sizeWrap.innerHTML=`<label>Width / Height</label>
        <div style="display:flex;gap:6px;">
          <input type="number" min="0" placeholder="W" value="${w}" style="flex:1;">
          <input type="number" min="0" placeholder="H" value="${h}" style="flex:1;">
        </div>
        <div class="ce-unit-toggle" style="margin-top:6px;">
          <button class="${w.toString().includes('%') ? 'active' : ''}" data-unit="%">%</button>
          <button class="${w.toString().includes('px') || !w ? 'active' : ''}" data-unit="px">PX</button>
        </div>`;
      const [wInp,hInp]=sizeWrap.querySelectorAll('input');
      const unitToggle = sizeWrap.querySelector('.ce-unit-toggle');
      
      // Set initial unit
      let currentUnit = w.toString().includes('%') ? '%' : 'px';
      
      wInp.addEventListener('input',e=>{
        pushHistory(); 
        const value = e.target.value ? e.target.value + currentUnit : '';
        img.setAttribute('width', value); 
        syncExport();
      });
      hInp.addEventListener('input',e=>{
        pushHistory(); 
        const value = e.target.value ? e.target.value + currentUnit : '';
        img.setAttribute('height', value); 
        syncExport();
      });
      
      unitToggle.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
          currentUnit = btn.dataset.unit;
          unitToggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          
          // Update values with new unit
          const wVal = wInp.value ? wInp.value + currentUnit : '';
          const hVal = hInp.value ? hInp.value + currentUnit : '';
          if (wVal) img.setAttribute('width', wVal);
          if (hVal) img.setAttribute('height', hVal);
          syncExport();
        });
      });
      
      panel.appendChild(sizeWrap);
    });
  }

  function addCustomHTMLField(panel, block, html){
    const htmlField=document.createElement('div');htmlField.className='ce-field';
    htmlField.innerHTML=`<label>Custom HTML</label><textarea>${html}</textarea>`;
    htmlField.querySelector('textarea').addEventListener('change',e=>{
      pushHistory();
      const handle=block.querySelector('.ce-block-handle');
      const addBtns=[...block.querySelectorAll('.ce-add-inside')];
      block.innerHTML=e.target.value;
      if(handle) block.appendChild(handle);
      addBtns.forEach(a=>block.appendChild(a));
      bindAddInside(block);
      syncExport();
    });
    panel.appendChild(htmlField);
  }
  /**
 * In the Content pane, let the user edit the href & target of each <a>
 */
function addButtonContentField(panel, el, idx) {
  const field = document.createElement('div');
  field.className = 'ce-field';
  field.innerHTML = `
    <label>Button ${idx+1} Link URL</label>
    <input type="text" class="ce-btn-link-url" placeholder="https://example.com" value="${el.getAttribute('href')||''}" />
    <div style="margin-top:8px;">
      <label><input type="checkbox" class="ce-btn-link-target" ${el.target === '_blank' ? 'checked' : ''}/> Open in new tab</label>
    </div>
  `;

  const urlInput = field.querySelector('.ce-btn-link-url');
  const targetCheckbox = field.querySelector('.ce-btn-link-target');

  urlInput.addEventListener('input', e => {
    pushHistory();
    el.setAttribute('href', e.target.value);
    syncExport();
  });

  targetCheckbox.addEventListener('change', e => {
    pushHistory();
    if (e.target.checked) {
      el.setAttribute('target', '_blank');
      el.setAttribute('rel', 'noopener noreferrer');
    } else {
      el.removeAttribute('target');
      el.removeAttribute('rel');
    }
    syncExport();
  });

  panel.appendChild(field);
}


  /* ===== CLEAN & SYNC ===== */
  function getInnerHTMLWithoutUI(el){
    const clone=el.cloneNode(true);
    clone.querySelectorAll('.ce-block-handle,.ce-add-inside,.ce-drop-marker').forEach(x=>x.remove());
    return clone.innerHTML.trim();
  }

  function cleanCloneFromEdit(){
    const clone=state.editEl.cloneNode(true);
    clone.querySelectorAll('.ce-block-handle,.ce-add-inside,.ce-drop-marker').forEach(x=>x.remove());
    clone.querySelectorAll('.ce-block').forEach(b=>{
      b.classList.remove('ce-block','ce-selected','ce-prop-active');
      b.removeAttribute('data-block-id');
      b.removeAttribute('data-key');
    });
    clone.querySelectorAll('.ce-slot').forEach(s=>s.replaceWith(...s.childNodes));
    return clone;
  }

  function syncExport(){
  // 1) clean & mirror for export
  const cleaned = cleanCloneFromEdit();
  state.outEl.innerHTML = cleaned.innerHTML;

  // 2) **raw** draft: the full editor inner HTML, handles & all
  document.getElementById('ceDraftExport').value = state.editEl.innerHTML;
}

  function rebuildEditFromExport(html){
    state.editEl.innerHTML='';
    const tmp=document.createElement('div'); tmp.innerHTML=html;
    Array.from(tmp.childNodes).forEach(n=>{
      if(n.nodeType===1){
        const wrap=createBlock(n.outerHTML,'custom');
        state.editEl.appendChild(wrap);
      }else if(n.nodeType===3 && n.textContent.trim()){
        const wrap=createBlock(`<p>${n.textContent}</p>`,'ce-paragraph');
        state.editEl.appendChild(wrap);
      }
    });
    rebindAll();
    syncExport();
  }

  function rgb2hex(rgb){
    if(!rgb || rgb==='transparent') return '#ffffff';
    const m=rgb.match(/\d+/g); if(!m) return '#ffffff';
    const r=parseInt(m[0]).toString(16).padStart(2,'0');
    const g=parseInt(m[1]).toString(16).padStart(2,'0');
    const b=parseInt(m[2]).toString(16).padStart(2,'0');
    return '#'+r+g+b;
  }

  /* ===== DRAG from palette ===== */
  function onDragStartComponent(e){
    const el=e.currentTarget;
    e.dataTransfer.setData('text/plain', JSON.stringify({ key: el.dataset.key, html: el.dataset.html }));
    e.dataTransfer.effectAllowed='copy';
  }

  /* ===== CANVAS DnD ===== */
  function onDragOverCanvas(e){
    e.preventDefault();
    const marker=state.markerEl;
    const target=getClosestBlock(e.target);
    if(target){
      const rect=target.getBoundingClientRect();
      const before=(e.clientY-rect.top) < rect.height/2;
      marker.classList.add('active');
      target.insertAdjacentElement(before?'beforebegin':'afterend',marker);
      marker.dataset.position=before?'before':'after';
      marker.dataset.targetId=target.dataset.blockId;
    }else{
      state.editEl.appendChild(marker);
      marker.classList.add('active');
      marker.dataset.position='end';
      marker.dataset.targetId='';
    }
  }
  function onDropCanvas(e){
    e.preventDefault();
    const raw=e.dataTransfer.getData('text/plain');
    if(!raw) return;
    let data; try{data=JSON.parse(raw);}catch(_){return;}
    pushHistory();
    if(data.key==='ce-html'){
      const html=prompt('Enter custom HTML:','<div>Custom</div>');
      if(html!==null) data.html=html;
    }
    const block=createBlock(data.html,data.key);
    const marker=state.markerEl;
    const id=marker.dataset.targetId;
    const pos=marker.dataset.position;
    marker.classList.remove('active');
    if(id){
      const target=state.editEl.querySelector(`[data-block-id="${id}"]`);
      target.insertAdjacentElement(pos==='before'?'beforebegin':'afterend',block);
    }else{
      state.editEl.appendChild(block);
    }

    block.querySelectorAll('.ce-slot').forEach(slot=>{
      const parent = slot.parentElement;
      if(parent && !parent.classList.contains('ce-block')){
        const colBlock = createBlock(parent.innerHTML,'ce-column');
        colBlock.style.flex='1';
        parent.replaceWith(colBlock);
      }
    });

    syncExport();
  }
  function onDragStartBlock(e){
    const b=getClosestBlock(e.target);
    if(!b) return;
    e.dataTransfer.setData('application/x-ce-block', b.dataset.blockId);
    e.dataTransfer.effectAllowed='move';
  }

  /* ===== SELECT ===== */
  function onSelectBlock(e){
    e.stopPropagation();
    const block=getClosestBlock(e.target);
    if(!block) return;
    deselect();
    state.selectedBlock=block;
    block.classList.add('ce-selected');
    renderInspector(block);
  }

  /* ===== EXPORT ===== */
  // Replace your existing getExportHTML() with this:
  function getExportHTML(){
  // 1) grab the cleaned inner HTML
  const inner = state.outEl.innerHTML.trim();

  // 2) return the full email template
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Your Campaign</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    /* Reset */
    body, table, td { margin:0; padding:0; }
    img { border:none; display:block; max-width:100%; height:auto; }
    a { text-decoration:none; }

    /* Wrapper tables */
    .wrapper { width:100% !important; background:#f3f4f6; }
    .inner   {
      width:100% !important;
      margin:0 auto;
      background:#ffffff;
      box-shadow:0 1px 3px rgba(0,0,0,0.1);
    }

    /* Mobile tweaks */
    @media only screen and (max-width:600px) {
      .inner { box-shadow:none !important; }
    }
      .ce-section-slot-wrapper {
        width:100% !important;
        display: flex;
        }

        /* this must come *after* your base rule */
        @media (max-width: 375px) {
        .ce-section-slot-wrapper {
            flex-direction: column !important;
        }
        }
  </style>
</head>
<body>
  <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
    <tr>
      <td align="center">
        <table class="inner" cellpadding="0" cellspacing="0" border="0" align="center">
          <tr>
            <td style="padding:24px; font-family:Arial,sans-serif; color:#111827;">
              ${inner}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>`;
}


  function exportHTML(){
    const html=getExportHTML();
    document.getElementById('ceExport').value=html;
    document.getElementById('ceModal').style.display='flex';
  }

  /* ===== CODE TAB ===== */
  function loadCodeFromExport(){
    const html=getExportHTML();
    document.getElementById('ceCodeArea').value=html;
    refreshPreview(html);
  }
  function refreshPreview(html){
    const iframe=document.getElementById('ceCodePreview');
    iframe.contentDocument.open();iframe.contentDocument.write(html);iframe.contentDocument.close();
  }
  function applyCodeToCanvas(){
    const txt=document.getElementById('ceCodeArea').value;
    state.outEl.innerHTML=txt;
    rebuildEditFromExport(txt);
    refreshPreview(txt);
  }

  function rebindAll(){
    deselect();
    state.editEl.querySelectorAll('.ce-block').forEach(b=>attachBlockEvents(b));
    state.editEl.querySelectorAll('.ce-block-handle').forEach(h=>{
      const blk=getClosestBlock(h);
      bindHandle(h, blk);
    });
    bindAddInside(state.editEl);
  }
async function loadDepartments() {
  try {
    const token =
      localStorage.getItem('token') ||
      sessionStorage.getItem('token') ||
      '';

    const res = await fetch('/api/departments', {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': 'Bearer ' + token } : {})
      }
    });

    if (!res.ok) {
      console.warn('Departments load failed:', res.status);
      return;
    }

    const json = await res.json();

    if (!json || !Array.isArray(json.data)) {
      console.warn('Unexpected department response', json);
      return;
    }

    const select = document.getElementById('departmentId');
    select.innerHTML = '<option value="">Select department</option>';

    json.data.forEach(dep => {
      const opt = document.createElement('option');
      opt.value = dep.id;
      opt.textContent = dep.title;
      select.appendChild(opt);
    });

  } catch (err) {
    console.error('Department load error', err);
  }
}

// document.addEventListener('DOMContentLoaded', loadDepartments);
async function loadPageForEdit() {
  const form = document.getElementById('pageMetaForm');
  if (!form) return;

  const pageId = form.dataset.pageId;
  if (!pageId) return;

  console.log('EDIT MODE:', pageId);

  const token =
    localStorage.getItem('token') ||
    sessionStorage.getItem('token') || '';

  const res = await fetch(`/api/pages/${pageId}`, {
    headers: {
      'Accept': 'application/json',
      ...(token ? { 'Authorization': 'Bearer ' + token } : {})
    }
  });

  const { data } = await res.json();

  // Meta
  document.getElementById('pageTitle').value = data.title || '';
  document.getElementById('metaDescriptionEditor').innerText =
    data.meta_description || '';
  document.getElementById('pageStatus').value = data.status || 'Active';
  document.getElementById('pageType').value = data.page_type || 'page';
  document.getElementById('departmentId').value = data.department_id || '';
  document.getElementById('submenuExists').checked =
    data.submenu_exists === 'yes';

  document.getElementById('includable_id').value =
    data.includable_id || '';
  document.getElementById('layout_key').value =
    data.layout_key || '';

  // Builder
  if (window.CEBuilder && data.content_html) {
    window.CEBuilder.setHTML(data.content_html);
  }

  document.getElementById('saveText').textContent = 'Update Page';
}
function waitForCEBuilder(cb) {
  const timer = setInterval(() => {
    if (window.CEBuilder && typeof window.CEBuilder.setHTML === 'function') {
      clearInterval(timer);
      cb();
    }
  }, 50);
}


document.addEventListener('DOMContentLoaded', async () => {
  await loadDepartments();

  waitForCEBuilder(() => {
    loadPageForEdit();
  });
});

  /* ===== BOOT ===== */
  window.addEventListener('DOMContentLoaded', () => {
    document
    .getElementById('ceCanvasEdit')
    .addEventListener('click', e => {
      // if you clicked on an <a> (or inside one), stop it
      if (e.target.closest('a')) {
        e.preventDefault();
      }
    });
    
  state.editEl   = document.getElementById('ceCanvasEdit');
  state.outEl    = document.getElementById('ceCanvasExport');
  state.markerEl = document.createElement('div');
  state.markerEl.className = 'ce-drop-marker';

  // 1) Only the canvas handles dragover & drop
  state.editEl.addEventListener('dragover', onDragOverCanvas);
  state.editEl.addEventListener('drop',    onDropCanvas);

  // 2) Close inspector/popup on canvas click
  state.editEl.addEventListener('click', () => { deselect(); closePopup(); });

  // 3) Only palette items (elements & sections) start a new drag
  document
    .querySelectorAll('#list-elements .ce-component, #list-sections .ce-component')
    .forEach(el => el.addEventListener('dragstart', onDragStartComponent));

  // 4) Modal close button
  document.getElementById('ceCloseModal')
    .addEventListener('click', () => {
      document.getElementById('ceModal').style.display = 'none';
    });

  // 5) Code tab buttons
  document.getElementById('ceCodeRefresh')
    .addEventListener('click', loadCodeFromExport);
  document.getElementById('ceCodeApply')
    .addEventListener('click', applyCodeToCanvas);

  // 6) Undo / Redo
  document.getElementById('ceUndo')
    .addEventListener('click', undo);
  document.getElementById('ceRedo')
    .addEventListener('click', redo);

  // 7) Keyboard shortcuts
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') {
      e.preventDefault();
      undo();
    }
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'z') {
      e.preventDefault();
      redo();
    }
    if (state.selectedBlock && e.altKey && e.key === 'ArrowUp') {
      e.preventDefault();
      moveBlock(state.selectedBlock, 'up');
    }
    if (state.selectedBlock && e.altKey && e.key === 'ArrowDown') {
      e.preventDefault();
      moveBlock(state.selectedBlock, 'down');
    }
  });

  // 8) Initialize history & export
  pushHistory();
  syncExport();
});

  window.CEBuilder = {
    getHTML: getExportHTML,
    setHTML(html) {
      rebuildEditFromExport(html || '');
      syncExport();
    },
    getDraft() {
      return document.getElementById('ceDraftExport').value;
    }
  };


})();
</script>
</body>
</html>