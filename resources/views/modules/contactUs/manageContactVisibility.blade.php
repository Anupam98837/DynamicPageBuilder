{{-- resources/views/modules/contactUs/manageContactVisibility.blade.php --}}
@section('title','Manage Contact Page')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  .mc-wrap{ padding: 18px; }

  .mc-card{
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    box-shadow: var(--shadow-2);
    overflow: hidden;
  }

  .mc-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap: 12px;
    padding: 14px 14px 10px;
    border-bottom: 1px solid var(--line-soft);
  }
  .mc-head h3{
    margin:0;
    font-weight: 900;
    color: var(--ink);
    letter-spacing: .2px;
  }
  .mc-head p{
    margin: 6px 0 0;
    color: var(--muted-color);
    font-size: 13px;
  }

  .mc-actions-top{
    display:flex;
    gap: 10px;
    align-items:center;
    justify-content:flex-end;
    flex-wrap: wrap;
  }

  .mc-btn{
    border: 1px solid var(--line-strong);
    background: transparent;
    color: var(--ink);
    padding: 10px 12px;
    border-radius: 12px;
    font-weight: 900;
    display:inline-flex;
    align-items:center;
    gap: 8px;
    cursor: pointer;
  }
  .mc-btn:hover{ filter: brightness(.98); }

  .mc-btn.primary{
    border: none;
    background: var(--primary-color);
    color: #fff;
  }
  .mc-btn.primary:hover{ background: var(--secondary-color); }

  .mc-body{ padding: 14px; }

  .mc-grid{
    display:grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 14px;
  }

  .mc-pane{
    border: 1px solid var(--line-soft);
    border-radius: 14px;
    background: rgba(255,255,255,.6);
    padding: 12px;
  }
  html.theme-dark .mc-pane{ background: rgba(0,0,0,.08); }

  .mc-pane h4{
    margin:0 0 10px;
    font-weight: 900;
    color: var(--ink);
    font-size: 14px;
    display:flex;
    align-items:center;
    gap: 8px;
  }

  .mc-switches{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .mc-sw{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
    border: 1px solid var(--line-soft);
    border-radius: 12px;
    padding: 10px;
    background: rgba(255,255,255,.65);
  }
  html.theme-dark .mc-sw{ background: rgba(0,0,0,.08); }

  .mc-meta{ display:flex; flex-direction:column; gap: 3px; }
  .mc-meta b{ color: var(--ink); font-size: 13px; }
  .mc-meta span{ color: var(--muted-color); font-size: 12px; }

  .mc-sw input[type="checkbox"]{
    width: 44px;
    height: 22px;
    accent-color: var(--primary-color);
    cursor: pointer;
  }

  .mc-note{
    margin-top: 10px;
    color: var(--muted-color);
    font-size: 12.5px;
    line-height: 1.45;
  }

  .mc-kv{
    display:grid;
    grid-template-columns: 120px 1fr;
    gap: 8px 10px;
    font-size: 13px;
  }
  .mc-kv .k{ color: var(--muted-color); font-weight: 800; }
  .mc-kv .v{ color: var(--ink); font-weight: 900; }

  .mc-mini-preview{
    margin-top: 10px;
    border: 1px dashed var(--line-strong);
    border-radius: 14px;
    padding: 10px;
  }
  .mc-chip{
    display:inline-flex;
    align-items:center;
    gap: 6px;
    border: 1px solid var(--line-soft);
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 900;
    color: var(--ink);
    background: rgba(255,255,255,.55);
    margin: 4px 6px 0 0;
  }
  html.theme-dark .mc-chip{ background: rgba(0,0,0,.08); }

  @media(max-width: 980px){
    .mc-grid{ grid-template-columns: 1fr; }
    .mc-switches{ grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')
<div class="mc-wrap">

  <div class="mc-card">
    <div class="mc-head">
      <div>
        <h3>Manage Contact Page</h3>
        <p>Toggle which sections should be visible on the public <b>Contact Us</b> page.</p>
      </div>

      <div class="mc-actions-top">
        <a class="mc-btn" href="{{ url('/contact-us') }}" target="_blank" rel="noopener">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview Public Page
        </a>
        <button id="btnReload" class="mc-btn" type="button">
          <i class="fa-solid fa-rotate"></i> Reload
        </button>
        <button id="btnAllOn" class="mc-btn" type="button">
          <i class="fa-solid fa-check-double"></i> All ON
        </button>
        <button id="btnSave" class="mc-btn primary" type="button">
          <i class="fa-solid fa-floppy-disk"></i> Save
        </button>
      </div>
    </div>

    <div class="mc-body">
      <div class="mc-grid">

        {{-- LEFT: VISIBILITY TOGGLES --}}
        <div class="mc-pane">
          <h4><i class="fa-solid fa-toggle-on"></i> Section Visibility</h4>

          <div class="mc-switches">
            <div class="mc-sw">
              <div class="mc-meta">
                <b>Address</b>
                <span>Show/hide the address block</span>
              </div>
              <input id="show_address" type="checkbox">
            </div>

            <div class="mc-sw">
              <div class="mc-meta">
                <b>Call Us</b>
                <span>Show/hide phone block</span>
              </div>
              <input id="show_call" type="checkbox">
            </div>

            <div class="mc-sw">
              <div class="mc-meta">
                <b>Recruitment Email</b>
                <span>Show/hide placement email</span>
              </div>
              <input id="show_recruitment" type="checkbox">
            </div>

            <div class="mc-sw">
              <div class="mc-meta">
                <b>General Email</b>
                <span>Show/hide info email</span>
              </div>
              <input id="show_email" type="checkbox">
            </div>

            <div class="mc-sw">
              <div class="mc-meta">
                <b>Contact Form</b>
                <span>Show/hide message form</span>
              </div>
              <input id="show_form" type="checkbox">
            </div>

            <div class="mc-sw">
              <div class="mc-meta">
                <b>Map</b>
                <span>Show/hide Google map</span>
              </div>
              <input id="show_map" type="checkbox">
            </div>
          </div>

          <div class="mc-note">
            Tip: your public <code>contact-us.blade.php</code> should read these flags from
            <code>contact_us_page_visibility</code> (latest row) and show/hide sections accordingly.
          </div>
        </div>

        {{-- RIGHT: STATUS / MINI PREVIEW --}}
        <div class="mc-pane">
          <h4><i class="fa-solid fa-circle-info"></i> Status</h4>

          <div class="mc-kv">
            <div class="k">Loaded Row</div>
            <div class="v" id="rowId">-</div>

            <div class="k">Updated At</div>
            <div class="v" id="updatedAt">-</div>
          </div>

          <div class="mc-mini-preview">
            <div style="font-weight:900; color:var(--ink); margin-bottom:6px;">
              Live Preview (enabled sections)
            </div>
            <div id="chips"></div>
          </div>

        </div>

      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const API_GET = '/api/contact-us/visibility';
  const API_PUT = '/api/contact-us/visibility';

  const els = {
    show_address: document.getElementById('show_address'),
    show_call: document.getElementById('show_call'),
    show_recruitment: document.getElementById('show_recruitment'),
    show_email: document.getElementById('show_email'),
    show_form: document.getElementById('show_form'),
    show_map: document.getElementById('show_map'),
    rowId: document.getElementById('rowId'),
    updatedAt: document.getElementById('updatedAt'),
    chips: document.getElementById('chips'),
    btnSave: document.getElementById('btnSave'),
  };

  function getToken(){
    return localStorage.getItem('token')
      || localStorage.getItem('auth_token')
      || sessionStorage.getItem('token')
      || sessionStorage.getItem('auth_token')
      || '';
  }

  function headers(){
    const h = { 'Content-Type': 'application/json' };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrf) h['X-CSRF-TOKEN'] = csrf;

    const t = getToken();
    if (t) h['Authorization'] = 'Bearer ' + t;

    return h;
  }

  function renderChips(){
    const chips = [];
    if (els.show_address.checked) chips.push('Address');
    if (els.show_call.checked) chips.push('Call Us');
    if (els.show_recruitment.checked) chips.push('Recruitment');
    if (els.show_email.checked) chips.push('Email');
    if (els.show_form.checked) chips.push('Form');
    if (els.show_map.checked) chips.push('Map');

    els.chips.innerHTML = chips.length
      ? chips.map(t => `<span class="mc-chip"><i class="fa-solid fa-check"></i> ${t}</span>`).join('')
      : `<span class="mc-chip"><i class="fa-solid fa-triangle-exclamation"></i> No sections enabled</span>`;
  }

  function applyRow(row){
    const r = row || {};
    els.rowId.textContent = r.id ?? '-';
    els.updatedAt.textContent = r.updated_at ?? '-';

    els.show_address.checked = !!(r.show_address ?? true);
    els.show_call.checked = !!(r.show_call ?? true);
    els.show_recruitment.checked = !!(r.show_recruitment ?? true);
    els.show_email.checked = !!(r.show_email ?? true);
    els.show_form.checked = !!(r.show_form ?? true);
    els.show_map.checked = !!(r.show_map ?? true);

    renderChips();
  }

  async function load(){
    try{
      const res = await fetch(API_GET, { headers: headers() });
      const json = await res.json().catch(()=> ({}));
      if(!res.ok) throw new Error(json.message || 'Failed to load visibility settings');
      applyRow(json.data);
    }catch(e){
      console.error(e);
      Swal.fire('Error', e.message || 'Failed to load', 'error');
      applyRow(null);
    }
  }

  async function save(){
    const payload = {
      show_address: els.show_address.checked,
      show_call: els.show_call.checked,
      show_recruitment: els.show_recruitment.checked,
      show_email: els.show_email.checked,
      show_form: els.show_form.checked,
      show_map: els.show_map.checked,
    };

    els.btnSave.disabled = true;
    try{
      const res = await fetch(API_PUT, {
        method: 'PUT',
        headers: headers(),
        body: JSON.stringify(payload),
      });
      const json = await res.json().catch(()=> ({}));
      if(!res.ok) throw new Error(json.message || 'Save failed');

      applyRow(json.data || null);
      Swal.fire('Saved', 'Visibility settings updated.', 'success');
    }catch(e){
      console.error(e);
      Swal.fire('Error', e.message || 'Save failed', 'error');
    }finally{
      els.btnSave.disabled = false;
    }
  }

  // events
  document.getElementById('btnReload').addEventListener('click', load);
  document.getElementById('btnSave').addEventListener('click', save);
  document.getElementById('btnAllOn').addEventListener('click', function(){
    els.show_address.checked = true;
    els.show_call.checked = true;
    els.show_recruitment.checked = true;
    els.show_email.checked = true;
    els.show_form.checked = true;
    els.show_map.checked = true;
    renderChips();
  });

  // live preview changes
  ['show_address','show_call','show_recruitment','show_email','show_form','show_map'].forEach(id => {
    document.getElementById(id).addEventListener('change', renderChips);
  });

  load();
})();
</script>
@endpush
