@extends('layouts.dashboard')

@section('title', 'Rapoarte și statistici')

@section('content')
<div class="container-fluid">
    <!-- NOU: Antet și Filtru de Perioadă (Stil Vue/Inertia) -->
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

    <!-- Carduri cu statistici (KPIs) - Neschimbate -->
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
                    <div class="text text-uppercase small text-tertiary">Valoare totală</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($totalValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Total facturat</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($totalInvoicedValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Facturat (neîncasat)</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($invoicedUnpaidValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text text-uppercase small text-tertiary">Total încasat</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($cashedInValue, 2, ',', '.') }} RON</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rând cu grafice - Neschimbate -->
    <div class="row">
        <div class="col-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Distribuție statusuri</div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="statusDonutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">Evoluție valorică lunară</div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="monthlyValuesBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">Top 5 clienți (după valoare)</div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:350px;">
                        <canvas id="topClientsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Expunem datele pentru ca app.js să le poată folosi --}}
<script id="reports-data" type="application/json">
{
    "statusDistribution": @json($statusDistribution),
    "monthlyChartData": @json($monthlyChartData),
    "topClients": @json($topClients)
}
</script>
@endpush