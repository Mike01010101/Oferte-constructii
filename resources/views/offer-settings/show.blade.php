@extends('layouts.dashboard')

@section('title', 'Setări Ofertare')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Setări ofertare</h1>

    <form method="POST" action="{{ route('offer-settings.update') }}">
        @csrf
        <div class="row">
            <div class="col-lg-7">
                <!-- Card Numerotare -->
                <div class="card mb-4">
                    <div class="card-header">Numerotare Oferte</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mod de numerotare</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="numbering_mode" id="mode_auto" value="auto" {{ (old('numbering_mode', $settings->numbering_mode ?? 'auto') == 'auto') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_auto">Automat (Prefix + Număr + /Anul Curent)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="numbering_mode" id="mode_manual" value="manual" {{ (old('numbering_mode', $settings->numbering_mode ?? 'auto') == 'manual') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_manual">Manual (introduc numărul la crearea fiecărei oferte)</label>
                            </div>
                        </div>

                        {{-- Secțiunea pentru opțiunile de numerotare automată, controlată de JS --}}
                        <div id="auto-numbering-options">
                            <p class="text small">Exemplu: <strong>OFC-</strong> + <strong>{{ $settings->next_number ?? 1 }}</strong> + <strong>/{{ now()->year }}</strong> = <strong>OFC-{{ $settings->next_number ?? 1 }}/{{ now()->year }}</strong></p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prefix" class="form-label">Prefix (opțional)</label>
                                    <input type="text" class="form-control" id="prefix" name="prefix" value="{{ old('prefix', $settings->prefix ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="start_number" class="form-label">Numărul următor</label>
                                    <input type="number" class="form-control" id="start_number" name="start_number" value="{{ old('start_number', $settings->next_number ?? 1) }}" min="1">
                                    <small class="text-muted">Sistemul va folosi acest număr pentru următoarea ofertă automată.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Recapitulatii -->
                <div class="card mb-4">
                    <div class="card-header">Recapitulații la finalul ofertei</div>
                    <div class="card-body">
                         <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_summary_block" name="show_summary_block" value="1" {{ old('show_summary_block', $settings->show_summary_block ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_summary_block">Afișează blocul de recapitulații în PDF</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="include_summary_in_prices" name="include_summary_in_prices" value="1" {{ old('include_summary_in_prices', $settings->include_summary_in_prices ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="include_summary_in_prices">Include recapitulația în prețul resurselor (ascunde blocul)</label>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="summary_cam_percentage" class="form-label">CAM (%)</label>
                                <input type="number" step="0.01" class="form-control" name="summary_cam_percentage" value="{{ old('summary_cam_percentage', $settings->summary_cam_percentage ?? 0) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="summary_indirect_percentage" class="form-label">Indirecte (%)</label>
                                <input type="number" step="0.01" class="form-control" name="summary_indirect_percentage" value="{{ old('summary_indirect_percentage', $settings->summary_indirect_percentage ?? 0) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="summary_profit_percentage" class="form-label">Profit (%)</label>
                                <input type="number" step="0.01" class="form-control" name="summary_profit_percentage" value="{{ old('summary_profit_percentage', $settings->summary_profit_percentage ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                                <!-- Card Coloane și TVA -->
                <div class="card">
                    <div class="card-header">Afișare Coloane, Totaluri și TVA</div>
                    <div class="card-body">
                        <label class="form-label fw-bold">Configurare coloane resurse</label>

                        {{-- Material --}}
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" id="show_material_column" name="show_material_column" value="1" {{ old('show_material_column', $settings->show_material_column ?? true) ? 'checked' : '' }}>
                            </div>
                            <input type="text" class="form-control" name="material_column_name" value="{{ old('material_column_name', $settings->material_column_name ?? 'Material') }}" placeholder="Nume coloană">
                        </div>
                        <div class="form-check form-switch mb-3 ms-4">
                             <input class="form-check-input" type="checkbox" role="switch" id="show_material_total" name="show_material_total" value="1" {{ old('show_material_total', $settings->show_material_total ?? false) ? 'checked' : '' }}>
                             <label class="form-check-label small" for="show_material_total">Afișează total pe ofertă</label>
                        </div>

                        {{-- Manoperă --}}
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" id="show_labor_column" name="show_labor_column" value="1" {{ old('show_labor_column', $settings->show_labor_column ?? true) ? 'checked' : '' }}>
                            </div>
                            <input type="text" class="form-control" name="labor_column_name" value="{{ old('labor_column_name', $settings->labor_column_name ?? 'Manoperă') }}" placeholder="Nume coloană">
                        </div>
                        <div class="form-check form-switch mb-3 ms-4">
                             <input class="form-check-input" type="checkbox" role="switch" id="show_labor_total" name="show_labor_total" value="1" {{ old('show_labor_total', $settings->show_labor_total ?? false) ? 'checked' : '' }}>
                             <label class="form-check-label small" for="show_labor_total">Afișează total pe ofertă</label>
                        </div>
                        
                        {{-- Utilaj --}}
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" id="show_equipment_column" name="show_equipment_column" value="1" {{ old('show_equipment_column', $settings->show_equipment_column ?? true) ? 'checked' : '' }}>
                            </div>
                            <input type="text" class="form-control" name="equipment_column_name" value="{{ old('equipment_column_name', $settings->equipment_column_name ?? 'Utilaj') }}" placeholder="Nume coloană">
                        </div>
                        <div class="form-check form-switch mb-3 ms-4">
                             <input class="form-check-input" type="checkbox" role="switch" id="show_equipment_total" name="show_equipment_total" value="1" {{ old('show_equipment_total', $settings->show_equipment_total ?? false) ? 'checked' : '' }}>
                             <label class="form-check-label small" for="show_equipment_total">Afișează total pe ofertă</label>
                        </div>

                        <hr>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_unit_price_column" name="show_unit_price_column" value="1" {{ old('show_unit_price_column', $settings->show_unit_price_column ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_unit_price_column">Afișează coloana "Preț Unitar"</label>
                        </div>
                        <hr>
                        <label class="form-label fw-bold">Afișare prețuri în PDF</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pdf_price_display_mode" id="mode_unit" value="unit" {{ (old('pdf_price_display_mode', $settings->pdf_price_display_mode ?? 'unit') == 'unit') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_unit">Afișează prețuri unitare în coloanele de resurse</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="pdf_price_display_mode" id="mode_total" value="total" {{ (old('pdf_price_display_mode', $settings->pdf_price_display_mode ?? 'unit') == 'total') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_total">Afișează prețuri totale în coloanele de resurse</label>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="vat_percentage" class="form-label fw-bold">Cotă TVA (%)</label>
                            <input type="number" step="0.01" class="form-control" id="vat_percentage" name="vat_percentage" value="{{ old('vat_percentage', $settings->vat_percentage ?? 19.00) }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Salvează setările</button>
        </div>
    </form>
</div>
@endsection