@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă clienți</h1>
        <a href="{{ route('clienti.create') }}" class="btn btn-primary">Adaugă client nou</a>
    </div>

    <!-- Bara de căutare -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" class="form-control" id="search-input" name="search" placeholder="Caută instant după nume, CUI, contact..." value="{{ request('search') }}">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Containerul unde se va încărca tabelul -->
            <div id="clients-table-container">
                @include('clients.partials.clients-table', ['clients' => $clients])
            </div>
        </div>
    </div>
</div>
@endsection