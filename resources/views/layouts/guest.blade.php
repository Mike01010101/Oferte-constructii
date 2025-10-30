<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme) {
                document.documentElement.setAttribute('data-theme', theme);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body style="background-color: var(--main-bg);">
    {{-- Am înlocuit <div id="app"> cu un nou container pentru a evita conflictele de stil --}}
    <div class="guest-container">
        <div class="container">
            <header class="text-center py-4">
                 <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Aplicatie" class="img-fluid" style="max-width: 120px;">
                 </a>
            </header>
            
            <main>
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>