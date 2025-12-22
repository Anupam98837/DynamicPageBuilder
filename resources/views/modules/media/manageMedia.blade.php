<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Media Manager</title>
 
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
  />
 
  <meta name="csrf-token" content="{{ csrf_token() }}">
 
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/manageMedia/manageMedia.css') }}">
</head>
<body class="bg-light">
  <div class="container py-5">
 
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs" id="mediaTab">
      <li class="nav-item">
        <button class="nav-link active" type="button" id="library-tab" data-bs-toggle="tab" data-bs-target="#libraryPane">
          <i class="fa-solid fa-list"></i> Library
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" type="button" id="upload-tab" data-bs-toggle="tab" data-bs-target="#uploadPane">
          <i class="fa-solid fa-upload"></i> Upload
        </button>
      </li>
    </ul>
 
    <div class="tab-content">
 
      <!-- Library Pane -->
      <div class="tab-pane fade show active" id="libraryPane">
        <div class="card-container mt-3" id="mediaCards">
          <!-- cards injected here -->
        </div>
      </div>
 
      <!-- Upload Pane -->
      <div class="tab-pane fade" id="uploadPane">
        <div class="mt-3">
          <div id="dropZone" class="drop-zone mb-3">
            <p>Drag &amp; drop files here, or</p>
            <button class="btn btn-outline-primary" type="button" id="pickFileBtn">
              <i class="fa-solid fa-folder-open"></i> Choose File
            </button>
            <input type="file" id="fileInput" multiple class="d-none" />
          </div>
          <ul class="list-group" id="uploadList"></ul>
        </div>
      </div>
 
    </div>
  </div>
 
  <!-- Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
  <script>
    // const token = sessionStorage.getItem('token');
    // if (!token) location.href = '/';
 
    const API = {
      list:    () => fetch('/api/media', { headers: { 'Authorization': `Bearer ${token}` } }),
      upload:  form => fetch('/api/media', { method:'POST', headers: { 'Authorization': `Bearer ${token}` }, body: form }),
      remove:  id   => fetch(`/api/media/${id}`, { method:'DELETE', headers:{ 'Authorization': `Bearer ${token}` } }),
    };
 
    function fmtSize(b){
      if(b < 1024) return b + ' B';
      if(b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
      return (b/(1024*1024)).toFixed(1) + ' MB';
    }
 
    // ——— Library ———
    async function loadLibrary(){
      const container = document.getElementById('mediaCards');
      container.innerHTML = '';
      try {
        const res = await API.list().then(r=>r.json());
        if(res.status !== 'success') throw '';
        res.data.forEach(item => {
          // thumbnail or fallback
          const thumbHTML = `
            <div class="thumb-container">
              <img src="${item.url}" loading="lazy"
                   onerror="
                     this.style.display='none';
                     this.parentNode.querySelector('.fallback-icon').style.display='flex';
                   " />
              <div class="fallback-icon">
                <i class="fa-solid fa-file-image"></i>
              </div>
            </div>`;
 
          const card = document.createElement('div');
          card.className = 'media-card';
          card.innerHTML = `
            ${thumbHTML}
            <div class="card-body">
              <div class="title">${item.url.split('/').pop()}</div>
            </div>
            <div class="overlay">
              <div class="url">${item.url}</div>
              <div class="size">${fmtSize(item.size)}</div>
              <button class="btn btn-copy btn-sm" type="button">
                <i class="fa-solid fa-copy me-1"></i>Copy URL
              </button>
            </div>`;
 
          // Copy handler
          card.querySelector('.btn-copy').onclick = e => {
            e.stopPropagation();
            navigator.clipboard.writeText(item.url)
              .then(()=> Swal.fire({ icon:'success', title:'Copied!', timer:1200, showConfirmButton:false }))
              .catch(()=> Swal.fire('Error','Copy failed','error'));
          };
 
          // Click opens full URL in new tab
          card.onclick = () => window.open(item.url, '_blank');
 
          container.append(card);
        });
      } catch {
        Swal.fire('Error','Could not load media','error');
      }
    }
 
    // ——— Delete on long-press ———
    let pressTimer;
    document.addEventListener('mousedown', e => {
      const card = e.target.closest('.media-card');
      if (!card) return;
      pressTimer = setTimeout(async () => {
        const url = card.querySelector('.overlay .url').textContent;
        const match = url.match(/\/(\d+)(?:[/?]|$)/);
        const id = match ? parseInt(match[1]) : null;
        if (id) {
          const { isConfirmed } = await Swal.fire({
            title: 'Delete this file?',
            icon: 'warning',
            showCancelButton: true
          });
          if (isConfirmed) {
            const r2 = await API.remove(id).then(r=>r.json());
            if (r2.status === 'success') {
              Swal.fire('Deleted', r2.message, 'success');
              loadLibrary();
            } else {
              Swal.fire('Error', r2.message||'Delete failed','error');
            }
          }
        }
      }, 800);
    });
    document.addEventListener('mouseup', () => clearTimeout(pressTimer));
 
    // ——— Upload Pane ———
    const dropZone  = document.getElementById('dropZone'),
          fileInput = document.getElementById('fileInput'),
          uploadList= document.getElementById('uploadList');
 
    document.getElementById('pickFileBtn').onclick = () => fileInput.click();
    fileInput.onchange = () => handleFiles([...fileInput.files]);
 
    ['dragenter','dragover'].forEach(evt =>
      dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('dragover'); })
    );
    ['dragleave','drop'].forEach(evt =>
      dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('dragover'); })
    );
    dropZone.addEventListener('drop', e => handleFiles([...e.dataTransfer.files]));
 
    function addUploadItem(name){
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between';
      li.textContent = name;
      const badge = document.createElement('span');
      badge.className = 'badge bg-secondary';
      badge.textContent = 'waiting';
      li.append(badge);
      uploadList.append(li);
      return badge;
    }
 
    async function handleFiles(files){
      for (let f of files) {
        let badge = addUploadItem(f.name),
            form  = new FormData();
        form.append('file', f);
 
        try {
          let json = await API.upload(form).then(r=>r.json());
          if (json.status === 'success') {
            badge.className = 'badge bg-success';
            badge.textContent = 'done';
          } else {
            badge.className = 'badge bg-danger';
            badge.textContent = 'error';
          }
        } catch {
          badge.className = 'badge bg-danger';
          badge.textContent = 'error';
        }
      }
      await loadLibrary();
    }
 
    // ——— Initialize ———
    document.addEventListener('DOMContentLoaded', () => {
      loadLibrary();
      document.getElementById('library-tab')
        .addEventListener('shown.bs.tab', loadLibrary);
    });
  </script>
</body>
</html>
 
 