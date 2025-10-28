@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă oferte</h1>
        <a href="{{ route('oferte.create') }}" class="btn btn-primary">Adaugă ofertă nouă</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nr. Ofertă</th>
                        <th>Client</th>
                        <th>Data</th>
                        <th class="text-end">Valoare totală</th>
                        <th>Status</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td><strong>{{ $offer->offer_number }}</strong></td>
                            <td>{{ $offer->client->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format($offer->total_value, 2, ',', '.') }} RON</td>
                            <td><span class="badge bg-secondary">{{ $offer->status }}</span></td>
                            <td>
                                <a href="{{ route('oferte.show', $offer->id) }}" class="btn btn-sm btn-info">Vezi</a>
                                </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nu aveți nicio ofertă creată.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">{{ $offers->links() }}</div>
        </div>
    </div>
</div>
@endsection