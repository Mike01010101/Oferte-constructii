@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Creator șabloane ofertă</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">Personalizare aspect ofertă</div>
        <div class="card-body">
            <form method="POST" action="{{ route('template.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        
                        <!-- Culoare de accent -->
                        <div class="mb-4">
                            <label for="accent_color" class="form-label"><strong>Culoare de accent</strong></label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?? '#0d6efd') }}" title="Alege o culoare">
                                <span class="ms-2">Selectează o culoare pentru titluri și elemente cheie.</span>
                            </div>
                            @error('accent_color')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Aliniere logo -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Alinierea siglei în antet</strong></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_alignment" id="align_left" value="left" {{ (old('logo_alignment', $settings->logo_alignment ?? 'left') == 'left') ? 'checked' : '' }}>
                                <label class="form-check-label" for="align_left">Stânga</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_alignment" id="align_center" value="center" {{ (old('logo_alignment', $settings->logo_alignment ?? 'left') == 'center') ? 'checked' : '' }}>
                                <label class="form-check-label" for="align_center">Centru</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_alignment" id="align_right" value="right" {{ (old('logo_alignment', $settings->logo_alignment ?? 'left') == 'right') ? 'checked' : '' }}>
                                <label class="form-check-label" for="align_right">Dreapta</label>
                            </div>
                            @error('logo_alignment')
                                 <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Text subsol -->
                        <div class="mb-3">
                            <label for="footer_text" class="form-label"><strong>Text subsol (Termeni și condiții)</strong></label>
                            <textarea class="form-control" id="footer_text" name="footer_text" rows="5" placeholder="Ex: Valabilitate ofertă: 30 de zile. Termen de plată: la livrare.">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                            <small class="form-text text-muted">Acest text va apărea la finalul fiecărei oferte generate.</small>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary">Salvează șablonul</button>
                    </div>

                    <div class="col-md-6">
                        <div class="border p-3">
                            <h5>Previzualizare (schematic)</h5>
                            <p class="text-muted small">Aceasta este o reprezentare simplificată. Aspectul final va fi vizibil în PDF-ul generat.</p>
                            <div class="border" style="height: 400px; display: flex; flex-direction: column;">
                                <!-- Antet simulat -->
                                <div class="p-2 border-bottom" style="text-align: {{ $settings->logo_alignment ?? 'left' }};">
                                    <div style="width: 80px; height: 30px; background-color: #ccc; display: inline-block;">Logo</div>
                                </div>
                                <!-- Corp simulat -->
                                <div class="p-2 flex-grow-1">
                                    <h6 style="color: {{ $settings->accent_color ?? '#0d6efd' }};">Titlu Ofertă</h6>
                                    <div class="border-top mt-2" style="border-color: {{ $settings->accent_color ?? '#0d6efd' }} !important;">
                                        <p class="small mt-2">Tabel cu produse...</p>
                                    </div>
                                </div>
                                <!-- Subsol simulat -->
                                <div class="p-2 border-top">
                                    <p class="small text-muted fst-italic">
                                        {{ Str::limit($settings->footer_text ?? 'Textul de subsol va apărea aici...', 80) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection