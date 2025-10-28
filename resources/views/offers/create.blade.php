@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Creează o ofertă nouă</h1>

    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    
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
                         <label for="offer_number" class="form-label">Număr ofertă</label>
                         <input type="text" class="form-control" id="offer_number" name="offer_number" value="{{ $offerNumber }}" readonly>
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
                <button type="button" id="add-item-btn" class="btn btn-sm btn-success">Adaugă rând</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Descriere</th>
                            <th>Cant.</th>
                            <th>U.M.</th>
                            <th>Preț unitar</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="offer-items-tbody">
                        {{-- Rândurile vor fi adăugate aici de JavaScript --}}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end border-0"><strong>Total General:</strong></td>
                            <td class="text-end border-0"><strong id="grand-total">0.00 RON</strong></td>
                            <td class="border-0"></td>
                        </tr>
                    </tfoot>
                </table>
                 @error('items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Note adiționale</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Salvează oferta</button>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Anulează</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-item-btn');
    const tbody = document.getElementById('offer-items-tbody');
    const grandTotalElem = document.getElementById('grand-total');
    let itemIndex = 0;

    function addRow() {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity" step="0.01" value="1" required></td>
            <td><input type="text" name="items[${itemIndex}][unit_measure]" class="form-control form-control-sm" value="buc" required></td>
            <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm unit-price" step="0.01" value="0.00" required></td>
            <td class="text-end line-total">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn">Șterge</button></td>
        `;
        tbody.appendChild(row);
        itemIndex++;
        updateEventListeners();
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.line-total').forEach(line => {
            total += parseFloat(line.textContent.replace(',', '.'));
        });
        grandTotalElem.textContent = total.toFixed(2).replace('.', ',') + ' RON';
    }

    function updateEventListeners() {
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.onclick = function() {
                this.closest('tr').remove();
                updateGrandTotal();
            }
        });

        document.querySelectorAll('.quantity, .unit-price').forEach(input => {
            input.oninput = function() {
                const row = this.closest('tr');
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const price = parseFloat(row.querySelector('.unit-price').value) || 0;
                const lineTotal = qty * price;
                row.querySelector('.line-total').textContent = lineTotal.toFixed(2).replace('.', ',');
                updateGrandTotal();
            }
        });
    }
    
    addBtn.addEventListener('click', addRow);
    // Adaugă un rând la încărcarea paginii
    addRow();
});
</script>
@endsection