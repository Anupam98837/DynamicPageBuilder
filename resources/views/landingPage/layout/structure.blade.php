{{-- resources/views/landing/structure.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title','Meghnad Saha Institute of Technology')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

    {{-- Shared design system --}}
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

    @stack('styles')
</head>
<body>
    {{-- Site header + navigation --}}
    @include('landingPage.partials.header')

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- (Optional) simple footer placeholder – you can replace later --}}
    <footer class="mt-4">
        <div class="container py-3 text-center text-muted" style="font-size: 13px;">
            © {{ date('Y') }} Meghnad Saha Institute of Technology. All rights reserved.
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
