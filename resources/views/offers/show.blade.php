@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detalii ofertă: {{ $offer->offer_number }}</h1>
        <div>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Înapoi la listă</a>
            <a href="{{ route('oferte.pdf', $offer->id) }}" class="btn btn-primary">Descarcă PDF</a>
        </div>
    </div>

    <div class="card p-4">
        <div class="card-body">
            <!-- Antet -->
            <header class="row mb-5">
                <div class="col-md-6" style="text-align: {{ $templateSettings->logo_alignment ?? 'left' }};">
                    @if ($companyProfile->logo_path)
                        <img src="{{ asset('storage/' . $companyProfile->logo_path) }}" alt="Logo" style="max-height: 80px;">
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <h2 style="color: {{ $templateSettings->accent_color ?? '#0d6efd' }};">OFERTĂ COMERCIALĂ</h2>
                    <p class="mb-0"><strong>Nr:</strong> {{ $offer->offer_number }}</p>
                    <p class="mb-0"><strong>Data:</strong> {{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</p>
                </div>
            </header>

            <!-- Informații Firmă și Client -->
            <section class="row mb-5">
                <div class="col-md-6">
                    <h5>Furnizor:</h5>
                    <p class="mb-0"><strong>{{ $companyProfile->company_name ?? 'Nume firmă neconfigurat' }}</strong></p>
                    <p class="mb-0">{{ $companyProfile->address ?? '' }}</p>
                    <p class="mb-0">CUI: {{ $companyProfile->vat_number ?? '' }} / Reg.Com: {{ $companyProfile->trade_register_number ?? '' }}</p>
                    <p class="mb-0">Email: {{ $companyProfile->contact_email ?? '' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Către:</h5>
                    <p class="mb-0"><strong>{{ $offer->client->name }}</strong></p>
                    <p class="mb-0">{{ $offer->client->address ?? '' }}</p>
                    <p class="mb-0">CUI: {{ $offer->client->vat_number ?? '' }}</p>
                    <p class="mb-0">Contact: {{ $offer->client->contact_person ?? '' }}</p>
                </div>
            </section>

            <!-- Tabel cu produse/servicii -->
            <section class="mb-5">
                <table class="table table-bordered">
                    <thead style="background-color: {{ $templateSettings->accent_color ?? '#0d6efd' }}; color: white;">
                        <tr>
                            <th>Nr. crt.</th>
                            <th class="w-50">Descriere produs / serviciu</th>
                            <th>U.M.</th>
                            <th>Cant.</th>
                            <th>Preț unitar (RON)</th>
                            <th>Valoare (RON)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offer->items as $item)
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
                    <tfoot>
                        <tr>
                            <td colspan="4" rowspan="3" class="border-0 align-bottom">
                                <strong>Note:</strong><br>
                                <p>{{ $offer->notes ?? '-' }}</p>
                            </td>
                            <td class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>{{ number_format($offer->total_value, 2, ',', '.') }} RON</strong></td>
                        </tr>
                        <tr>
                            <td class="text-end">TVA (19%):</td>
                            <td>{{ number_format($offer->total_value * 0.19, 2, ',', '.') }} RON</td>
                        </tr>
                        <tr>
                            <td class="text-end" style="background-color: #f2f2f2;"><h5>Total general:</h5></td>
                            <td style="background-color: #f2f2f2;"><h5>{{ number_format($offer->total_value * 1.19, 2, ',', '.') }} RON</h5></td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <!-- Subsol -->
            <footer class="mt-5">
                <hr>
                <p class="text-muted small">
                    {!! nl2br(e($templateSettings->footer_text ?? '')) !!}
                </p>
            </footer>
        </div>
    </div>
</div>
@endsection