@extends('layouts.dashboard')
@section('title', 'Panou de control')
@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Panou de control</h1>
    <div class="row">
    <!-- Coloana stânga: Ofertele Mele -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-user-tag me-2"></i> Ofertele mele active</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nr. Ofertă</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th class="text-end">Valoare</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myOpenOffers as $offer)
                                <tr>
                                    <td><a href="{{ route('oferte.show', $offer->id) }}"><strong>{{ $offer->offer_number }}</strong></a></td>
                                    <td>{{ $offer->client->name }}</td>
                                    <td><span class="badge {{ $offer->getStatusColorClass() }}">{{ $offer->status }}</span></td>
                                    <td class="text-end">{{ number_format($offer->total_value, 2, ',', '.') }} RON</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nu ai nicio ofertă activă alocată.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Coloana dreapta: Sumar Statusuri -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-chart-pie me-2"></i> Sumar oferte companie</h5>
            </div>
            <div class="card-body">
                @if($offerStatusSummary->isEmpty())
                    <p class="text-center text-muted">Nu există nicio ofertă în sistem.</p>
                @else
                    <ul class="list-group list-group-flush">
                            @foreach (App\Models\Offer::STATUSES as $statusKey => $statusName)
                                @if($offerStatusSummary->has($statusKey))
                                    @php
                                        // Folosim noua funcție pentru a obține culoarea
                                        $colorName = App\Models\Offer::getColorNameForStatus($statusKey);
                                    @endphp
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $statusName }}
                                        {{-- Am adăugat clasele 'text-bg-*' și am mărit textul --}}
                                        <span class="badge text-bg-{{ $colorName }} rounded-pill fs-6">
                                            {{ $offerStatusSummary[$statusKey] }}
                                        </span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection