<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplicatie Oferte</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles (Bootstrap) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="antialiased">
    
    {{-- Container principal care umple tot ecranul și centrează conținutul --}}
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        
        <div class="text-center">
            
            {{-- Logo-ul --}}
            <img src="{{ asset('images/logo.png') }}" alt="Logo Aplicatie" class="img-fluid" style="max-width: 250px;">
            
            {{-- Butonul de Conectare --}}
            <div class="mt-4">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 py-3 fw-bold">
                    Conectare
                </a>
            </div>
            
        </div>
        
    </div>

</body>
</html>