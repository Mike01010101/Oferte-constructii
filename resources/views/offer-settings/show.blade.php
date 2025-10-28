@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Setări ofertare</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">Format și numerotare oferte</div>
        <div class="card-body">
            <form method="POST" action="{{ route('offer-settings.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <p class="text-muted">
                            Stabiliți formatul numerelor de înregistrare pentru ofertele generate. <br>
                            Exemplu: <strong>Prefix</strong> + <strong>Număr</strong> + <strong>Sufix</strong>  ->  <strong>OFC-</strong><strong>101</strong><strong>/2024</strong>
                        </p>

                        <div class="row align-items-center">
                            <div class="col-md-4 mb-3">
                                <label for="prefix" class="form-label">Prefix (opțional)</label>
                                <input type="text" class="form-control" id="prefix" name="prefix" value="{{ old('prefix', $settings->prefix ?? '') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="start_number" class="form-label">Număr de început</label>
                                <input type="number" class="form-control @error('start_number') is-invalid @enderror" id="start_number" name="start_number" value="{{ old('start_number', $settings->start_number ?? 1) }}" required>
                                @error('start_number')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="suffix" class="form-label">Sufix (opțional)</label>
                                <input type="text" class="form-control" id="suffix" name="suffix" value="{{ old('suffix', $settings->suffix ?? '') }}">
                            </div>
                        </div>

                        <hr>
                        <p>
                            Următorul număr de ofertă care va fi generat: 
                            <strong>
                                {{ $settings->prefix ?? '' }}{{ $settings->next_number ?? ($settings->start_number ?? 1) }}{{ $settings->suffix ?? '' }}
                            </strong>
                        </p>
                        <hr>

                        <button type="submit" class="btn btn-primary">Salvează setările</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection