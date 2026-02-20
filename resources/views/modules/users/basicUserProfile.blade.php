{{-- resources/views/landing/userBasicProfile.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Basic Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

    <style>
        :root{
            --bp-surface: var(--surface, #ffffff);
            --bp-surface-alt: #f8fafc;
            --bp-ink: var(--ink, #0f172a);
            --bp-muted: var(--muted-color, #64748b);
            --bp-line: var(--line-strong, #e2e8f0);
            --bp-line-soft: rgba(148,163,184,.22);
            --bp-brand: var(--primary-color, #9E363A);
            --bp-brand-soft: rgba(158,54,58,.10);
            --bp-shadow: 0 10px 24px rgba(2,6,23,.08);
            --bp-shadow-lg: 0 16px 34px rgba(2,6,23,.12);
            --bp-radius: 18px;
        }

        body{
            background: var(--bg-body, #f8fafc);
            color: var(--bp-ink);
        }

        .bp-page{
            max-width: 1320px;
            margin: 0 auto;
            padding: 18px 12px 40px;
        }

        .bp-layout{
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 991.98px){
            .bp-layout{ grid-template-columns: 1fr; }
        }

        /* Sidebar */
        .bp-side{
            position: sticky;
            top: 16px;
            background: var(--bp-surface);
            border: 1px solid var(--bp-line);
            border-radius: 20px;
            box-shadow: var(--bp-shadow);
            padding: 18px;
            max-height: calc(100vh - 32px);
            overflow: auto;
        }

        .bp-side::-webkit-scrollbar{ width: 8px; }
        .bp-side::-webkit-scrollbar-thumb{
            background: rgba(100,116,139,.35);
            border-radius: 999px;
        }

        .bp-avatar-wrap{
            position: relative;
            width: 126px;
            height: 126px;
            margin: 2px auto 14px;
        }

        .bp-avatar{
            width: 100%;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            background:
              radial-gradient(100px 100px at 25% 15%, rgba(158,54,58,.12), transparent 65%),
              linear-gradient(180deg, rgba(2,6,23,.03), rgba(2,6,23,.06));
            border: 4px solid #fff;
            box-shadow: 0 12px 26px rgba(2,6,23,.12);
            display: grid;
            place-items: center;
            color: var(--bp-brand);
            font-size: 38px;
        }

        .bp-avatar img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .bp-badge{
            position: absolute;
            right: -4px;
            bottom: -4px;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 3px solid #fff;
            background: var(--bp-brand);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 13px;
            box-shadow: 0 8px 18px rgba(158,54,58,.25);
        }

        .bp-name{
            text-align: center;
            margin: 0;
            font-weight: 900;
            font-size: 1.1rem;
            color: var(--bp-ink);
            line-height: 1.35;
            word-break: break-word;
        }

        .bp-role{
            margin: 8px auto 10px;
            width: fit-content;
            max-width: 100%;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--bp-brand-soft);
            color: var(--bp-brand);
            border: 1px solid rgba(158,54,58,.18);
            font-weight: 800;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .3px;
            text-align: center;
        }

        .bp-subchips{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 14px;
        }

        .bp-chip{
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid var(--bp-line);
            background: var(--bp-surface-alt);
            color: var(--bp-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .bp-contact{
            border: 1px solid var(--bp-line);
            background: var(--bp-surface-alt);
            border-radius: 14px;
            padding: 12px;
            display: grid;
            gap: 10px;
        }

        .bp-contact-item{
            display: grid;
            grid-template-columns: 18px 1fr;
            gap: 10px;
            align-items: start;
            font-size: .9rem;
            color: var(--bp-ink);
            line-height: 1.45;
        }

        .bp-contact-item i{
            color: var(--bp-brand);
            margin-top: 2px;
        }

        .bp-contact-item a{
            color: #1d4ed8;
            text-decoration: none;
            word-break: break-word;
        }
        .bp-contact-item a:hover{ text-decoration: underline; }

        .bp-social{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-top: 14px;
        }

        .bp-social a{
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid var(--bp-line);
            background: var(--bp-surface-alt);
            color: var(--bp-ink);
            display: grid;
            place-items: center;
            text-decoration: none;
            transition: .18s ease;
        }

        .bp-social a:hover{
            transform: translateY(-2px);
            background: var(--bp-brand);
            color: #fff;
            border-color: rgba(158,54,58,.35);
            box-shadow: 0 10px 18px rgba(158,54,58,.22);
        }

        .bp-nav{
            margin-top: 16px;
            display: grid;
            gap: 8px;
        }

        .bp-nav button{
            border: 1px solid transparent;
            background: transparent;
            color: var(--bp-ink);
            padding: 10px 12px;
            border-radius: 12px;
            text-align: left;
            font-weight: 700;
            font-size: .92rem;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: .18s ease;
        }

        .bp-nav button i{ width: 18px; color: var(--bp-muted); }
        .bp-nav button:hover{
            background: var(--bp-brand-soft);
            color: var(--bp-brand);
            border-color: rgba(158,54,58,.16);
        }
        .bp-nav button.active{
            background: var(--bp-brand);
            color: #fff;
            box-shadow: 0 10px 18px rgba(158,54,58,.18);
        }
        .bp-nav button.active i{ color: #fff; }

        .bp-scroll-hint{
            margin-top: 12px;
            display: none;
            justify-content: center;
            pointer-events: none;
        }
        .bp-scroll-hint .pill{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.92);
            border: 1px solid var(--bp-line);
            border-radius: 999px;
            padding: 6px 10px;
            color: var(--bp-muted);
            font-size: 12px;
            box-shadow: 0 10px 18px rgba(2,6,23,.08);
        }
        .bp-scroll-hint i{ animation: bpBounce 1.1s infinite; }
        @keyframes bpBounce{
            0%,100%{ transform: translateY(0); }
            50%{ transform: translateY(3px); }
        }

        /* Main */
        .bp-main{
            min-height: 420px;
            position: relative;
        }

        .bp-loading{
            background: var(--bp-surface);
            border: 1px solid var(--bp-line);
            border-radius: 20px;
            box-shadow: var(--bp-shadow);
            padding: 36px 18px;
            text-align: center;
            color: var(--bp-muted);
        }

        .bp-spinner{
            width: 38px;
            height: 38px;
            margin: 0 auto 12px;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            border-top-color: var(--bp-brand);
            animation: bpSpin 1s linear infinite;
        }
        @keyframes bpSpin{ to{ transform: rotate(360deg);} }

        .bp-error{
            display: none;
            background: #fff;
            border: 1px solid rgba(239,68,68,.22);
            color: #991b1b;
            border-radius: 16px;
            padding: 16px;
            box-shadow: var(--bp-shadow);
        }

        .bp-stack{
            display: none;
            gap: 16px;
        }

        .bp-card{
            background: var(--bp-surface);
            border: 1px solid var(--bp-line);
            border-radius: 20px;
            box-shadow: var(--bp-shadow);
            padding: 18px;
        }

        .bp-card h5{
            margin: 0 0 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--bp-line);
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--bp-brand);
            font-weight: 900;
            font-size: 1rem;
        }

        .bp-card h5 i{
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--bp-brand-soft);
            border: 1px solid rgba(158,54,58,.14);
        }

        .bp-kv{
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px 18px;
            align-items: start;
        }

        .bp-k{
            color: var(--bp-muted);
            font-weight: 700;
            font-size: .88rem;
            text-transform: uppercase;
            letter-spacing: .25px;
        }

        .bp-v{
            color: var(--bp-ink);
            font-size: .94rem;
            line-height: 1.6;
            word-break: break-word;
        }

        .bp-v a{
            color: #1d4ed8;
            text-decoration: none;
        }
        .bp-v a:hover{ text-decoration: underline; }

        .bp-divider{
            grid-column: 1 / -1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(148,163,184,.35), transparent);
            margin: 2px 0;
        }

        @media (max-width: 767.98px){
            .bp-kv{
                grid-template-columns: 1fr;
                gap: 6px 0;
            }
            .bp-k{
                color: var(--bp-ink);
                font-size: .84rem;
            }
            .bp-divider{ margin: 6px 0; }
        }

        .bp-tags{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bp-tag{
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--bp-brand-soft);
            border: 1px solid rgba(158,54,58,.16);
            color: var(--bp-brand);
            font-weight: 700;
            font-size: .82rem;
        }

        .bp-grid-3{
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 12px;
        }

        @media (max-width: 991.98px){
            .bp-grid-3{ grid-template-columns: 1fr; }
        }

        .bp-mini{
            border: 1px solid var(--bp-line);
            border-radius: 14px;
            padding: 12px;
            background: var(--bp-surface-alt);
        }

        .bp-mini .lbl{
            font-size: .82rem;
            color: var(--bp-muted);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .bp-mini .val{
            font-size: 1.05rem;
            font-weight: 900;
            color: var(--bp-ink);
            line-height: 1.35;
        }

        .bp-empty{
            color: var(--bp-muted);
            font-style: italic;
        }

        .bp-about{
            color: var(--bp-ink);
            line-height: 1.75;
            font-size: .95rem;
        }

        .bp-about p{ margin: 0 0 10px; }
        .bp-about ul{ margin: 0 0 10px 18px; }
    </style>
</head>
<body>

@include('landing.components.header')
@include('landing.components.headerMenu')

<div class="bp-page">
    <div class="bp-layout">
        <!-- Sidebar -->
        <aside class="bp-side" id="bpSidebar">
            <div class="bp-avatar-wrap">
                <div class="bp-avatar" id="bpAvatar">
                    <i class="fa fa-user"></i>
                </div>
                <div class="bp-badge"><i class="fa fa-check"></i></div>
            </div>

            <h2 class="bp-name" id="bpName">—</h2>
            <div class="bp-role" id="bpRole">—</div>

            <div class="bp-subchips" id="bpChips"></div>

            <div class="bp-contact" id="bpContact">
                <div class="bp-contact-item"><i class="fa fa-envelope"></i><span>—</span></div>
                <div class="bp-contact-item"><i class="fa fa-phone"></i><span>—</span></div>
                <div class="bp-contact-item"><i class="fa fa-location-dot"></i><span>—</span></div>
            </div>

            <div class="bp-social" id="bpSocial"></div>

            <div class="bp-nav" id="bpNav">
                <button class="active" data-target="bpOverview"><i class="fa fa-user"></i> Overview</button>
                <button data-target="bpBasic"><i class="fa fa-id-card"></i> Basic Details</button>
                <button data-target="bpPersonal"><i class="fa fa-circle-info"></i> Personal Info</button>
                <button data-target="bpStats"><i class="fa fa-chart-simple"></i> Academic Snapshot</button>
            </div>

            <div class="bp-scroll-hint" id="bpScrollHint" aria-hidden="true">
                <div class="pill"><i class="fa fa-arrow-down"></i></div>
            </div>
        </aside>

        <!-- Main -->
        <main class="bp-main">
            <div class="bp-loading" id="bpLoading">
                <div class="bp-spinner"></div>
                <div>Loading basic profile...</div>
            </div>

            <div class="bp-error" id="bpError"></div>

            <div class="bp-stack" id="bpContentStack">
                <!-- Overview -->
                <section class="bp-card" id="bpOverview">
                    <h5><i class="fa fa-user"></i> Overview</h5>
                    <div class="bp-about" id="bpOverviewHtml"></div>
                </section>

                <!-- Basic details -->
                <section class="bp-card" id="bpBasic">
                    <h5><i class="fa fa-id-card"></i> Basic Details</h5>
                    <div class="bp-kv" id="bpBasicKv"></div>
                </section>

                <!-- Personal details -->
                <section class="bp-card" id="bpPersonal">
                    <h5><i class="fa fa-circle-info"></i> Personal Information</h5>
                    <div class="bp-kv" id="bpPersonalKv"></div>
                </section>

                <!-- Stats -->
                <section class="bp-card" id="bpStats">
                    <h5><i class="fa fa-chart-simple"></i> Academic Snapshot</h5>
                    <div class="bp-grid-3" id="bpStatsGrid"></div>
                </section>
            </div>
        </main>
    </div>
</div>

<script>
(() => {
    // Same API (as requested)
    const API_BASE = @json(url('/api'));
    const profileData = { raw: null };

    const el = {
        loading: document.getElementById('bpLoading'),
        error: document.getElementById('bpError'),
        stack: document.getElementById('bpContentStack'),

        avatar: document.getElementById('bpAvatar'),
        name: document.getElementById('bpName'),
        role: document.getElementById('bpRole'),
        chips: document.getElementById('bpChips'),
        contact: document.getElementById('bpContact'),
        social: document.getElementById('bpSocial'),

        overviewHtml: document.getElementById('bpOverviewHtml'),
        basicKv: document.getElementById('bpBasicKv'),
        personalKv: document.getElementById('bpPersonalKv'),
        statsGrid: document.getElementById('bpStatsGrid'),

        nav: document.getElementById('bpNav'),
        sidebar: document.getElementById('bpSidebar'),
        scrollHint: document.getElementById('bpScrollHint'),
    };

    function esc(v){
        return String(v ?? '').replace(/[&<>"']/g, s => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[s]));
    }

    function safeUrl(v){
        const s = String(v ?? '').trim();
        if (!s) return '';
        if (/^javascript:/i.test(s)) return '';
        try { return new URL(s, window.location.origin).toString(); }
        catch(e){ return ''; }
    }

    function textOrDash(v){
        const s = String(v ?? '').trim();
        return s ? s : '—';
    }

    function withBr(v){
        const s = String(v ?? '').trim();
        if (!s) return '—';
        return esc(s).replace(/\n/g, '<br>');
    }

    function richText(v){
        const s = String(v ?? '').trim();
        if (!s) return '<span class="bp-empty">—</span>';

        // minimal safe cleanup (strip scripts/iframes, keep simple formatting)
        let out = s
            .replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '')
            .replace(/<iframe[\s\S]*?>[\s\S]*?<\/iframe>/gi, '')
            .replace(/on\w+="[^"]*"/gi, '')
            .replace(/on\w+='[^']*'/gi, '');

        return out;
    }

    function parseMaybeJson(v){
        if (v == null) return null;
        if (Array.isArray(v) || typeof v === 'object') return v;
        try { return JSON.parse(String(v)); } catch(e){ return null; }
    }

    function pick(obj, keys){
        for (const k of keys){
            const val = obj?.[k];
            if (val !== null && val !== undefined && String(val).trim() !== '') return val;
        }
        return '';
    }

    function socialIcon(platform){
        const p = String(platform || '').toLowerCase().trim();
        if (p.includes('linkedin')) return 'fa-brands fa-linkedin-in';
        if (p.includes('github')) return 'fa-brands fa-github';
        if (p.includes('orcid')) return 'fa-brands fa-orcid';
        if (p.includes('researchgate')) return 'fa-brands fa-researchgate';
        if (p.includes('google') || p.includes('scholar')) return 'fa-solid fa-graduation-cap';
        if (p.includes('twitter') || p === 'x') return 'fa-brands fa-x-twitter';
        if (p.includes('facebook')) return 'fa-brands fa-facebook-f';
        if (p.includes('instagram')) return 'fa-brands fa-instagram';
        if (p.includes('youtube')) return 'fa-brands fa-youtube';
        return 'fa-solid fa-link';
    }

    function getUuidFromPath(){
        const segs = window.location.pathname.split('/').filter(Boolean);
        return segs.length ? segs[segs.length - 1] : '';
    }

    function showLoading(show){
        el.loading.style.display = show ? '' : 'none';
        if (show){
            el.error.style.display = 'none';
            el.stack.style.display = 'none';
        }
    }

    function showError(msg){
        el.loading.style.display = 'none';
        el.stack.style.display = 'none';
        el.error.style.display = 'block';
        el.error.innerHTML = `<i class="fa fa-triangle-exclamation me-2"></i>${esc(msg || 'Unable to load profile.')}`;
    }

    function renderSidebar(data){
        const d = data.basic || {};
        const p = data.personal || {};
        const meta = parseMaybeJson(p?.metadata) || {};

        el.name.textContent = textOrDash(d.name);
        el.role.textContent = textOrDash(d.role).toUpperCase();

        // avatar
        const img = safeUrl(d.image);
        if (img){
            el.avatar.innerHTML = `<img src="${esc(img)}" alt="${esc(d.name || 'User')}">`;
        } else {
            el.avatar.innerHTML = `<i class="fa fa-user"></i>`;
        }

        // chips
        const chipItems = [];
        const designation = textOrDash(pick(p, ['designation']));
        const department  = textOrDash(pick(d, ['department_name','department','department_title']));
        if (designation !== '—') chipItems.push({icon:'fa-briefcase', text: designation});
        if (department !== '—') chipItems.push({icon:'fa-building-columns', text: department});

        el.chips.innerHTML = chipItems.map(c =>
            `<span class="bp-chip"><i class="fa ${esc(c.icon)}"></i>${esc(c.text)}</span>`
        ).join('');

        // contact
        const email = textOrDash(d.email);
        const phone = textOrDash(pick(d, ['phone_number']));
        const addr  = textOrDash(String(d.address || '').replace(/\n/g, ', '));

        el.contact.innerHTML = `
            <div class="bp-contact-item">
                <i class="fa fa-envelope"></i>
                <span>${email !== '—' ? `<a href="mailto:${esc(email)}">${esc(email)}</a>` : '—'}</span>
            </div>
            <div class="bp-contact-item">
                <i class="fa fa-phone"></i>
                <span>${phone !== '—' ? `<a href="tel:${esc(phone)}">${esc(phone)}</a>` : '—'}</span>
            </div>
            <div class="bp-contact-item">
                <i class="fa fa-location-dot"></i>
                <span>${esc(addr)}</span>
            </div>
        `;

        // socials
        const socials = Array.isArray(data.social_media) ? data.social_media : [];
        el.social.innerHTML = socials
            .filter(s => safeUrl(s.link))
            .map(s => `
                <a href="${esc(safeUrl(s.link))}" target="_blank" rel="noopener noreferrer" title="${esc(s.platform || 'Link')}">
                    <i class="${esc(socialIcon(s.platform))}"></i>
                </a>
            `).join('');

        setupSidebarScrollHint();
    }

    function kvHtml(rows){
        return rows.map((r, idx) => `
            <div class="bp-k">${esc(r.label)}</div>
            <div class="bp-v">${r.html}</div>
            ${idx < rows.length - 1 ? `<div class="bp-divider" aria-hidden="true"></div>` : ``}
        `).join('');
    }

    function renderOverview(data){
        const d = data.basic || {};
        const p = data.personal || {};

        const aboutBlocks = [];

        // Affiliation / Bio / Note style
        if (p.affiliation) aboutBlocks.push(`<p><strong>Affiliation:</strong> ${richText(p.affiliation)}</p>`);
        if (p.specification) aboutBlocks.push(`<p><strong>Specification:</strong> ${richText(p.specification)}</p>`);
        if (p.interest) aboutBlocks.push(`<p><strong>Research Interests:</strong> ${richText(p.interest)}</p>`);

        if (!aboutBlocks.length){
            aboutBlocks.push(`<p class="bp-empty">No profile summary available.</p>`);
        }

        el.overviewHtml.innerHTML = aboutBlocks.join('');

        // basic details
        const createdAt = d.created_at
            ? new Date(d.created_at).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' })
            : '—';

        const basicRows = [
            { label:'Name', html: esc(textOrDash(d.name)) },
            { label:'Email', html: d.email ? `<a href="mailto:${esc(d.email)}">${esc(d.email)}</a>` : '—' },
            { label:'Phone', html: d.phone_number ? `<a href="tel:${esc(d.phone_number)}">${esc(d.phone_number)}</a>` : '—' },
            { label:'Alternative Email', html: d.alternative_email ? `<a href="mailto:${esc(d.alternative_email)}">${esc(d.alternative_email)}</a>` : '—' },
            { label:'Alternative Phone', html: esc(textOrDash(d.alternative_phone_number)) },
            { label:'WhatsApp', html: esc(textOrDash(d.whatsapp_number)) },
            { label:'Address', html: withBr(d.address) },
            { label:'Role', html: esc(textOrDash(d.role)) },
            { label:'Status', html: esc(textOrDash(d.status)) },
            { label:'Member Since', html: esc(createdAt) }
        ];
        el.basicKv.innerHTML = kvHtml(basicRows);

        // personal details
        const qualification = Array.isArray(p.qualification) ? p.qualification : (parseMaybeJson(p.qualification) || []);
        const qHtml = qualification.length
            ? `<div class="bp-tags">${qualification.map(q => `<span class="bp-tag">${esc(q)}</span>`).join('')}</div>`
            : '<span class="bp-empty">—</span>';

        const personalRows = [
            { label:'Qualifications', html: qHtml },
            { label:'Affiliation', html: richText(p.affiliation || '—') },
            { label:'Specification', html: richText(p.specification || '—') },
            { label:'Experience', html: richText(p.experience || '—') },
            { label:'Research Interests', html: richText(p.interest || '—') },
            { label:'Administration', html: richText(p.administration || '—') },
            { label:'Research Projects', html: richText(p.research_project || '—') },
        ];
        el.personalKv.innerHTML = kvHtml(personalRows);
    }

    function renderStats(data){
        const educations = Array.isArray(data.educations) ? data.educations : [];
        const honors = Array.isArray(data.honors) ? data.honors : [];
        const journals = Array.isArray(data.journals) ? data.journals : [];
        const conferences = Array.isArray(data.conference_publications) ? data.conference_publications : [];
        const teaching = Array.isArray(data.teaching_engagements) ? data.teaching_engagements : [];

        const latestEdu = educations[0] || null;
        const latestEduText = latestEdu
            ? [latestEdu.degree_title || latestEdu.education_level, latestEdu.institution_name || latestEdu.university_name]
                .filter(Boolean).join(' • ')
            : 'No education record';

        el.statsGrid.innerHTML = `
            <div class="bp-mini">
                <div class="lbl">Latest Education</div>
                <div class="val">${esc(latestEduText)}</div>
            </div>
            <div class="bp-mini">
                <div class="lbl">Honors / Awards</div>
                <div class="val">${honors.length}</div>
            </div>
            <div class="bp-mini">
                <div class="lbl">Journal Publications</div>
                <div class="val">${journals.length}</div>
            </div>
            <div class="bp-mini">
                <div class="lbl">Conference Publications</div>
                <div class="val">${conferences.length}</div>
            </div>
            <div class="bp-mini">
                <div class="lbl">Teaching Engagements</div>
                <div class="val">${teaching.length}</div>
            </div>
            <div class="bp-mini">
                <div class="lbl">Profile Status</div>
                <div class="val">${esc(textOrDash(data.basic?.status || '—'))}</div>
            </div>
        `;
    }

    function renderAll(data){
        renderSidebar(data);
        renderOverview(data);
        renderStats(data);

        el.error.style.display = 'none';
        el.stack.style.display = 'grid';
        el.loading.style.display = 'none';

        setupSectionNav();
    }

    function setupSectionNav(){
        const buttons = el.nav.querySelectorAll('button[data-target]');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const target = document.getElementById(targetId);
                if (!target) return;

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // active state on scroll
        const sectionIds = Array.from(buttons).map(b => b.getAttribute('data-target'));
        const sections = sectionIds.map(id => document.getElementById(id)).filter(Boolean);

        const onScroll = () => {
            let activeId = sectionIds[0];
            const offset = 120;

            sections.forEach(sec => {
                const rect = sec.getBoundingClientRect();
                if (rect.top - offset <= 0) activeId = sec.id;
            });

            buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-target') === activeId));
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    function setupSidebarScrollHint(){
        const sidebar = el.sidebar;
        const hint = el.scrollHint;
        if (!sidebar || !hint) return;

        const update = () => {
            const canScroll = sidebar.scrollHeight > sidebar.clientHeight + 4;
            const atBottom = (sidebar.scrollTop + sidebar.clientHeight) >= (sidebar.scrollHeight - 4);
            hint.style.display = (canScroll && !atBottom) ? 'flex' : 'none';
        };

        update();
        sidebar.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);

        const mo = new MutationObserver(() => setTimeout(update, 40));
        mo.observe(sidebar, { childList: true, subtree: true });
    }

    async function init(){
        const uuid = getUuidFromPath();

        if (!uuid){
            showError('Invalid profile URL.');
            return;
        }

        showLoading(true);

        try{
            // Same API endpoint as your full profile page
            const res = await fetch(`${API_BASE}/users/${encodeURIComponent(uuid)}/profile`, {
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json?.message || json?.error || `Request failed: ${res.status}`);

            profileData.raw = json?.data || {};
            renderAll(profileData.raw);
        } catch(err){
            console.error(err);
            showError(err?.message || 'Failed to load profile data.');
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();
</script>

</body>
</html>
