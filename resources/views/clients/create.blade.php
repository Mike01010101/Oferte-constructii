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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('cui-search-btn');
    const searchInput = document.getElementById('cui-search-input');
    const apiResult = document.getElementById('api-result');

    // Mapparea câmpurilor din formular
    const formFields = {
        name: document.getElementById('name'),
        vat_number: document.getElementById('vat_number'),
        trade_register_number: document.getElementById('trade_register_number'),
        address: document.getElementById('address')
    };

    const searchFunction = () => {
        const cui = searchInput.value.trim().toUpperCase().replace('RO', '');
        if (!cui) {
            apiResult.textContent = 'Vă rugăm introduceți un CUI.';
            apiResult.className = 'mt-2 small text-danger';
            return;
        }

        apiResult.textContent = 'Se caută...';
        apiResult.className = 'mt-2 small text-muted';
        
        fetch(`https://lista-firme.info/api/v1/info?cui=${cui}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.name) {
                    const { name, cui: cuiVal, reg_com, address = {} } = data;
                    
                    // Pre-completăm câmpurile formularului
                    formFields.name.value = name || '';
                    formFields.vat_number.value = `RO${cuiVal}` || '';
                    formFields.trade_register_number.value = reg_com || '';

                    // Construim adresa completă din părțile disponibile
                    const adresaParts = [];
                    if (address.county) adresaParts.push(address.county);
                    if (address.city) adresaParts.push(address.city);
                    if (address.street) adresaParts.push(`Str. ${address.street}`);
                    if (address.number) adresaParts.push(`Nr. ${address.number}`);
                    formFields.address.value = adresaParts.join(', ');

                    apiResult.textContent = `Firmă găsită: ${name}. Câmpurile au fost pre-completate.`;
                    apiResult.className = 'mt-2 small text-success';
                } else {
                    apiResult.textContent = "Firmă negăsită sau CUI invalid.";
                    apiResult.className = 'mt-2 small text-danger';
                }
            })
            .catch(error => {
                console.error('API Error:', error);
                apiResult.textContent = "A apărut o eroare la interogarea API-ului.";
                apiResult.className = 'mt-2 small text-danger';
            });
    };

    searchBtn.addEventListener('click', searchFunction);
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchFunction();
        }
    });
});
</script>
@endpush
@endsection