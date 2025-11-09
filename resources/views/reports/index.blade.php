@extends('layouts.dashboard')

@section('title', 'Rapoarte și statistici')

@section('content')
<div class="container-fluid">
    <!-- Antet și Filtru de Perioadă -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4">
        <h1 class="h3 mb-0">Rapoarte și statistici</h1>
        
        <div class="card w-100 w-md-auto">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                    <span class="fw-semibold small text-muted">Filtrează perioada:</span>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">De la</span>
                        <input type="date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Până la</span>
                        <input type="date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <a href="{{ route('rapoarte.index') }}" class="btn btn-sm btn-outline-secondary" title="Resetează filtrul">
                        <i class="fa-solid fa-undo"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Carduri cu statistici (KPIs) - Cu mențiunea (fără TVA) -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-4 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Număr oferte</div>
                    <div class="h3 fw-bold mb-0">{{ $totalOffers }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Valoare totală (fără TVA)</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($totalValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Total facturat (fără TVA)</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($totalInvoicedValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Facturat, neîncasat (fără TVA)</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($invoicedUnpaidValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Total încasat (fără TVA)</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($cashedInValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rând cu datele textuale -->
    <div class="row">
        <!-- NOU: Listă Distribuție Statusuri -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Distribuție statusuri</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse ($statusDistribution as $status => $count)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $status }}
                                <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nu există date pentru perioada selectată.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- NOU: Listă Evoluție Valorică -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">Evoluție valorică lunară (fără TVA)</div>
                <div class="card-body">
                     <ul class="list-group list-group-flush">
                        @forelse ($monthlyChartData as $month => $value)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $month }}
                                <span class="fw-bold">{{ number_format($value, 2, ',', '.') }} RON</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nu există date pentru perioada selectată.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- NOU: Listă Top Clienți -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">Top 5 clienți (după valoare, fără TVA)</div>
                <div class="card-body">
                    @if (count($topClients) > 0)
                        <ol class="list-group list-group-numbered">
                            @foreach ($topClients as $client => $total)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{ $client }}</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill fs-6">{{ number_format($total, 2, ',', '.') }} RON</span>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted mb-0">Nu există date pentru perioada selectată.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Am păstrat doar logica pentru filtre, am eliminat complet codul pentru Chart.js
    const initReportsPage = () => {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        // Ieșim dacă nu suntem pe pagina de rapoarte
        if (!startDateInput || !endDateInput) {
            return;
        }

        const applyFilters = () => {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            // Construim noua URL
            const url = new URL(window.location.href);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            
            // Navigăm la noua URL (aceasta va reîncărca pagina prin mecanismul browser-ului sau Swup)
            window.location.href = url.toString();
        };

        // Adăugăm event listeneri care declanșează filtrarea la schimbarea datei
        // Adăugăm o verificare pentru a nu atașa evenimentele de multiple ori
        if (!startDateInput.listenerAttached) {
            startDateInput.addEventListener('change', applyFilters);
            endDateInput.addEventListener('change', applyFilters);
            startDateInput.listenerAttached = true;
            endDateInput.listenerAttached = true;
        }
    };

    // Apelăm funcția principală atât la încărcarea completă, cât și la navigarea prin Swup
    document.addEventListener('DOMContentLoaded', initReportsPage);
    document.addEventListener('swup:content:replace', initReportsPage);
</script>
@endpush