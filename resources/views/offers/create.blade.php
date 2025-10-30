@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Creează o ofertă nouă</h1>

    {{-- Afișează TOATE erorile de validare --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>A apărut o problemă:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('oferte.store') }}">
        @csrf
        
        <div class="card">
            <div class="card-header">Detalii ofertă</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="client_id" class="form-label">Client*</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">Selectează un client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="offer_number" class="form-label">Număr ofertă*</label>
                        <input type="text" class="form-control" id="offer_number" name="offer_number" 
                            value="{{ old('offer_number', $offerNumber) }}" 
                            @if($settings->numbering_mode === 'auto') readonly @endif 
                            required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="offer_date" class="form-label">Data ofertei*</label>
                        <input type="date" class="form-control @error('offer_date') is-invalid @enderror" id="offer_date" name="offer_date" value="{{ old('offer_date', now()->format('Y-m-d')) }}" required>
                         @error('offer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Elemente ofertă
                <div>
                    <div class="form-check form-check-inline form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="price-mode-toggle">
                        <label class="form-check-label" for="price-mode-toggle">Introdu prețuri totale</label>
                    </div>
                    <button type="button" id="add-item-btn" class="btn btn-sm btn-success">Adaugă poziție</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <thead>
                            <tr>
                                <th style="width: 30%;">Descriere</th>
                                <th style="width: 8%;">U.M.</th>
                                <th style="width: 8%;">Cant.</th>
                                @if($settings->show_material_column) <th class="price-col">Material <span class="price-mode-label">(unitar)</span></th> @endif
                                @if($settings->show_labor_column) <th class="price-col">Manoperă <span class="price-mode-label">(unitar)</span></th> @endif
                                @if($settings->show_equipment_column) <th class="price-col">Utilaj <span class="price-mode-label">(unitar)</span></th> @endif
                                @if($settings->show_unit_price_column) <th class="price-col text-end">Preț Unitar</th> @endif
                                <th class="price-col text-end">Total</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="offer-items-tbody" 
                            data-show-material="{{ $settings->show_material_column ? 'true' : 'false' }}"
                            data-show-labor="{{ $settings->show_labor_column ? 'true' : 'false' }}"
                            data-show-equipment="{{ $settings->show_equipment_column ? 'true' : 'false' }}"
                            data-show-unit-price="{{ $settings->show_unit_price_column ? 'true' : 'false' }}">
                            {{-- Rândurile generate de JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="{{ 3 + ($settings->show_material_column ? 1:0) + ($settings->show_labor_column ? 1:0) + ($settings->show_equipment_column ? 1:0) + ($settings->show_unit_price_column ? 1:0) }}" class="text-end border-0"><strong>Total General:</strong></td>
                                <td class="text-end border-0"><strong id="grand-total">0.00 RON</strong></td>
                                <td class="border-0"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- SECȚIUNEA DE JOS A FORMULARULUI (LIPSEA) -->
        <div class="card mt-4">
            <div class="card-header">Note adiționale</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="3" placeholder="Informații suplimentare, termene, condiții speciale...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Salvează oferta</button>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Anulează</a>
        </div>
    </form>
</div>
@endsection
