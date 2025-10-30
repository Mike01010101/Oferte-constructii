@extends('layouts.dashboard')

@section('title', 'Rapoarte și statistici')

@section('content')
<div class="container-fluid">
    <!-- Antet și Filtru de Perioadă -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-3 mb-md-0">Rapoarte și statistici</h1>
        
        <form method="GET" action="{{ route('rapoarte.index') }}" class="d-flex align-items-center gap-2">
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i></button>
            <a href="{{ route('rapoarte.index') }}" class="btn btn-secondary" title="Resetează filtrul"><i class="fa-solid fa-undo"></i></a>
        </form>
    </div>

    <!-- Carduri cu statistici (KPIs) -->
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

    <!-- Rând cu grafice -->
    <div class="row">
        <!-- Grafic Distribuție Statusuri -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Distribuție statusuri</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="statusDonutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafic Evoluție Valorică -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">Evoluție valorică lunară</div>
                <div class="card-body">
                    <canvas id="monthlyValuesBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grafic Top Clienți -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">Top 5 clienți (după valoare)</div>
                <div class="card-body">
                    <canvas id="topClientsChart"></canvas>
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