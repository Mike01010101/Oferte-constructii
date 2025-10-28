@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Panou de control</h1>
    
    <div class="card">
        <div class="card-header">{{ __('Dashboard') }}</div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            {{ __('Sunteți autentificat!') }}
        </div>
    </div>
    
    {{-- Aici veți adăuga conținutul specific paginii de start a panoului de control (statistici, grafice etc.) --}}

</div>
@endsection