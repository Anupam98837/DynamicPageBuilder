<head>
    {{-- ✅ Server-side meta tags (SEO + share friendly) --}}
    @include('landing.components.metaTags')
</head>

{{-- Top Header --}}
@include('landing.components.topHeaderMenu')

{{-- Main Header --}}
@include('landing.components.header')

{{-- Header --}}
@include('landing.components.headerMenu')

{{-- Common UI --}}
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<div>
    @include('modules.faculty.viewAllFaculty')
</div>


{{-- Footer --}}
@include('landing.components.footer')