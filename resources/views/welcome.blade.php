@extends('layouts.guest')
@section('content')
<div class="row justify-content-center">
<div class="col-md-6 col-lg-5 text-center">
{{-- Butonul de Conectare --}}
    <div class="card">
        <div class="card-body p-5">
            <h3 class="mb-4">Bun venit!</h3>
            <p class="text-secondary mb-4">Conectează-te pentru a începe să creezi oferte.</p>
            <div class="d-grid">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Conectare
                </a>
            </div>
        </div>
    </div>
</div>
</div>
@endsection