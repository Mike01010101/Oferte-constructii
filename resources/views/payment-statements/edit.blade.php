@extends('layouts.dashboard')

@section('title', 'Creează Situație de Plată')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Editează situație de plată pentru Oferta: {{ $offer->offer_number }}</h1>

    <form method="POST" action="{{ route('situatii-plata.update', $statement->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="offer_id" value="{{ $offer->id }}">
        
        {{-- Aici vom folosi o structură similară cu offers.edit, dar adaptată --}}
        {{-- Poți copia conținutul din `offers/edit.blade.php` și să faci următoarele modificări: --}}
        {{-- 1. Schimbă acțiunea formularului la `route('situatii-plata.store')` --}}
        {{-- 2. Înlocuiește variabilele `$offer->` cu `$statement->` pentru câmpurile de bază (număr, dată, obiect, note) --}}
        {{-- 3. Câmpul `status` și `assigned_to` nu mai sunt necesare. --}}
        {{-- 4. În bucla de itemi, folosește `$items` în loc de `$offer->items` --}}
        {{-- 5. Schimbă textul butonului de submit în "Salvează Situația de Plată" --}}

        {{-- Pentru a merge mai repede, folosește direct codul de mai jos --}}

        <div class="card">
            <div class="card-header">Detalii Situație de Plată</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="client_id" class="form-label">Client*</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $statement->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                         <label for="statement_number" class="form-label">Număr Situație de Plată*</label>
                         <input type="text" class="form-control" id="statement_number" name="statement_number" value="{{ old('statement_number', $statement->statement_number) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="statement_date" class="form-label">Data Situației de Plată*</label>
                        <input type="date" class="form-control" id="statement_date" name="statement_date" value="{{ old('statement_date', \Carbon\Carbon::parse($statement->statement_date)->format('Y-m-d')) }}" required>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-12 mb-3">
                        <label for="object" class="form-label">Obiectul lucrării</label>
                        <input type="text" class="form-control" id="object" name="object" value="{{ old('object', $statement->object) }}">
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Aici urmează cardul cu itemii, exact ca în offers.edit --}}
        @include('offers.partials.items-table', ['items' => $items, 'settings' => $settings])

        <div class="card mt-4">
            <div class="card-header">Note adiționale</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="3">{{ old('notes', $statement->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Salvează Situația de Plată</button>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Anulează</a>
        </div>
    </form>
</div>
@endsection