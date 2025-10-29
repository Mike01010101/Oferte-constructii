@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Setări ofertare</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('offer-settings.update') }}">
        @csrf
        <div class="row">
            <div class="col-lg-6">
                <!-- Card Numerotare și TVA -->
                <div class="card mb-4">
                    <div class="card-header">Numerotare și TVA</div>
                    <div class="card-body">
                        <p class="text-muted small">Format: <strong>Prefix</strong> + <strong>Număr</strong> + <strong>Sufix</strong></p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="prefix" class="form-label">Prefix</label>
                                <input type="text" class="form-control" id="prefix" name="prefix" value="{{ old('prefix', $settings->prefix ?? '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="start_number" class="form-label">Număr de început</label>
                                <input type="number" class="form-control" id="start_number" name="start_number" value="{{ old('start_number', $settings->start_number ?? 1) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="suffix" class="form-label">Sufix</label>
                                <input type="text" class="form-control" id="suffix" name="suffix" value="{{ old('suffix', $settings->suffix ?? '') }}">
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="vat_percentage" class="form-label">Cotă TVA (%)</label>
                            <input type="number" step="0.01" class="form-control" id="vat_percentage" name="vat_percentage" value="{{ old('vat_percentage', $settings->vat_percentage ?? 19.00) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Card Recapitulatii -->
                <div class="card">
                    <div class="card-header">Recapitulații la finalul ofertei</div>
                    <div class="card-body">
                         <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_summary_block" name="show_summary_block" value="1" {{ old('show_summary_block', $settings->show_summary_block ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_summary_block">Afișează blocul de recapitulații</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="include_summary_in_prices" name="include_summary_in_prices" value="1" {{ old('include_summary_in_prices', $settings->include_summary_in_prices ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_summary_in_prices">Include recapitulația în prețul resurselor (ascunde blocul)</label>
                                </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="summary_cam_percentage" class="form-label">CAM (%)</label>
                                <input type="number" step="0.01" class="form-control" id="summary_cam_percentage" name="summary_cam_percentage" value="{{ old('summary_cam_percentage', $settings->summary_cam_percentage ?? 0) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="summary_indirect_percentage" class="form-label">Indirecte (%)</label>
                                <input type="number" step="0.01" class="form-control" id="summary_indirect_percentage" name="summary_indirect_percentage" value="{{ old('summary_indirect_percentage', $settings->summary_indirect_percentage ?? 0) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="summary_profit_percentage" class="form-label">Profit (%)</label>
                                <input type="number" step="0.01" class="form-control" id="summary_profit_percentage" name="summary_profit_percentage" value="{{ old('summary_profit_percentage', $settings->summary_profit_percentage ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Card Coloane Vizibile -->
                <div class="card">
                    <div class="card-header">Coloane vizibile în ofertă</div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_material_column" name="show_material_column" value="1" {{ old('show_material_column', $settings->show_material_column ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_material_column">Material (RON)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_labor_column" name="show_labor_column" value="1" {{ old('show_labor_column', $settings->show_labor_column ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_labor_column">Manoperă (RON)</label>
                        </div>
                         <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_equipment_column" name="show_equipment_column" value="1" {{ old('show_equipment_column', $settings->show_equipment_column ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_equipment_column">Utilaj (RON)</label>
                        </div>
                         <hr>
                        <label class="form-label">Afișare prețuri în PDF</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pdf_price_display_mode" id="mode_unit" value="unit" {{ (old('pdf_price_display_mode', $settings->pdf_price_display_mode ?? 'unit') == 'unit') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_unit">Afișează prețuri unitare</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pdf_price_display_mode" id="mode_total" value="total" {{ (old('pdf_price_display_mode', $settings->pdf_price_display_mode ?? 'unit') == 'total') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_total">Afișează prețuri totale</label>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const showSummary = document.getElementById('show_summary_block');
    const includeInPrices = document.getElementById('include_summary_in_prices');

    function toggleOptions() {
        if (includeInPrices.checked) {
            showSummary.checked = false;
            showSummary.disabled = true;
        } else {
            showSummary.disabled = false;
        }
    }

    includeInPrices.addEventListener('change', toggleOptions);
    // Rulează la încărcare
    toggleOptions();
});
</script>
@endpush
@endsection