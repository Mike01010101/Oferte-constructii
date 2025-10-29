@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Editează client: {{ $client->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('clienti.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nume client / firmă*</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $client->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vat_number" class="form-label">CUI / CIF</label>
                        <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}">
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="trade_register_number" class="form-label">Nr. Reg. Comerțului</label>
                        <input type="text" class="form-control" id="trade_register_number" name="trade_register_number" value="{{ old('trade_register_number', $client->trade_register_number) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Adresă</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $client->address) }}">
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="contact_person" class="form-label">Persoană de contact</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $client->contact_person) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">Salvează modificările</button>
                <a href="{{ route('clienti.index') }}" class="btn btn-secondary">Anulează</a>
            </form>
        </div>
    </div>
</div>
@endsection