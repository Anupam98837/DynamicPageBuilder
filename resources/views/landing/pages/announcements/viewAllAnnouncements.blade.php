{{-- Top Header --}}
@include('landing.components.topHeaderMenu')

{{-- Main Header --}}
@include('landing.components.header')

{{-- Header --}}
@include('landing.components.headerMenu')

{{-- Common UI --}}
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<div>
    @include('modules.announcements.viewAllAnnouncements')
</div>


{{-- Footer --}}
@include('landing.components.footer')