@extends('layouts.dashboard')

@section('title', 'Situații de Plată')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Listă Situații de Plată</h1>

    {{-- Aici poți adăuga o bară de căutare similară cu cea de la oferte --}}

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nr. Situație</th>
                            <th>Ofertă Originală</th>
                            <th>Client</th>
                            <th>Data</th>
                            <th class="text-end">Valoare</th>
                            <th class="text-center">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statements as $statement)
                            <tr>
                                <td><strong>{{ $statement->statement_number }}</strong></td>
                                <td><a href="{{ route('oferte.show', $statement->offer_id) }}">{{ $statement->offer->offer_number }}</a></td>
                                <td>{{ $statement->client->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($statement->statement_date)->format('d.m.Y') }}</td>
                                <td class="text-end">{{ number_format($statement->total_value, 2, ',', '.') }} RON</td>
                                <td class="text-center">
                                    <a href="{{ route('situatii-plata.edit', $statement->id) }}" class="btn btn-sm btn-secondary" title="Editează"><i class="fa-solid fa-pencil"></i></a>
                                    <a href="{{ route('situatii-plata.pdf', $statement->id) }}" class="btn btn-sm btn-secondary" title="Descarcă PDF" target="_blank" data-swup-ignore>
                                        <i class="fa-solid fa-file-pdf text-danger"></i>
                                    </a>
                                    {{-- Aici poți adăuga și un buton de ștergere --}}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Nu există nicio situație de plată.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             <div class="mt-3">{{ $statements->links() }}</div>
        </div>
    </div>
</div>
@endsection