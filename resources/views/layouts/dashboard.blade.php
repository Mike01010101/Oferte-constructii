<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Script esențial pentru a preveni flash-ul la Dark Mode. Rulează înainte de randarea paginii. --}}
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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @if(isset($accentColor) && $accentColor)
    <style>
        :root {
            --primary-accent: {{ $accentColor }};
        }
        /* Asigurăm culoarea și pentru dark mode, dacă nu are o variabilă separată */
        html[data-theme='dark'] {
            --primary-accent: {{ $accentColor }};
        }
    </style>
    @endif
    
    @stack('head_links')
</head>
<body
    @if(session('success')) data-success-message="{{ session('success') }}" @endif
    @if(session('error')) data-error-message="{{ session('error') }}" @endif
>
    <div id="app">
        
        <aside class="sidebar" id="sidebar">
            {{-- ... conținutul sidebar rămâne neschimbat ... --}}
            <div class="sidebar-header">
                <a href="{{ route('home') }}" class="sidebar-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 30px;">
                    <span class="ms-2">Ofertare</span>
                </a>
            </div>
            
            <nav class="sidebar-nav" id="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-section-header">Navigare</li>
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i>
                            <span>Panou de control</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('oferte.index') }}" class="nav-link {{ request()->routeIs('oferte.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>Oferte</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('clienti.index') }}" class="nav-link {{ request()->routeIs('clienti.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Clienți</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('rapoarte.index') }}" class="nav-link {{ request()->routeIs('rapoarte.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Rapoarte</span>
                        </a>
                    </li>

                    <li class="nav-section-header">Personalizare</li>
                    <li class="nav-item">
                        <a href="{{ route('template.show') }}" class="nav-link {{ request()->routeIs('template.show') ? 'active' : '' }}">
                            <i class="fa-solid fa-palette"></i>
                            <span>Creator șabloane</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('offer-settings.show') }}" class="nav-link {{ request()->routeIs('offer-settings.show') ? 'active' : '' }}">
                            <i class="fa-solid fa-cogs"></i>
                            <span>Setări ofertare</span>
                        </a>
                    </li>

                    @role('Owner|Administrator')
                    <li class="nav-section-header">Management</li>
                    <li class="nav-item">
                        <a href="{{ route('utilizatori.index') }}" class="nav-link {{ request()->routeIs('utilizatori.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-cog"></i>
                            <span>Management utilizatori</span>
                        </a>
                    </li>
                    @endrole

                    <li class="nav-section-header">Configurare</li>
                    <li class="nav-item">
                        <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                            <i class="fa-solid fa-building"></i>
                            <span>Profilul firmei</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="theme-switcher-wrapper">
                    <button class="theme-switcher" id="theme-switcher" aria-label="Switch theme">
                        <i class="fa-solid fa-sun theme-icon-light"></i>
                        <i class="fa-solid fa-moon theme-icon-dark"></i>
                    </button>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-circle me-2 fs-4"></i>
                        <span class="fw-bold">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                        <li>
                            <button class="dropdown-item" type="button"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>
                                Deconectare
                            </button>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        {{-- Containerul principal acum are un ID pentru Swup --}}
        <main id="swup" class="main-content transition-fade">
            <header class="main-header">
                <button class="btn" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="header-title">@yield('title', 'Panou de control')</span>
            </header>
            
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
        {{-- Aici se închide <div id="swup"> --}}

    </div> 
    {{-- Aici se închide <div id="app">. Toast-ul trebuie să fie DUPĂ acest div. --}}

    <!-- Containerul gol pentru notificări, care va fi populat de JavaScript -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1100;"></div>
    <!-- Definirea globală a modalelor de ștergere -->
    
    <!-- Modal Ștergere Client -->
    <div class="modal fade" id="deleteClientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Confirmare ștergere</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p>Sunteți sigur că doriți să ștergeți acest client? Acțiunea este ireversibilă.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button><button type="button" class="btn btn-danger" id="confirmDeleteBtnClient">Da, șterge</button></div>
            </div>
        </div>
    </div>

    <!-- Modal Ștergere Ofertă -->
    <div class="modal fade" id="deleteOfferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Confirmare ștergere</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p>Sunteți sigur că doriți să ștergeți această ofertă? Acțiunea este ireversibilă.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button><button type="button" class="btn btn-danger" id="confirmDeleteBtnOffer">Da, șterge</button></div>
            </div>
        </div>
    </div>

    <!-- Modal Ștergere Utilizator -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Confirmare ștergere</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p>Sunteți sigur că doriți să ștergeți acest utilizator? Acțiunea este ireversibilă.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button><button type="button" class="btn btn-danger" id="confirmDeleteBtnUser">Da, șterge</button></div>
            </div>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>