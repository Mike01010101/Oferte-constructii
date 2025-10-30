@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Adaugă un client nou</h1>

    <!-- NOU: Secțiunea de căutare după CUI -->
    <div class="card mb-4">
        <div class="card-header">Caută rapid firmă după CUI</div>
        <div class="card-body">
            <div class="input-group">
                <input type="text" id="cui-search-input" class="form-control" placeholder="Introduceți CUI-ul firmei...">
                <button class="btn btn-primary" type="button" id="cui-search-btn">Caută firmă</button>
            </div>
            <div id="api-result" class="mt-2 small text-muted"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('clienti.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nume client / firmă*</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vat_number" class="form-label">CUI / CIF</label>
                        <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number') }}">
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="trade_register_number" class="form-label">Nr. Reg. Comerțului</label>
                        <input type="text" class="form-control" id="trade_register_number" name="trade_register_number" value="{{ old('trade_register_number') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Adresă</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="contact_person" class="form-label">Persoană de contact</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">Salvează clientul</button>
                <a href="{{ route('clienti.index') }}" class="btn btn-secondary">Anulează</a>
            </form>
        </div>
    </div>
</div>

@endsection