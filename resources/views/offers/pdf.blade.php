<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Oferta {{ $oferte->offer_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .w-50 { width: 50%; }
        .text-end { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        .mb-5 { margin-bottom: 3rem; }
        .mt-5 { margin-top: 3rem; }
        h2, h5, h6 { margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 8px; }
        thead { background-color: {{ $templateSettings->accent_color ?? '#0d6efd' }}; color: white; }
        .border-0 { border: 0 !important; }
        .small { font-size: 0.875em; }
        .text-muted { color: #6c757d; }
        .align-bottom { vertical-align: bottom; }
        hr { border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <table class="border-0 mb-5">
            <tr>
                <td class="w-50 border-0" style="text-align: {{ $templateSettings->logo_alignment ?? 'left' }};">
                    @if ($companyProfile && $companyProfile->logo_path)
                        <img src="{{ public_path('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 70px;">
                    @endif
                </td>
                <td class="w-50 border-0 text-end">
                    <h2 style="color: {{ $templateSettings->accent_color ?? '#0d6efd' }};">OFERTĂ COMERCIALĂ</h2>
                    <p class="mb-0"><strong>Nr:</strong> {{ $oferte->offer_number }}</p>
                    <p class="mb-0"><strong>Data:</strong> {{ \Carbon\Carbon::parse($oferte->offer_date)->format('d.m.Y') }}</p>
                </td>
            </tr>
        </table>

        <table class="border-0 mb-5">
             <tr>
                <td class="w-50 border-0">
                    <h5>Furnizor:</h5>
                    <p class="mb-0"><strong>{{ $companyProfile->company_name ?? 'N/A' }}</strong></p>
                    <p class="mb-0">{!! nl2br(e($companyProfile->address ?? '')) !!}</p>
                    <p class="mb-0">CUI: {{ $companyProfile->vat_number ?? '' }} / Reg.Com: {{ $companyProfile->trade_register_number ?? '' }}</p>
                </td>
                <td class="w-50 border-0">
                    <h5>Către:</h5>
                    <p class="mb-0"><strong>{{ $oferte->client->name }}</strong></p>
                    <p class="mb-0">{!! nl2br(e($oferte->client->address ?? '')) !!}</p>
                    <p class="mb-0">CUI: {{ $oferte->client->vat_number ?? '' }}</p>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Nr. crt.</th>
                    <th style="width: 50%;">Descriere</th>
                    <th>U.M.</th>
                    <th>Cant.</th>
                    <th>Preț unitar</th>
                    <th>Valoare</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($oferte->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->unit_measure }}</td>
                        <td>{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                        <td>{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <table class="border-0" style="margin-top: 20px;">
            <tr>
                <td class="w-50 border-0">
                    <strong>Note:</strong><br>
                    <p>{{ $oferte->notes ?? '-' }}</p>
                </td>
                <td class="w-50 border-0">
                    <table style="background-color: #f8f9fa;">
                        <tr>
                            <td class="text-end"><strong>Subtotal (RON):</strong></td>
                            <td class="text-end">{{ number_format($oferte->total_value, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-end">TVA (19%) (RON):</td>
                            <td class="text-end">{{ number_format($oferte->total_value * 0.19, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Total general (RON):</strong></td>
                            <td class="text-end"><strong>{{ number_format($oferte->total_value * 1.19, 2, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="mt-5">
            <hr>
            <p class="small text-muted">
                {!! nl2br(e($templateSettings->footer_text ?? '')) !!}
            </p>
        </div>
    </div>
</body>
</html>