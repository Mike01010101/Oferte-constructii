@extends('layouts.dashboard')

@section('title', 'Detalii Ofertă: ' . $offer->offer_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detalii ofertă: {{ $offer->offer_number }}</h1>
        <div>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Înapoi la listă</a>
            <a href="{{ route('oferte.edit', $offer->id) }}" class="btn btn-info">
                <i class="fa-solid fa-pencil me-1"></i> Editează
            </a>
            <a href="{{ route('oferte.pdf', $offer->id) }}" class="btn btn-primary" target="_blank" data-swup-ignore>
                 <i class="fa-solid fa-file-pdf me-1"></i> Descarcă PDF
            </a>
        </div>
    </div>

    @php
        // Setările vizuale sunt singurele care mai sunt necesare aici
        $layout = $templateSettings->layout ?? 'classic';
        $fontFamily = $templateSettings->font_family ?? 'Roboto';
        $accentColor = $templateSettings->accent_color ?? '#0d6efd';
        $tableStyle = $offerSettings->table_style ?? 'grid';
    @endphp

    <div class="card p-4" style="font-family: '{{ $fontFamily }}', sans-serif;">
        <div class="card-body">
            <!-- Antet (Aici poți adăuga un preview al antetului din PDF dacă dorești) -->
            
            <!-- Informații Firmă și Client -->
            <section class="row mb-5">
                <div class="col-md-6">
                    <h5>De la:</h5>
                    <p class="mb-1"><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
                    <p class="text-secondary small">{!! nl2br(e($companyProfile->address ?? '')) !!}</p>
                </div>
                 <div class="col-md-6 text-md-end">
                    <h5>Către:</h5>
                    <p class="mb-1"><strong>{{ $offer->client->name }}</strong></p>
                    <p class="text-secondary small">{!! nl2br(e($offer->client->address ?? '')) !!}</p>
                </div>
            </section>


            <!-- Tabel cu produse/servicii -->
            <section class="mb-5">
                <div class="table-responsive">
                    <table class="table {{ $tableStyle == 'grid' ? 'table-bordered' : 'table-striped' }}">
                        <thead style="color: white;">
                            <tr style="background-color: {{ $accentColor }};">
                                <th>Nr.</th>
                                <th>Descriere</th>
                                <th class="text-center">U.M.</th>
                                <th class="text-center">Cant.</th>
                                @if($offerSettings->show_material_column) <th class="text-end">Material</th> @endif
                                @if($offerSettings->show_labor_column) <th class="text-end">Manoperă</th> @endif
                                @if($offerSettings->show_equipment_column) <th class="text-end">Utilaj</th> @endif
                                @if($offerSettings->show_unit_price_column) <th class="text-end">Preț Unitar</th> @endif
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offer->items as $item)
                                @php
                                    $recapMultiplier = $calculations->recapMultiplier;
                                    $displayMatPrice = $item->material_price * $recapMultiplier;
                                    $displayLabPrice = $item->labor_price * $recapMultiplier;
                                    $displayEqPrice = $item->equipment_price * $recapMultiplier;
                                    $displayUnitPrice = $displayMatPrice + $displayLabPrice + $displayEqPrice;
                                    $displayTotal = $item->quantity * $displayUnitPrice;

                                    $displayTotalMat = $displayMatPrice * $item->quantity;
                                    $displayTotalLab = $displayLabPrice * $item->quantity;
                                    $displayTotalEq = $displayEqPrice * $item->quantity;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{!! nl2br(e($item->description)) !!}</td>
                                    <td class="text-center">{{ $item->unit_measure }}</td>
                                    <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    
                                    @if($offerSettings->show_material_column)
                                        <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalMat : $displayMatPrice, 2, ',', '.') }}</td>
                                    @endif
                                    @if($offerSettings->show_labor_column)
                                        <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalLab : $displayLabPrice, 2, ',', '.') }}</td>
                                    @endif
                                    @if($offerSettings->show_equipment_column)
                                        <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalEq : $displayEqPrice, 2, ',', '.') }}</td>
                                    @endif
                                    @if($offerSettings->show_unit_price_column)
                                        <td class="text-end">{{ number_format($displayUnitPrice, 2, ',', '.') }}</td>
                                    @endif
                                    <td class="text-end fw-bold">{{ number_format($displayTotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            
            <!-- Totaluri și Note / Recapitulatii -->
            <section class="row justify-content-end">
                <div class="col-md-6">
                    @if($offer->notes)
                        <strong>Note:</strong>
                        <p class="text-secondary">{!! nl2br(e($offer->notes)) !!}</p>
                    @endif
                </div>
                <div class="col-md-5">
                    @if($offerSettings->show_summary_block)
                        <table class="table table-sm">
                            <tbody>
                                <tr><td class="border-0">Subtotal Resurse</td><td class="text-end border-0">{{ number_format($calculations->baseSubtotal, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">CAM ({{ $offerSettings->summary_cam_percentage }}%)</td><td class="text-end border-0">{{ number_format($calculations->camValue, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">Cheltuieli indirecte ({{ $offerSettings->summary_indirect_percentage }}%)</td><td class="text-end border-0">{{ number_format($calculations->indirectValue, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">Profit ({{ $offerSettings->summary_profit_percentage }}%)</td><td class="text-end border-0">{{ number_format($calculations->profitValue, 2, ',', '.') }}</td></tr>
                                <tr class="fw-bold"><td class="border-0 pt-3">TOTAL FĂRĂ TVA</td><td class="text-end border-0 pt-3">{{ number_format($calculations->totalWithoutVat, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">TVA ({{ $offerSettings->vat_percentage }}%)</td><td class="text-end border-0">{{ number_format($calculations->vatValue, 2, ',', '.') }}</td></tr>
                                <tr style="background-color: var(--element-bg-hover);" class="h5"><td class="border-0 py-2">TOTAL GENERAL</td><td class="text-end border-0 py-2">{{ number_format($calculations->grandTotal, 2, ',', '.') }} RON</td></tr>
                            </tbody>
                        </table>
                    @else
                        <table class="table table-sm">
                            <tbody>
                                <tr><td class="text-end border-0">Subtotal:</td><td class="text-end border-0">{{ number_format($calculations->totalWithoutVat, 2, ',', '.') }} RON</td></tr>
                                <tr><td class="text-end border-0">TVA ({{ $offerSettings->vat_percentage }}%):</td><td class="text-end border-0">{{ number_format($calculations->vatValue, 2, ',', '.') }} RON</td></tr>
                                <tr style="background-color: var(--element-bg-hover);" class="h5"><td class="text-end border-0 py-2">Total general:</td><td class="text-end border-0 py-2">{{ number_format($calculations->grandTotal, 2, ',', '.') }} RON</td></tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
{{-- Acest cod încarcă dinamic fonturile Google pentru a fi disponibile în previzualizare --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato&family=Merriweather&family=Roboto&family=Open+Sans&family=Montserrat&family=Source+Serif+Pro&display=swap" rel="stylesheet">
@endpush

@endsection