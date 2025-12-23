<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

<style>
body{background:var(--bg-body);color:var(--ink)}

/* ===== Layout ===== */
.profile-layout{max-width:1280px;margin:32px auto;display:grid;grid-template-columns:280px 1fr;gap:24px}
@media(max-width:992px){.profile-layout{grid-template-columns:1fr}}

/* ===== Sidebar ===== */
.profile-sidebar{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  padding:18px;
  box-shadow:var(--shadow-2);
  position:sticky;top:24px
}
.profile-avatar{
  width:110px;height:110px;border-radius:18px;
  overflow:hidden;background:var(--page-hover);
  display:flex;align-items:center;justify-content:center;
  font-size:42px;color:var(--muted-color);
  margin-bottom:14px
}
.profile-avatar img{width:100%;height:100%;object-fit:cover}
.profile-name{font-weight:700;font-size:1.2rem}
.profile-role{font-size:.8rem;color:var(--primary-color);margin-top:4px}

.profile-social{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
.profile-social a{
  width:36px;height:36px;border-radius:10px;
  background:var(--page-hover);
  display:flex;align-items:center;justify-content:center;
  color:var(--ink)
}

/* Nav */
.profile-nav{margin-top:18px;display:grid;gap:6px}
.profile-nav button{
  border:none;background:transparent;text-align:left;
  padding:8px 10px;border-radius:10px;
  color:var(--ink);font-size:.9rem
}
.profile-nav button:hover,
.profile-nav button.active{
  background:var(--page-hover);
  color:var(--primary-color)
}

/* ===== Content ===== */
.profile-content{display:grid;gap:22px}
.profile-card{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  padding:22px;
  box-shadow:var(--shadow-2)
}
.profile-card h5{
  font-size:1rem;font-weight:700;
  display:flex;gap:10px;align-items:center;margin-bottom:16px
}

/* ===== KV ===== */
.kv{display:grid;grid-template-columns:200px 1fr;gap:10px 16px;font-size:.9rem}
.kv .k{color:var(--muted-color)}
@media(max-width:768px){.kv{grid-template-columns:1fr}}

/* ===== Post Cards ===== */
.post{
  border:1px dashed var(--line-strong);
  border-radius:14px;
  padding:14px;
  display:grid;
  grid-template-columns:120px 1fr;
  gap:14px
}
.post img{
  width:100%;
  height:90px;
  object-fit:cover;
  border-radius:10px;
  border:1px solid var(--line-strong)
}
.post-title{font-weight:600}
.post-meta{font-size:.8rem;color:var(--muted-color)}
.post-desc{font-size:.9rem;margin-top:6px}
@media(max-width:576px){
  .post{grid-template-columns:1fr}
  .post img{height:160px}
}

/* ===== Empty ===== */
.empty{color:var(--muted-color);font-size:.9rem}
</style>
</head>

<body>

<div class="profile-layout">

<!-- ================= SIDEBAR ================= -->
<aside class="profile-sidebar">
  <div class="profile-avatar" id="avatar"><i class="fa fa-user"></i></div>
  <div class="profile-name" id="name">—</div>
  <div class="profile-role" id="role">—</div>

  <div class="profile-social" id="socialIcons"></div>

  <div class="profile-nav">
    <button class="active" data-target="basic">Basic</button>
    <button data-target="personal">Personal</button>
    <button data-target="education">Education</button>
    <button data-target="honors">Honors</button>
    <button data-target="journals">Journals</button>
    <button data-target="conferences">Conferences</button>
    <button data-target="teaching">Teaching</button>
  </div>
</aside>

<!-- ================= CONTENT ================= -->
<main class="profile-content">

<section id="basic" class="profile-card">
  <h5><i class="fa fa-user"></i> Basic Details</h5>
  <div id="basicDetails" class="kv"></div>
</section>

<section id="personal" class="profile-card">
  <h5><i class="fa fa-id-card"></i> Personal Information</h5>
  <div id="personalInfo" class="kv"></div>
</section>

<section id="education" class="profile-card">
  <h5><i class="fa fa-graduation-cap"></i> Education</h5>
  <div id="educations"></div>
</section>

<section id="honors" class="profile-card">
  <h5><i class="fa fa-award"></i> Honors</h5>
  <div id="honors"></div>
</section>

<section id="journals" class="profile-card">
  <h5><i class="fa fa-book"></i> Journals</h5>
  <div id="journalsList"></div>
</section>

<section id="conferences" class="profile-card">
  <h5><i class="fa fa-microphone"></i> Conferences</h5>
  <div id="conferencesList"></div>
</section>

<section id="teaching" class="profile-card">
  <h5><i class="fa fa-chalkboard-teacher"></i> Teaching</h5>
  <div id="teachingList"></div>
</section>

</main>
</div>

<script>
(async ()=>{
  const uuid = location.pathname.split('/').pop();
  const res = await fetch(`/api/users/${uuid}/profile`);
  const {data:d} = await res.json();

  const fixUrl = u => {
    if(!u) return null;
    if(u.startsWith('http')) return u;
    return location.origin + u;
  };

  /* Header */
  name.textContent = d.basic.name;
  role.textContent = d.basic.role.toUpperCase();
  if(d.basic.image){
    avatar.innerHTML = `<img src="${fixUrl(d.basic.image)}">`;
  }

  /* Social */
  (d.social_media||[]).forEach(s=>{
    let icon = 'fa fa-link';
    if(s.icon?.startsWith('fa')) icon = s.icon;
    socialIcons.insertAdjacentHTML('beforeend',
      `<a href="${s.link}" target="_blank" title="${s.platform}">
        <i class="${icon}"></i>
      </a>`
    );
  });

  /* KV */
  const kv=(el,obj)=>Object.entries(obj||{}).forEach(([k,v])=>{
    el.insertAdjacentHTML('beforeend',
      `<div class="k">${k.replace(/_/g,' ')}</div><div>${v??'—'}</div>`
    );
  });
  kv(basicDetails,d.basic);
  kv(personalInfo,d.personal);

  /* Posts */
  const posts=(el,rows,title,meta)=>{
    if(!rows?.length){el.innerHTML=`<div class="empty">No records</div>`;return;}
    rows.forEach(r=>{
      el.insertAdjacentHTML('beforeend',`
        <div class="post">
          ${r.image?`<img src="${fixUrl(r.image)}">`:''}
          <div>
            <div class="post-title">${title(r)}</div>
            <div class="post-meta">${meta(r)}</div>
            ${r.description?`<div class="post-desc">${r.description}</div>`:''}
          </div>
        </div>
      `);
    });
  };

  posts(educations,d.educations,
    r=>r.degree_title||r.education_level,
    r=>`${r.institution_name} • ${r.passing_year||''}`
  );

  posts(honors,d.honors,
    r=>r.title,
    r=>`${r.honouring_organization} • ${r.honor_year}`
  );

  posts(journalsList,d.journals,
    r=>r.title,
    r=>`${r.publication_organization} • ${r.publication_year}`
  );

  posts(conferencesList,d.conference_publications,
    r=>r.title,
    r=>`${r.conference_name} • ${r.publication_year}`
  );

  posts(teachingList,d.teaching_engagements,
    r=>r.organization_name,
    r=>r.domain||''
  );

  /* Sidebar nav */
  document.querySelectorAll('.profile-nav button').forEach(b=>{
    b.onclick=()=>{
      document.querySelectorAll('.profile-nav button').forEach(x=>x.classList.remove('active'));
      b.classList.add('active');
      document.getElementById(b.dataset.target).scrollIntoView({behavior:'smooth'});
    }
  });
})();
</script>

</body>
</html>
