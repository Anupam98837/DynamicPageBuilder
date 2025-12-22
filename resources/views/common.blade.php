<!-- views/common.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dynamic Page')</title>
    
    <!-- Include the header module -->
    @include('modules.header.header')
    
    <!-- Your custom styles for the page content -->
    <style>
        .page-content {
            padding: 2rem 0;
            min-height: 70vh;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- The dynamic navbar is already included above -->
    
    <!-- Page Content -->
    <main class="page-content">
        <div class="container">
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Institution') }}. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Your page-specific scripts -->
    @stack('scripts')
</body>

</html>