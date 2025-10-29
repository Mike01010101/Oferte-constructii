<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Oferta {{ $offer->offer_number }}</title>
    
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

    <style>
        @font-face {
            font-family: 'Roboto';
            src: url('{{ storage_path('fonts/Roboto-Regular.ttf') }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url('{{ storage_path('fonts/Roboto-Bold.ttf') }}') format('truetype');
            font-weight: bold;
        }
        body { font-family: '{{ $fontFamily }}', DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px; vertical-align: top; }
        thead th { background-color: {{ $accentColor }}; color: white; text-align: left; border: 1px solid {{ $accentColor }}; }
        
        tbody td {
            @if ($tableStyle == 'grid')
                border: 1px solid #dee2e6;
            @else
                border: none;
                border-bottom: 1px solid #dee2e6;
            @endif
        }
        @if ($tableStyle == 'striped')
            tbody tr:nth-child(even) { background-color: #f8f9fa; }
        @endif
        
        .border-0 { border: 0 !important; }
        p { margin: 0 0 2px 0; }
        h2, h5 { margin: 0 0 8px 0; }
        strong { font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .mb-4 { margin-bottom: 15px; }
        .mt-5 { margin-top: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Antet - Layout Classic -->
        @if ($layout == 'classic')
        <table class="border-0 mb-4">
            <tr>
                <td class="border-0" style="width: 25%; vertical-align: middle;">
                    @if ($companyProfile && $companyProfile->logo_path)
                        <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-width: 120px; height: auto;">
                    @endif
                </td>
                <td class="border-0" style="width: 75%; vertical-align: top; padding-left: 20px;">
                    <p><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
                    <p>Nr. Reg. Com.: {{ $companyProfile->trade_register_number ?? '' }}</p>
                    <p>C.I.F.: {{ $companyProfile->vat_number ?? '' }}</p>
                    <p>{!! nl2br(e($companyProfile->address ?? '')) !!}</p>
                    <p>Tel: {{ $companyProfile->phone_number ?? '' }} | Email: {{ $companyProfile->contact_email ?? '' }}</p>
                    <p>Cont bancar: {{ $companyProfile->iban ?? '' }}</p>
                    <p>Banca: {{ $companyProfile->bank_name ?? '' }}</p>
                </td>
            </tr>
        </table>
        <hr>
        @endif

        <!-- Antet - Layout Modern -->
        @if ($layout == 'modern')
        <div class="text-center mb-4">
             @if ($companyProfile && $companyProfile->logo_path)
                <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 80px; margin-bottom: 1rem;">
            @endif
            <h2 style="color: {{ $accentColor }};">{{ $templateSettings->document_title ?? 'OFERTĂ' }}</h2>
            <p><strong>Nr:</strong> {{ $offer->offer_number }} | <strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
        </div>
        @endif
        
        <!-- Antet - Layout Compact -->
        @if ($layout == 'compact')
        <div style="background-color: {{ $accentColor }}; color: white; padding: 15px; border-radius: 5px;" class="mb-4">
             <table class="border-0">
                <tr>
                    <td class="border-0" style="width: 130px; vertical-align: top;">
                        @if ($companyProfile && $companyProfile->logo_path)
                             <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="height: 80px; width: auto;">
                        @endif
                    </td>
                    <td class="border-0" style="vertical-align: top; font-size: 9px; line-height: 1.4;">
                        <p style="color: white;"><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
                        <p style="color: white;">Reg. Com.: {{ $companyProfile->trade_register_number ?? '' }} | C.I.F.: {{ $companyProfile->vat_number ?? '' }}</p>
                        <p style="color: white;">{{ $companyProfile->address ?? '' }}</p>
                        <p style="color: white;">Cont: {{ $companyProfile->iban ?? '' }} | Banca: {{ $companyProfile->bank_name ?? '' }}</p>
                    </td>
                    <td class="border-0 text-end" style="vertical-align: top;">
                        <h2 style="color: white; margin-bottom: 5px;">{{ $templateSettings->document_title ?? 'OFERTĂ' }}</h2>
                        <p style="color: white;"><strong>Nr:</strong> {{ $offer->offer_number }}</p>
                        <p style="color: white;"><strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
                    </td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Informații Client și Titlu Ofertă -->
        @if($layout == 'classic')
            <table class="border-0 mb-4">
                <tr>
                    <td class="border-0" style="width: 50%;">
                        <h5>Către:</h5>
                        <p><strong>{{ $offer->client->name }}</strong></p>
                        <p>{!! nl2br(e($offer->client->address ?? '')) !!}</p>
                    </td>
                    <td class="border-0 text-end" style="width: 50%; vertical-align: bottom;">
                        <h2 style="color: {{ $accentColor }};">{{ $templateSettings->document_title ?? 'DEVIZ OFERTĂ' }}</h2>
                        <p><strong>Nr:</strong> {{ $offer->offer_number }} | <strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
                    </td>
                </tr>
            </table>
        @else
             <table class="border-0 mb-4">
                <tr>
                    <td class="border-0">
                        <h5>Către:</h5>
                        <p><strong>{{ $offer->client->name }}</strong></p>
                        <p>{!! nl2br(e($offer->client->address ?? '')) !!}</p>
                    </td>
                </tr>
            </table>
        @endif

        <!-- Tabel Produse -->
                <!-- Tabel Produse -->
        <table>
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Descriere</th>
                    <th>U.M.</th>
                    <th>Cant.</th>
                    {{-- Antetele se modifică dinamic --}}
                    @if($offerSettings->show_material_column) <th class="text-end">Material @if($offerSettings->pdf_price_display_mode == 'total') (Total) @endif</th> @endif
                    @if($offerSettings->show_labor_column) <th class="text-end">Manoperă @if($offerSettings->pdf_price_display_mode == 'total') (Total) @endif</th> @endif
                    @if($offerSettings->show_equipment_column) <th class="text-end">Utilaj @if($offerSettings->pdf_price_display_mode == 'total') (Total) @endif</th> @endif
                    
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

                        $displayTotalMat = $displayMatPrice * $item->quantity;
                        $displayTotalLab = $displayLabPrice * $item->quantity;
                        $displayTotalEq = $displayEqPrice * $item->quantity;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{!! nl2br(e($item->description)) !!}</td>
                        <td class="text-center">{{ $item->unit_measure }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                        
                        {{-- Aici este logica de afișare condiționată --}}
                        @if($offerSettings->show_material_column)
                            <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalMat : $displayMatPrice, 2, ',', '.') }}</td>
                        @endif
                        @if($offerSettings->show_labor_column)
                            <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalLab : $displayLabPrice, 2, ',', '.') }}</td>
                        @endif
                        @if($offerSettings->show_equipment_column)
                             <td class="text-end">{{ number_format( $offerSettings->pdf_price_display_mode == 'total' ? $displayTotalEq : $displayEqPrice, 2, ',', '.') }}</td>
                        @endif

                        @if($offerSettings->show_unit_price_column) <td class="text-end">{{ number_format($displayUnitPrice, 2, ',', '.') }}</td> @endif
                        <td class="text-end">{{ number_format($displayTotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totaluri și Note / Recapitulatii -->
        <table class="border-0" style="margin-top: 20px;">
            <tr>
                <td class="border-0" style="vertical-align: bottom; width: 50%;">
                    @if($offer->notes)
                        <strong>Note:</strong>
                        <p>{!! nl2br(e($offer->notes)) !!}</p>
                    @endif
                </td>
                <td class="border-0" style="width: 50%;">
                    @if($offerSettings->show_summary_block)
                        <table style="background-color: #f8f9fa;">
                            <tr><td class="text-end">TOTAL (RON)</td><td class="text-end">{{ number_format($subtotal_de_baza, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">CAM {{ $offerSettings->summary_cam_percentage }}%</td><td class="text-end">{{ number_format($cam, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">Cheltuieli indirecte {{ $offerSettings->summary_indirect_percentage }}%</td><td class="text-end">{{ number_format($indirect, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">Profit {{ $offerSettings->summary_profit_percentage }}%</td><td class="text-end">{{ number_format($profit, 2, ',', '.') }}</td></tr>
                            <tr style="font-weight: bold;">
                                <td class="text-end">TOTAL GENERAL FARA TVA</td>
                                <td class="text-end">{{ number_format($totalWithoutVat, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-end">TVA ({{ $offerSettings->vat_percentage }}%):</td>
                                <td class="text-end">{{ number_format($tvaValue, 2, ',', '.') }}</td>
                            </tr>
                            <tr style="font-weight: bold; background-color: #e9ecef;">
                                <td class="text-end">TOTAL GENERAL CU TVA</td>
                                <td class="text-end">{{ number_format($grandTotal, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    @else
                        <table style="background-color: #f8f9fa;">
                            <tr>
                                <td class="text-end">Subtotal:</td>
                                <td class="text-end">{{ number_format($subtotal_afisat, 2, ',', '.') }} RON</td>
                            </tr>
                            <tr>
                                <td class="text-end">TVA ({{ $offerSettings->vat_percentage }}%):</td>
                                <td class="text-end">{{ number_format($tvaValue, 2, ',', '.') }} RON</td>
                            </tr>
                            <tr style="font-weight: bold;">
                                <td class="text-end">Total general:</td>
                                <td class="text-end">{{ number_format($grandTotal, 2, ',', '.') }} RON</td>
                            </tr>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
        
        <!-- Subsol -->
        <div class="mt-5">
            <p class="small text-muted">{!! nl2br(e($templateSettings->footer_text ?? '')) !!}</p>
        </div>
    </div>
</body>
</html>