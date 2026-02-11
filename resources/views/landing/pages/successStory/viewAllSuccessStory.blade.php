<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicon/msit_logo.jpg') }}">
    
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
    @include('modules.successStory.viewAllSuccessStory')
</div>


{{-- Footer --}}
@include('landing.components.footer')