@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă clienți</h1>
        <a href="{{ route('clienti.create') }}" class="btn btn-primary">Adaugă client nou</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nume client</th>
                        <th>CUI / CIF</th>
                        <th>Persoană de contact</th>
                        <th>Telefon</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->vat_number ?? '-' }}</td>
                            <td>{{ $client->contact_person ?? '-' }}</td>
                            <td>{{ $client->phone ?? '-' }}</td>
                            <td>
                                {{-- Aici vom adăuga butoane de Editare / Ștergere --}}
                                <a href="#" class="btn btn-sm btn-secondary">Editează</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nu aveți niciun client adăugat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection