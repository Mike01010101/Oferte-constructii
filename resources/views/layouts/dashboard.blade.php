<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Vite Scripts and Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .sidebar {
            min-height: 100vh; /* Meniul va ocupa toată înălțimea ecranului */
        }
    </style>
</head>
<body>
    <div id="app" class="d-flex">
        
        {{-- Meniul Vertical din Stânga --}}
        <div class="sidebar bg-dark text-white p-3" style="width: 280px;">
            <h4 class="text-center mb-4">Aplicatie oferte</h4>
            
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link active text-white">
                        Panou de control
                    </a>
                </li>
                <li>
                    <a href="{{ route('oferte.index') }}" class="nav-link text-white">
                        Oferte
                    </a>
                </li>
                <li>
                    <a href="{{ route('clienti.index') }}" class="nav-link text-white">
                        Clienți
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-white">
                        Rapoarte
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a class="nav-link text-secondary">PERSONALIZARE</a>
                </li>
                <li>
                    <a href="{{ route('template.show') }}" class="nav-link text-white">
                        Creator șabloane
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a class="nav-link text-secondary">CONFIGURARE</a>
                </li>
                <li>
                    <a href="{{ route('profile.show') }}" class="nav-link text-white">
                        Profilul firmei
                    </a>
                </li>
                <li>
                    <a href="{{ route('offer-settings.show') }}" class="nav-link text-white">
                        Setări ofertare
                    </a>
                </li>
            </ul>

            <div class="mt-auto">
                <hr class="text-secondary">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <strong>{{ Auth::user()->name }}</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Deconectare
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Conținutul Principal din Dreapta --}}
        <main class="w-100 p-4" style="background-color: #f8f9fa;">
            @yield('content')
        </main>
        
    </div>
</body>
</html>