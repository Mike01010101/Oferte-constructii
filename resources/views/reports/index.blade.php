@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Rapoarte și statistici</h1>

    <!-- Carduri cu statistici -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text text-uppercase small">Număr total oferte</div>
                        <div class="h4 fw-bold mb-0">{{ $totalOffers }}</div>
                    </div>
                    <div class="ms-3">
                        <i class="fa-solid fa-file-invoice fa-2x text-tertiary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text text-uppercase small">Valoare oferte acceptate</div>
                        <div class="h4 fw-bold mb-0">{{ number_format($totalValueAccepted, 2, ',', '.') }} RON</div>
                    </div>
                    <div class="ms-3">
                        <i class="fa-solid fa-dollar-sign fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel cu ultimele oferte -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ultimele 5 oferte create</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nr. Ofertă</th>
                            <th>Client</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th class="text-end">Valoare</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestOffers as $offer)
                            <tr>
                                <td><a href="{{ route('oferte.show', $offer->id) }}">{{ $offer->offer_number }}</a></td>
                                <td>{{ $offer->client->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</td>
                                <td><span class="badge">{{ $offer->status }}</span></td>
                                <td class="text-end">{{ number_format($offer->total_value, 2, ',', '.') }} RON</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Nu există oferte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection