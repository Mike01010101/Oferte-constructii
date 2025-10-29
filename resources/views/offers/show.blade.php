@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detalii ofertă: {{ $offer->offer_number }}</h1>
        <div>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Înapoi la listă</a>
            <a href="{{ route('oferte.pdf', $offer->id) }}" class="btn btn-primary" target="_blank">Descarcă PDF</a>
        </div>
    </div>

    @php
        // Preluarea setărilor
        $layout = $templateSettings->layout ?? 'classic';
        $fontFamily = $templateSettings->font_family ?? 'Roboto';
        $accentColor = $templateSettings->accent_color ?? '#0d6efd';
        $tableStyle = $offerSettings->table_style ?? 'grid';

        // Pas 1: Calculăm totalurile de bază pe resurse
        $totalBaseMaterial = 0;
        $totalBaseLabor = 0;
        $totalBaseEquipment = 0;
        foreach ($offer->items as $item) {
            $totalBaseMaterial += $item->quantity * $item->material_price;
            $totalBaseLabor += $item->quantity * $item->labor_price;
            $totalBaseEquipment += $item->quantity * $item->equipment_price;
        }
        $subtotal_de_baza = $offer->total_value; // Preluăm subtotalul de bază salvat

        // Coeficient de bază
        $recapMultiplier = 1.0;

        // Pas 2: Calculăm recapitulatiile și factorul de redistribuire
        if ($offerSettings->include_summary_in_prices) {
            // REGULA NOUĂ: CAM se aplică DOAR la manoperă
            $cam = $totalBaseLabor * ($offerSettings->summary_cam_percentage / 100);
            
            $total_plus_cam = $subtotal_de_baza + $cam;
            $indirect = $total_plus_cam * ($offerSettings->summary_indirect_percentage / 100);
            $total_plus_indirect = $total_plus_cam + $indirect;
            $profit = $total_plus_indirect * ($offerSettings->summary_profit_percentage / 100);
            $totalRecap = $cam + $indirect + $profit;

            if ($subtotal_de_baza > 0) {
                $recapMultiplier = 1 + ($totalRecap / $subtotal_de_baza);
            }
        }
        
        // Pas 3: Calculăm valorile finale pentru afișare
        $subtotal_afisat = $subtotal_de_baza * $recapMultiplier;
        
        $tvaValue = 0;
        $grandTotal = 0;

        if ($offerSettings->show_summary_block) {
            // Recalculăm totul explicit pentru afișare
            $cam = $totalBaseLabor * ($offerSettings->summary_cam_percentage / 100);
            $indirect = ($subtotal_de_baza + $cam) * ($offerSettings->summary_indirect_percentage / 100);
            $profit = ($subtotal_de_baza + $cam + $indirect) * ($offerSettings->summary_profit_percentage / 100);
            $totalWithoutVat = $subtotal_de_baza + $cam + $indirect + $profit;
            $tvaValue = $totalWithoutVat * ($offerSettings->vat_percentage / 100);
            $grandTotal = $totalWithoutVat + $tvaValue;
        } else {
            // Pentru cazul simplu SAU cazul cu prețuri majorate, calculăm TVA din subtotalul deja majorat
            $tvaValue = $subtotal_afisat * ($offerSettings->vat_percentage / 100);
            $grandTotal = $subtotal_afisat + $tvaValue;
        }
    @endphp

    <div class="card p-4" style="font-family: '{{ $fontFamily }}', sans-serif;">
        <div class="card-body">
            <!-- Antet - Layout Classic -->
            @if ($layout == 'classic')
            <header class="row mb-5">
                <div class="col-md-6" style="text-align: {{ $templateSettings->logo_alignment ?? 'left' }};">
                    @if ($companyProfile->logo_path)
                        <img src="{{ asset('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 80px;">
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <h2 style="color: {{ $accentColor }};">{{ $templateSettings->document_title ?? 'OFERTĂ' }}</h2>
                    <p class="mb-0"><strong>Nr:</strong> {{ $offer->offer_number }}</p>
                    <p class="mb-0"><strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
                </div>
            </header>
            @endif

            <!-- Antet - Layout Modern -->
            @if ($layout == 'modern')
            <header class="text-center mb-5">
                @if ($companyProfile->logo_path)
                    <img src="{{ asset('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 90px; margin-bottom: 1.5rem;">
                @endif
                <h2 style="color: {{ $accentColor }};">{{ $templateSettings->document_title ?? 'OFERTĂ' }}</h2>
                <p><strong>Nr:</strong> {{ $offer->offer_number }} | <strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
            </header>
            @endif
            
            <!-- Antet - Layout Compact -->
            @if ($layout == 'compact')
            <header style="background-color: {{ $accentColor }}; color: white; padding: 1.5rem;" class="mb-5 rounded">
                 <div class="row align-items-center">
                    <div class="col-md-6">
                        @if ($companyProfile->logo_path)
                            <img src="{{ asset('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 60px;">
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        <h2 style="color: white;">{{ $templateSettings->document_title ?? 'OFERTĂ' }}</h2>
                        <p class="mb-0"><strong>Nr:</strong> {{ $offer->offer_number }}</p>
                    </div>
                </div>
            </header>
            @endif

           <!-- Informații Firmă și Client (se adaptează la layout) -->
            <section class="row mb-5">
                {{-- Logica de afișare Furnizor/Client --}}
            </section>

            <!-- Tabel cu produse/servicii -->
            <section class="mb-5">
                <table class="table {{ $tableStyle == 'grid' ? 'table-bordered' : 'table-striped' }}">
                    <thead style="color: white;">
                        <tr style="background-color: {{ $accentColor }};">
                            <th>Nr.</th>
                            <th>Descriere</th>
                            <th>U.M.</th>
                            <th>Cant.</th>
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
                        $displayMatPrice = $item->material_price * $recapMultiplier;
                        $displayLabPrice = $item->labor_price * $recapMultiplier;
                        $displayEqPrice = $item->equipment_price * $recapMultiplier;
                        $displayUnitPrice = $displayMatPrice + $displayLabPrice + $displayEqPrice;
                        $displayTotal = $item->quantity * $displayUnitPrice;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{!! nl2br(e($item->description)) !!}</td>
                        <td class="text-center">{{ $item->unit_measure }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                        @if($offerSettings->show_material_column) <td class="text-end">{{ number_format($displayMatPrice, 2, ',', '.') }}</td> @endif
                        @if($offerSettings->show_labor_column) <td class="text-end">{{ number_format($displayLabPrice, 2, ',', '.') }}</td> @endif
                        @if($offerSettings->show_equipment_column) <td class="text-end">{{ number_format($displayEqPrice, 2, ',', '.') }}</td> @endif
                        @if($offerSettings->show_unit_price_column) <td class="text-end">{{ number_format($displayUnitPrice, 2, ',', '.') }}</td> @endif
                        <td class="text-end">{{ number_format($displayTotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
                </table>
            </section>
            
            <!-- Totaluri și Note / Recapitulatii -->
            <section class="row">
                <div class="col-md-6">
                    @if($offer->notes)
                        <strong>Note:</strong><br>
                        <p>{!! nl2br(e($offer->notes)) !!}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($offerSettings->show_summary_block)
                        <table class="table table-sm">
                            <tbody>
                                <tr><td class="border-0">TOTAL (RON)</td><td class="text-end border-0">{{ number_format($subtotal_de_baza, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">CAM {{ $offerSettings->summary_cam_percentage }}%</td><td class="text-end border-0">{{ number_format($cam, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">Cheltuieli indirecte {{ $offerSettings->summary_indirect_percentage }}%</td><td class="text-end border-0">{{ number_format($indirect, 2, ',', '.') }}</td></tr>
                                <tr><td class="border-0">Profit {{ $offerSettings->summary_profit_percentage }}%</td><td class="text-end border-0">{{ number_format($profit, 2, ',', '.') }}</td></tr>
                                <tr class="fw-bold"><td class="border-0">TOTAL GENERAL FARA TVA</td><td class="text-end border-0">{{ number_format($totalWithoutVat, 2, ',', '.') }}</td></tr>
                            </tbody>
                        </table>
                    @else
                        <table class="table">
                            <tbody>
                                <tr><td class="text-end border-0">Subtotal:</td><td class="text-end border-0">{{ number_format($subtotal_afisat, 2, ',', '.') }} RON</td></tr>
                                <tr><td class="text-end border-0">TVA ({{ $offerSettings->vat_percentage }}%):</td><td class="text-end border-0">{{ number_format($tvaValue, 2, ',', '.') }} RON</td></tr>
                                <tr style="background-color: #f2f2f2;"><td class="text-end border-0"><h5>Total general:</h5></td><td class="text-end border-0"><h5>{{ number_format($grandTotal, 2, ',', '.') }} RON</h5></td></tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <!-- Subsol -->
            <footer class="mt-5">
                <hr>
                <p class="text-muted small">{!! nl2br(e($templateSettings->footer_text ?? '')) !!}</p>
            </footer>
        </div>
    </div>
</div>
@endsection