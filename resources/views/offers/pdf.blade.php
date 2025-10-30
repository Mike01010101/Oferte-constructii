<!DOCTYPE html>
<html lang="ro">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Oferta {{ $offer->offer_number }}</title>
    
    @php
        $layout = $templateSettings->layout ?? 'classic';
        $fontFamily = $templateSettings->font_family ?? 'Roboto';
        $accentColor = $templateSettings->accent_color ?? '#0d6efd';
        $tableStyle = $offerSettings->table_style ?? 'grid';
    @endphp

    <style>
        /* Declarații complete pentru toate fonturile */
        @font-face { font-family: 'Roboto'; src: url('{{ storage_path('fonts/Roboto-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Roboto'; src: url('{{ storage_path('fonts/Roboto-Bold.ttf') }}') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'Lato'; src: url('{{ storage_path('fonts/Lato-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Lato'; src: url('{{ storage_path('fonts/Lato-Bold.ttf') }}') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'Merriweather'; src: url('{{ storage_path('fonts/Merriweather-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Merriweather'; src: url('{{ storage_path('fonts/Merriweather-Bold.ttf') }}') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'Open Sans'; src: url('{{ storage_path('fonts/OpenSans-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Open Sans'; src: url('{{ storage_path('fonts/OpenSans-Bold.ttf') }}') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'Montserrat'; src: url('{{ storage_path('fonts/Montserrat-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Montserrat'; src: url('{{ storage_path('fonts/Montserrat-Bold.ttf') }}') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'Source Serif Pro'; src: url('{{ storage_path('fonts/SourceSerifPro-Regular.ttf') }}') format('truetype'); font-weight: normal; }
        @font-face { font-family: 'Source Serif Pro'; src: url('{{ storage_path('fonts/SourceSerifPro-Bold.ttf') }}') format('truetype'); font-weight: bold; }

        body { font-family: '{{ $fontFamily }}', DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px; vertical-align: top; }
        thead th { background-color: {{ $accentColor }}; color: white; text-align: left; border: 1px solid {{ $accentColor }}; }
        tbody td { @if ($tableStyle == 'grid') border: 1px solid #dee2e6; @else border: none; border-bottom: 1px solid #dee2e6; @endif }
        @if ($tableStyle == 'striped') tbody tr:nth-child(even) { background-color: #f8f9fa; } @endif
        .border-0 { border: 0 !important; }
        p { margin: 0 0 2px 0; }
        h1, h2, h5 { margin: 0 0 8px 0; }
        strong { font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .mb-4 { margin-bottom: 20px; }
        .mt-5 { margin-top: 25px; }

        /* Stiluri noi pentru footer-ul din PDF */
        footer {
            position: fixed; 
            bottom: 0px; 
            left: 0px; 
            right: 0px;
            font-size: 8px;
            color: #777;
            text-align: left;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Antet - Layout Classic -->
        @if ($layout == 'classic')
        <table class="border-0 mb-4">
            <tr>
                <td class="border-0" style="width: 150px; vertical-align: middle;">
                    @if ($companyProfile && $companyProfile->logo_path)
                        <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-width: 120px; height: auto;">
                    @endif
                </td>
                <td class="border-0" style="vertical-align: middle;">
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

        {{-- NOU: Am încadrat datele firmei într-un chenar stilizat --}}
        <div class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; font-size: 9px; line-height: 1.3; margin-bottom: 20px;">
            <p><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
            <p>
                Reg. Com.: {{ $companyProfile->trade_register_number ?? '' }} | 
                C.I.F.: {{ $companyProfile->vat_number ?? '' }} | 
                Adresa: {{ $companyProfile->address ?? '' }}
            </p>
        </div>
        @endif
        
        <!-- Antet - Layout Compact (Refăcut) -->
        @if ($layout == 'compact')
        <div style="border-top: 5px solid {{ $accentColor }}; padding-top: 10px;" class="mb-4">
        <table class="border-0">
        <tr>
        <!-- Partea stângă: Logo + Date Firmă -->
        <td class="border-0" style="width: 70%; vertical-align: top;">
        <table class="border-0">
        <tr>
        <td class="border-0" style="width: 130px; vertical-align: top;">
        @if ($companyProfile && $companyProfile->logo_path)
        <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-width: 120px; height: auto;">
        @endif
        </td>
        <td class="border-0" style="vertical-align: middle; font-size: 9px; line-height: 1.2; padding-left: 10px;">
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
        </td>
        <!-- Partea dreaptă: Titlu Ofertă -->
        <td class="border-0 text-end" style="width: 30%; vertical-align: top;">
        <h2 style="color: {{ $accentColor }}; margin-bottom: 5px;">{{ $templateSettings->document_title ?? 'DEVIZ OFERTĂ' }}</h2>
        <p><strong>Nr:</strong> {{ $offer->offer_number }}</p>
        <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
        </td>
        </tr>
        </table>
        </div>
        @endif

                <!-- NOU: Antet - Layout Elegant -->
        @if ($layout == 'elegant')
        <div class="mb-4 text-end">
            <h1 style="color: {{ $accentColor }}; margin: 0; font-family: 'Merriweather', serif;">{{ $templateSettings->document_title }}</h1>
            <p>Nr: {{ $offer->offer_number }} / Data: {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
        </div>
        <table class="border-0 mb-4">
            <tr>
                <td class="border-0" style="width: 50%; font-size: 9px; line-height: 1.3;">
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
        @endif

        <!-- NOU: Antet - Layout Minimalist -->
        @if ($layout == 'minimalist')
        <table class="border-0 mb-4">
            <tr>
                <td class="border-0">
                    <h2 style="color: {{ $accentColor }}; margin: 0;">{{ $templateSettings->document_title }}</h2>
                </td>
                <td class="border-0 text-end">
                    <p><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
                    <p>Nr. Reg. Com.: {{ $companyProfile->trade_register_number ?? '' }}</p>
                    <p>C.I.F.: {{ $companyProfile->vat_number ?? '' }}</p>
                    <p>{!! nl2br(e($companyProfile->address ?? '')) !!}</p>
                    <p>Tel: {{ $companyProfile->phone_number ?? '' }} | Email: {{ $companyProfile->contact_email ?? '' }}</p>
                    <p>Cont bancar: {{ $companyProfile->iban ?? '' }}</p>
                    <p>Banca: {{ $companyProfile->bank_name ?? '' }}</p>
                    <p><strong>Nr:</strong> {{ $offer->offer_number }} | <strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
                </td>
            </tr>
        </table>
        <div style="border-bottom: 2px solid {{ $accentColor }}; margin-bottom: 20px;"></div>
        @endif

        <!-- Informații Client (doar pentru layout-urile vechi) -->
        @if(in_array($layout, ['classic', 'modern', 'compact']))
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
        <table>
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Descriere</th>
                    <th class="text-center">U.M.</th>
                    <th class="text-center">Cant.</th>
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
                            <tr><td class="text-end">Subtotal Resurse</td><td class="text-end">{{ number_format($calculations->baseSubtotal, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">CAM ({{ $offerSettings->summary_cam_percentage }}%)</td><td class="text-end">{{ number_format($calculations->camValue, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">Cheltuieli indirecte ({{ $offerSettings->summary_indirect_percentage }}%)</td><td class="text-end">{{ number_format($calculations->indirectValue, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">Profit ({{ $offerSettings->summary_profit_percentage }}%)</td><td class="text-end">{{ number_format($calculations->profitValue, 2, ',', '.') }}</td></tr>
                            <tr style="font-weight: bold;"><td class="text-end">TOTAL FĂRĂ TVA</td><td class="text-end">{{ number_format($calculations->totalWithoutVat, 2, ',', '.') }}</td></tr>
                            <tr><td class="text-end">TVA ({{ $offerSettings->vat_percentage }}%)</td><td class="text-end">{{ number_format($calculations->vatValue, 2, ',', '.') }}</td></tr>
                            <tr style="font-weight: bold; background-color: #e9ecef;"><td class="text-end">TOTAL GENERAL CU TVA</td><td class="text-end">{{ number_format($calculations->grandTotal, 2, ',', '.') }}</td></tr>
                        </table>
                    @else
                        <table style="background-color: #f8f9fa;">
                            <tr><td class="text-end">Subtotal:</td><td class="text-end">{{ number_format($calculations->totalWithoutVat, 2, ',', '.') }} RON</td></tr>
                            <tr><td class="text-end">TVA ({{ $offerSettings->vat_percentage }}%):</td><td class="text-end">{{ number_format($calculations->vatValue, 2, ',', '.') }} RON</td></tr>
                            <tr style="font-weight: bold;"><td class="text-end">Total general:</td><td class="text-end">{{ number_format($calculations->grandTotal, 2, ',', '.') }} RON</td></tr>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
        <!-- NOU: Secțiune Semnătură și Ștampilă -->
        @if ($templateSettings && $templateSettings->stamp_path)
            <table class="border-0" style="margin-top: 40px;">
                <tr>
                    <td class="border-0" style="width: 60%;"></td>
                    <td class="border-0 text-center" style="width: 40%;">
                        <p style="font-size: 11px;">Ofertant,</p>
                        <img src="{{ public_path('storage/' . $templateSettings->stamp_path) }}" 
                             style="width: {{ $templateSettings->stamp_size }}px; height: auto; margin-top: 5px;">
                    </td>
                </tr>
            </table>
        @endif

        <!-- Subsol -->
        <footer>
            {!! nl2br(e($templateSettings->footer_text ?? '')) !!}
        </footer>
    </div>
</body>
</html>