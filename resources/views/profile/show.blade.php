@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Profilul firmei</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">Datele companiei</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Coloana stânga -->
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Nume firmă</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $profile->company_name) }}" required>
                            @error('company_name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vat_number" class="form-label">CUI / CIF</label>
                                <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number', $profile->vat_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="trade_register_number" class="form-label">Nr. Reg. Comerțului</label>
                                <input type="text" class="form-control" id="trade_register_number" name="trade_register_number" value="{{ old('trade_register_number', $profile->trade_register_number) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresă sediu social</label>
                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $profile->address) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">E-mail contact</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ old('contact_email', $profile->contact_email) }}">
                            </div>
                                                            <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">Telefon contact</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $profile->phone_number) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="iban" class="form-label">Cont bancar (IBAN)</label>
                                    <input type="text" class="form-control" id="iban" name="iban" value="{{ old('iban', $profile->iban) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bank_name" class="form-label">Banca</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $profile->bank_name) }}">
                                </div>
                            </div>
                    </div>

                    <!-- Coloana dreapta pentru logo -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo firmă</label>
                            <div class="text-center mb-2">
                                @if ($profile->logo_path)
                                    <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                @else
                                    <div class="border p-4 text-muted">Previzualizare logo</div>
                                @endif
                            </div>
                            <input class="form-control" type="file" id="logo" name="logo">
                            <small class="form-text text-muted">Fișiere acceptate: PNG, JPG, GIF. Maxim 2MB.</small>
                        </div>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">Salvează profilul</button>
            </form>
        </div>
    </div>
</div>
@endsection