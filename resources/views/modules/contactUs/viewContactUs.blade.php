{{-- resources/views/contact-us.blade.php --}}
 @section('title','Contact Us')
 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
 <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
 
 @php
 use Illuminate\Support\Facades\DB;
 
 // ✅ IMPORTANT: prevent Google Maps JS API from loading (iframe does NOT need it)
 // Your header/layout should respect this flag.
 $disableGoogleMapsJs = true;
 
 // ✅ read latest visibility settings (NO FK)
 $vis = DB::table('contact_us_page_visibility')->orderByDesc('id')->first();
 
 // ✅ defaults: show all if table has no row yet
 $show_address = (bool) ($vis->show_address ?? true);
 $show_call = (bool) ($vis->show_call ?? true);
 $show_recruitment = (bool) ($vis->show_recruitment ?? true);
 $show_email = (bool) ($vis->show_email ?? true);
 $show_form = (bool) ($vis->show_form ?? true);
 $show_map = (bool) ($vis->show_map ?? true);
 
 $show_info_grid = ($show_address || $show_call || $show_recruitment || $show_email);
 
 /* =========================================================
 | Dynamic Contact Info (Pulled from contact_info table)
 | Mirrors your ContactInfoController publicIndex behavior:
 | active + not deleted, featured first, then sort_order asc
 ========================================================= */
 
 $contactRows = DB::table('contact_info')
 ->whereNull('deleted_at')
 ->where('status', 'active')
 ->where('type', 'contact')
 ->orderByDesc('is_featured_home')
 ->orderBy('sort_order', 'asc')
 ->orderByDesc('id')
 ->get();
 
 $norm = fn($s) => strtolower(trim((string)$s));
 
 $fallbackIconByKey = function(string $key): string {
 $k = strtolower(trim($key));
 
 if (in_array($k, ['address','location','map'], true)) return 'fa-solid fa-location-dot';
 if (in_array($k, ['phone','mobile','tel','telephone','call'], true)) return 'fa-solid fa-phone';
 if ($k === 'whatsapp') return 'fa-brands fa-whatsapp';
 
 if (in_array($k, ['email','mail'], true)) return 'fa-solid fa-envelope-open-text';
 if (str_contains($k, 'recruit') || str_contains($k, 'placement')) return 'fa-solid fa-envelope';
 
 if (in_array($k, ['website','site','url'], true)) return 'fa-solid fa-globe';
 if ($k === 'facebook') return 'fa-brands fa-facebook-f';
 if ($k === 'instagram') return 'fa-brands fa-instagram';
 if ($k === 'linkedin') return 'fa-brands fa-linkedin-in';
 if ($k === 'youtube') return 'fa-brands fa-youtube';
 if ($k === 'twitter' || $k === 'x') return 'fa-brands fa-x-twitter';
 
 return 'fa-solid fa-circle-info';
 };
 
 $toUrl = function(?string $path): ?string {
 $path = trim((string) $path);
 if ($path === '') return null;
 
 if (preg_match('~^https?://~i', $path)) return $path;
 if (str_starts_with($path, '//')) return 'https:' . $path;
 
 return url('/' . ltrim($path, '/'));
 };
 
 $actionUrlFor = function(string $key, string $value) use ($toUrl): ?string {
 $k = strtolower(trim($key));
 $v = trim($value);
 if ($v === '') return null;
 
 // email
 if (in_array($k, ['email','mail'], true)) return 'mailto:' . $v;
 
 // phone
 if (in_array($k, ['phone','mobile','tel','telephone','call'], true)) {
 $clean = preg_replace('~\s+~', '', $v);
 return 'tel:' . $clean;
 }
 
 // whatsapp
 if ($k === 'whatsapp') {
 $digits = preg_replace('~\D+~', '', $v);
 $digits = ltrim($digits, '0');
 return $digits !== '' ? ('https://wa.me/' . $digits) : null;
 }
 
 // address / location
 if (in_array($k, ['address','location','map'], true)) {
 return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($v);
 }
 
 // website / social / any url-ish
 if (
 in_array($k, ['website','site','url','linkedin','facebook','instagram','twitter','x','youtube'], true)
 || preg_match('~^https?://~i', $v)
 || str_starts_with($v, '/')
 || str_starts_with($v, '//')
 ) {
 return $toUrl($v);
 }
 
 return null;
 };
 
 // --- Pick rows for each slot ---
 $addressRow = $contactRows->first(fn($r) => in_array($norm($r->key ?? ''), ['address','location','map'], true));
 $callRow = $contactRows->first(fn($r) => in_array($norm($r->key ?? ''), ['phone','mobile','tel','telephone','call'], true));
 
 // Recruitment: prefer explicit recruit/placement key, else "email" row whose name indicates placement/recruitment
 $recruitRow = $contactRows->first(function($r) use ($norm){
 $k = $norm($r->key ?? '');
 return str_contains($k, 'recruit') || str_contains($k, 'placement') || $k === 'recruitment_email' || $k === 'placement_email';
 });
 
 if (! $recruitRow) {
 $recruitRow = $contactRows->first(function($r) use ($norm){
 $k = $norm($r->key ?? '');
 $n = $norm($r->name ?? '');
 return (in_array($k, ['email','mail'], true)) && (str_contains($n, 'recruit') || str_contains($n, 'placement'));
 });
 }
 
 // General Email: first email/mail that isn't the recruitment row
 $emailRow = $contactRows
 ->filter(fn($r) => in_array($norm($r->key ?? ''), ['email','mail'], true))
 ->first(function($r) use ($recruitRow){
 if (! $recruitRow) return true;
 $rid = $recruitRow->id ?? null;
 return ($r->id ?? null) !== $rid;
 });
 
 // Icons + Titles + Values
 $addressIcon = $addressRow && trim((string)($addressRow->icon_class ?? '')) !== '' ? $addressRow->icon_class : $fallbackIconByKey('address');
 $callIcon = $callRow && trim((string)($callRow->icon_class ?? '')) !== '' ? $callRow->icon_class : $fallbackIconByKey('phone');
 $recruitIcon = $recruitRow && trim((string)($recruitRow->icon_class ?? '')) !== '' ? $recruitRow->icon_class : $fallbackIconByKey('recruitment');
 $emailIcon = $emailRow && trim((string)($emailRow->icon_class ?? '')) !== '' ? $emailRow->icon_class : $fallbackIconByKey('email');
 
 $addressTitle = $addressRow && trim((string)($addressRow->name ?? '')) !== '' ? $addressRow->name : 'Address';
 $callTitle = $callRow && trim((string)($callRow->name ?? '')) !== '' ? $callRow->name : 'Call Us';
 $recruitTitle = $recruitRow && trim((string)($recruitRow->name ?? '')) !== '' ? $recruitRow->name : 'For Campus Recruitment Drive';
 $emailTitle = $emailRow && trim((string)($emailRow->name ?? '')) !== '' ? $emailRow->name : 'Email';
 
 $addressValue = trim((string)($addressRow->value ?? ''));
 $callValue = trim((string)($callRow->value ?? ''));
 $recruitValue = trim((string)($recruitRow->value ?? ''));
 $emailValue = trim((string)($emailRow->value ?? ''));
 
 $callHref = $callRow ? $actionUrlFor((string)($callRow->key ?? ''), $callValue) : null;
 $recruitHref = $recruitRow ? $actionUrlFor((string)($recruitRow->key ?? ''), $recruitValue) : null;
 $emailHref = $emailRow ? $actionUrlFor((string)($emailRow->key ?? ''), $emailValue) : null;
 
 // Map src (prefer explicit map/location row value, else address)
 $mapQuery = trim((string)(
 ($addressRow && trim((string)($addressRow->value ?? '')) !== '' ? $addressRow->value : '') ?: 'Meghnad Saha Institute of Technology Uchhepota Kolkata'
 ));
 
 $mapSrc = 'https://www.google.com/maps?q=' . urlencode($mapQuery) . '&output=embed';
 
 // ✅ Legal authority texts (exact as you asked)
 $legalText1 = 'I agree to the Terms and conditions *';
 $legalText2 = 'I agree to receive communication on newsletters-promotional content-offers an events through SMS-RCS *';
 @endphp
 
 <style>
 /* =========================
 * Contact Us (Public) – UI like reference image
 * ========================= */
 
 :root{
 --contact-accent:#8f2d2f;
 --contact-accent-2:#6f2224;
 --contact-ink:#12212b;
 --contact-muted:#5b6b76;
 --contact-line:#e7eaee;
 --contact-surface:#ffffff;
 }
 
 .cu-wrap{ max-width: 980px; margin: 0 auto; padding: 28px 16px 44px; }
 
 .cu-hero{ text-align:center; padding-top: 6px; }
 .cu-hero h1{ margin:0; font-weight:800; letter-spacing:.2px; color:var(--contact-ink); font-size:34px; }
 .cu-hero p{ margin:8px 0 0; color:var(--contact-muted); font-size:14.5px; }
 
 .cu-info-grid{
 margin-top: 28px;
 display:grid;
 grid-template-columns: 1fr 1fr;
 gap: 22px 54px;
 align-items:start;
 }
 
 .cu-item{ display:flex; gap:14px; align-items:flex-start; }
 
 .cu-icon{
 width:54px; height:54px; border-radius:999px;
 background: var(--contact-accent);
 display:flex; align-items:center; justify-content:center;
 flex:0 0 54px;
 box-shadow: 0 10px 18px rgba(143,45,47,.15);
 }
 .cu-icon i{ color:#fff; font-size:18px; }
 
 .cu-item h4{ margin:0; font-weight:800; color:var(--contact-ink); font-size:18px; }
 .cu-item .cu-text{ margin-top:6px; color:#3b4a55; line-height:1.5; font-size:14.5px; }
 .cu-item .cu-link{ color:var(--contact-accent); text-decoration:none; font-weight:700; }
 .cu-item .cu-link:hover{ color:var(--contact-accent-2); text-decoration:underline; }
 .cu-item .cu-muted{ color: var(--contact-muted); }
 
 .cu-form-wrap{
 margin-top:26px;
 background: var(--contact-surface);
 border:1px solid var(--contact-line);
 border-radius:16px;
 padding:18px;
 box-shadow: 0 14px 30px rgba(16, 24, 40, .06);
 }
 .cu-form-head{
 display:flex; align-items:flex-start; justify-content:space-between;
 gap:12px; margin-bottom:12px;
 }
 .cu-form-head h3{ margin:0; font-weight:900; color:var(--contact-ink); font-size:18px; }
 .cu-form-head p{ margin:4px 0 0; color:var(--contact-muted); font-size:13.5px; }
 
 .cu-form{ margin-top:10px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
 .cu-form .full{ grid-column: 1 / -1; }
 .cu-form label{ display:block; font-weight:800; color:var(--contact-ink); font-size:13px; margin:0 0 6px; }
 .cu-form input, .cu-form textarea{
 width:100%;
 border:1px solid var(--contact-line);
 border-radius:12px;
 padding:11px 12px;
 font-size:14px;
 outline:none;
 background:#fff;
 }
 .cu-form textarea{ min-height:120px; resize:vertical; }
 .cu-form input:focus, .cu-form textarea:focus{
 border-color: rgba(143,45,47,.55);
 box-shadow: 0 0 0 3px rgba(143,45,47,.15);
 }
 
 .cu-consent{
 grid-column: 1 / -1;
 margin-top: 6px;
 padding-top: 8px;
 border-top: 1px dashed rgba(15,23,42,.12);
 display:flex;
 flex-direction:column;
 gap:10px;
 }
 .cu-check{
 display:flex;
 gap:10px;
 align-items:flex-start;
 font-size:13.5px;
 color:#2f3d46;
 line-height:1.45;
 }
 .cu-check input{
 width:18px; height:18px;
 margin-top:2px;
 accent-color: var(--contact-accent);
 flex:0 0 auto;
 }
 .cu-check b{ font-weight:900; color:var(--contact-ink); }
 
 .cu-actions{
 grid-column: 1 / -1;
 display:flex; gap:10px; align-items:center; justify-content:flex-start;
 margin-top:4px;
 }
 .cu-btn{
 border:none;
 background: var(--contact-accent);
 color:#fff;
 padding:11px 18px;
 border-radius:12px;
 font-weight:900;
 cursor:pointer;
 display:inline-flex;
 align-items:center;
 gap:8px;
 }
 .cu-btn:hover{ background: var(--contact-accent-2); }
 .cu-note{ color:var(--contact-muted); font-size:13px; }
 
 /* ✅ Disabled submit styling (still same button, but clearly disabled) */
 .cu-btn:disabled{
 opacity:.55;
 cursor:not-allowed;
 filter: grayscale(.05);
 }
 
 .cu-find{ margin-top:26px; text-align:center; }
 .cu-find h2{ margin:0 0 12px; font-weight:900; color:var(--contact-ink); font-size:24px; }
 
 .cu-map{
 border:1px solid var(--contact-line);
 border-radius:16px;
 overflow:hidden;
 box-shadow: 0 14px 30px rgba(16, 24, 40, .06);
 background:#fff;
 }
 .cu-map iframe{ width:100%; height:320px; border:0; display:block; }
 
 @media(max-width: 900px){
 .cu-info-grid{ grid-template-columns:1fr; gap:18px; }
 .cu-form{ grid-template-columns:1fr; }
 .cu-hero h1{ font-size:30px; }
 }
 </style>
 
 <div class="cu-wrap">
 
 <div class="cu-hero">
 <h1>Contact Us</h1>
 <p>Excellence in Technical Education</p>
 </div>
 
 @if($show_info_grid)
 <div class="cu-info-grid">
 
 {{-- Address --}}
 @if($show_address)
 <div class="cu-item">
 <div class="cu-icon"><i class="{{ $addressIcon }}"></i></div>
 <div>
 <h4>{{ $addressTitle }}</h4>
 <div class="cu-text">
 @if($addressValue !== '')
 {!! nl2br(e($addressValue)) !!}
 @else
 <span class="cu-muted">Not available</span>
 @endif
 </div>
 </div>
 </div>
 @endif
 
 {{-- Call --}}
 @if($show_call)
 <div class="cu-item">
 <div class="cu-icon"><i class="{{ $callIcon }}"></i></div>
 <div>
 <h4>{{ $callTitle }}</h4>
 <div class="cu-text">
 @if($callValue !== '' && $callHref)
 <a class="cu-link" href="{{ $callHref }}">{{ $callValue }}</a>
 @elseif($callValue !== '')
 <span class="cu-muted">{{ $callValue }}</span>
 @else
 <span class="cu-muted">Not available</span>
 @endif
 </div>
 </div>
 </div>
 @endif
 
 {{-- Recruitment --}}
 @if($show_recruitment)
 <div class="cu-item">
 <div class="cu-icon"><i class="{{ $recruitIcon }}"></i></div>
 <div>
 <h4>{{ $recruitTitle }}</h4>
 <div class="cu-text">
 @if($recruitValue !== '' && $recruitHref)
 <a class="cu-link" href="{{ $recruitHref }}">{{ $recruitValue }}</a>
 @elseif($recruitValue !== '')
 <span class="cu-muted">{{ $recruitValue }}</span>
 @else
 <span class="cu-muted">Not available</span>
 @endif
 </div>
 </div>
 </div>
 @endif
 
 {{-- Email --}}
 @if($show_email)
 <div class="cu-item">
 <div class="cu-icon"><i class="{{ $emailIcon }}"></i></div>
 <div>
 <h4>{{ $emailTitle }}</h4>
 <div class="cu-text">
 @if($emailValue !== '' && $emailHref)
 <a class="cu-link" href="{{ $emailHref }}">{{ $emailValue }}</a>
 @elseif($emailValue !== '')
 <span class="cu-muted">{{ $emailValue }}</span>
 @else
 <span class="cu-muted">Not available</span>
 @endif
 </div>
 </div>
 </div>
 @endif
 
 </div>
 @endif
 
 {{-- Form --}}
 @if($show_form)
 <div class="cu-form-wrap">
 <div class="cu-form-head">
 <div>
 <h3>Send a Message</h3>
 <p>Fill the form and we’ll get back to you as soon as possible.</p>
 </div>
 </div>
 
 <form id="contactForm" class="cu-form">
 <div>
 <label for="first_name">First Name *</label>
 <input id="first_name" type="text" placeholder="Your first name" required>
 </div>
 
 <div>
 <label for="last_name">Last Name</label>
 <input id="last_name" type="text" placeholder="Your last name (optional)">
 </div>
 
 <div class="full">
 <label for="email">Email *</label>
 <input id="email" type="email" placeholder="your@email.com" required>
 </div>
 
 <div class="full">
 <label for="phone">Phone</label>
 <input id="phone" type="text" placeholder="Your phone number (optional)">
 </div>
 
 <div class="full">
 <label for="message">Message *</label>
 <textarea id="message" placeholder="Write your message..." required></textarea>
 </div>
 
 {{-- ✅ Consent checkboxes (must be checked to enable submit) --}}
 <div class="cu-consent">
 <label class="cu-check" for="consent_terms">
 <input id="consent_terms" type="checkbox">
 <span>{{ $legalText1 }}</span>
 </label>
 
 <label class="cu-check" for="consent_promotions">
 <input id="consent_promotions" type="checkbox">
 <span>{{ $legalText2 }}</span>
 </label>
 </div>
 
 <div class="cu-actions">
 <button id="submitBtn" class="cu-btn" type="submit" disabled>
 <i class="fa-solid fa-paper-plane"></i> Send Message
 </button>
 <span class="cu-note">We never share your details.</span>
 </div>
 </form>
 </div>
 @endif
 
 {{-- Map --}}
 @if($show_map)
 <div class="cu-find">
 <h2>Find Us</h2>
 <div class="cu-map">
 <iframe
 src="{{ $mapSrc }}"
 loading="lazy"
 referrerpolicy="no-referrer-when-downgrade">
 </iframe>
 </div>
 </div>
 @endif
 
 </div>
 
 {{-- Submit form --}}
 @if($show_form)
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script>
 (function(){
 const form = document.getElementById('contactForm');
 const btn = document.getElementById('submitBtn');
 
 const firstNameEl = document.getElementById('first_name');
 const lastNameEl = document.getElementById('last_name');
 const emailEl = document.getElementById('email');
 const phoneEl = document.getElementById('phone');
 const msgEl = document.getElementById('message');
 
 const termsEl = document.getElementById('consent_terms');
 const promoEl = document.getElementById('consent_promotions');
 
 // ✅ texts must be posted as discussed
 const LEGAL_TEXT_1 = @json($legalText1);
 const LEGAL_TEXT_2 = @json($legalText2);
 
 function canSubmit(){
 const okRequired = firstNameEl.value.trim() && emailEl.value.trim() && msgEl.value.trim();
 const okConsent = termsEl.checked && promoEl.checked;
 return !!(okRequired && okConsent);
 }
 
 function syncBtn(){
 btn.disabled = !canSubmit();
 }
 
 // enable/disable submit live
 [firstNameEl, emailEl, msgEl, termsEl, promoEl, lastNameEl, phoneEl].forEach(el => {
 el.addEventListener('input', syncBtn);
 el.addEventListener('change', syncBtn);
 });
 syncBtn();
 
 form.addEventListener('submit', async function(e){
 e.preventDefault();
 
 const first_name = firstNameEl.value.trim();
 const last_name = lastNameEl.value.trim();
 const email = emailEl.value.trim();
 const phone = phoneEl.value.trim();
 const message = msgEl.value.trim();
 
 if(!first_name || !email || !message){
 Swal.fire('Error','Please fill all required fields','error');
 return;
 }
 
 // ✅ Hard requirement: both must be checked, otherwise blocked
 if(!(termsEl.checked && promoEl.checked)){
 Swal.fire('Error','Please accept the required agreements to continue.','error');
 syncBtn();
 return;
 }
 
 // ✅ payload updated (first_name/last_name + legal_authority_json)
 const legal_authority_json = [
 { key: 'terms', text: LEGAL_TEXT_1, accepted: true },
 { key: 'promotions', text: LEGAL_TEXT_2, accepted: true },
 ];
 
 const payload = {
 first_name,
 last_name: (last_name !== '' ? last_name : null),
 email,
 phone: (phone !== '' ? phone : null),
 message,
 legal_authority_json
 };
 
 try{
 btn.disabled = true;
 btn.style.opacity = '.85';
 
 const res = await fetch('/api/contact-us', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'Accept': 'application/json',
 },
 body: JSON.stringify(payload)
 });
 
 const data = await res.json().catch(() => ({}));
 
 if(res.ok){
 Swal.fire('Success','Message sent successfully','success');
 form.reset();
 
 // ✅ FIX: force-disable immediately after reset
 btn.disabled = true;
 
 syncBtn(); // keeps it disabled until required fields + both consents are checked again
 }else{
 let msg = data.message || 'Validation failed';
 
 // Laravel 422 errors pretty message
 if (data.errors && typeof data.errors === 'object') {
 const k = Object.keys(data.errors)[0];
 if (k && Array.isArray(data.errors[k]) && data.errors[k][0]) {
 msg = data.errors[k][0];
 }
 }
 
 Swal.fire('Error', msg, 'error');
 console.error(data);
 syncBtn();
 }
 }catch(err){
 console.error(err);
 Swal.fire('Error','Something went wrong. Please try again.','error');
 syncBtn();
 }finally{
 btn.style.opacity = '1';
 // do not force enable; keep it tied to consent + required fields
 syncBtn();
 }
 });
 })();
 </script>
 @endif
