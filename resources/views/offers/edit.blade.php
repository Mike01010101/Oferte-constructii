@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Editează oferta: {{ $offer->offer_number }}</h1>

    <form method="POST" action="{{ route('oferte.update', $offer->id) }}">
        @csrf
        @method('PUT')
        
        <div class="card">
            <div class="card-header">Detalii ofertă</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="client_id" class="form-label">Client*</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $offer->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                         <label for="offer_number" class="form-label">Număr ofertă*</label>
                         <input type="text" class="form-control" id="offer_number" name="offer_number" value="{{ old('offer_number', $offer->offer_number) }}" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="offer_date" class="form-label">Data ofertei*</label>
                        <input type="date" class="form-control" id="offer_date" name="offer_date" value="{{ old('offer_date', \Carbon\Carbon::parse($offer->offer_date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status*</label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach ($statuses as $key => $value)
                                <option value="{{ $key }}" {{ old('status', $offer->status) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="assigned_to_user_id" class="form-label">Alocată lui</label>
                        <select class="form-select" id="assigned_to_user_id" name="assigned_to_user_id">
                            <option value="">N/A</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to_user_id', $offer->assigned_to_user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    </div>
                {{-- NOU: Câmpul pentru obiectul ofertei --}}
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="object" class="form-label">Obiectul ofertei</label>
                        <input type="text" class="form-control" id="object" name="object" value="{{ old('object', $offer->object) }}" placeholder="Ex: Renovare apartament, Construcție gard, etc.">
                    </div>
                </div>
            </div>
        </div>
        
             @include('offers.partials.items-table', ['items' => $offer->items, 'settings' => $settings])

        <div class="card mt-4">
            <div class="card-header">Note adiționale</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="3">{{ old('notes', $offer->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Salvează modificările</button>
            <a href="{{ route('oferte.index') }}" class="btn btn-secondary">Anulează</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const settings = {
        showMaterial: {{ ($settings->show_material_column ?? false) ? 'true' : 'false' }},
        showLabor: {{ ($settings->show_labor_column ?? false) ? 'true' : 'false' }},
        showEquipment: {{ ($settings->show_equipment_column ?? false) ? 'true' : 'false' }},
        showUnitPrice: {{ ($settings->show_unit_price_column ?? false) ? 'true' : 'false' }},
    };

    const addBtn = document.getElementById('add-item-btn');
    const tbody = document.getElementById('offer-items-tbody');
    const grandTotalElem = document.getElementById('grand-total');
    const priceModeToggle = document.getElementById('price-mode-toggle');
    const priceModeLabels = document.querySelectorAll('.price-mode-label');
    let itemIndex = 0;

    function isTotalMode() {
        return priceModeToggle.checked;
    }

    
    function addRow() {
        const row = document.createElement('tr');
        row.classList.add('offer-item-row');
        let priceCells = '';
        
        if (settings.showMaterial) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible material-price-visible" step="0.01" value="0.00"></td>`;
        if (settings.showLabor) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible labor-price-visible" step="0.01" value="0.00"></td>`;
        if (settings.showEquipment) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible equipment-price-visible" step="0.01" value="0.00"></td>`;
        
        let hiddenPriceInputs = `
            <input type="hidden" name="items[${itemIndex}][material_price]" class="price-input-hidden material-price-hidden" value="0.00">
            <input type="hidden" name="items[${itemIndex}][labor_price]" class="price-input-hidden labor-price-hidden" value="0.00">
            <input type="hidden" name="items[${itemIndex}][equipment_price]" class="price-input-hidden equipment-price-hidden" value="0.00">
        `;

        if (settings.showUnitPrice) priceCells += `<td class="text-end align-middle unit-price-total">0.00</td>`;
        
        row.innerHTML = `
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm description-input" required>
                ${hiddenPriceInputs}
            </td>
            <td><input type="text" name="items[${itemIndex}][unit_measure]" class="form-control form-control-sm" value="buc" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity" step="0.01" value="1" required></td>
            ${priceCells}
            <td class="text-end align-middle line-total">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn">X</button></td>
        `;
        tbody.appendChild(row);
        updateEventListenersForRow(row);
        updatePriceModeLabels();

        row.querySelector('.description-input').focus();
        
        itemIndex++;
    }

    function togglePriceMode() {
        const newLabel = isTotalMode() ? '(total)' : '(unitar)';
        priceModeLabels.forEach(label => label.textContent = newLabel);
        
        // NOUA LOGICĂ: Recalculăm ce se afișează în input-uri la comutare
        document.querySelectorAll('.offer-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitMaterial = parseFloat(row.querySelector('.material-price-hidden').value) || 0;
            const unitLabor = parseFloat(row.querySelector('.labor-price-hidden').value) || 0;
            const unitEquipment = parseFloat(row.querySelector('.equipment-price-hidden').value) || 0;

            const materialInput = row.querySelector('.material-price-visible');
            const laborInput = row.querySelector('.labor-price-visible');
            const equipmentInput = row.querySelector('.equipment-price-visible');

            if (isTotalMode()) {
                // Comutăm pe modul TOTAL: afișăm valoarea totală (unitar * cantitate)
                if (materialInput) materialInput.value = (unitMaterial * qty).toFixed(2);
                if (laborInput) laborInput.value = (unitLabor * qty).toFixed(2);
                if (equipmentInput) equipmentInput.value = (unitEquipment * qty).toFixed(2);
            } else {
                // Comutăm pe modul UNITAR: afișăm valoarea unitară
                if (materialInput) materialInput.value = unitMaterial.toFixed(2);
                if (laborInput) laborInput.value = unitLabor.toFixed(2);
                if (equipmentInput) equipmentInput.value = unitEquipment.toFixed(2);
            }
        });

        updateCalculations();
    }

    function updateCalculations() {
        let grandTotal = 0;
        document.querySelectorAll('.offer-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            
            let materialValue = parseFloat(row.querySelector('.material-price-visible')?.value) || 0;
            let laborValue = parseFloat(row.querySelector('.labor-price-visible')?.value) || 0;
            let equipmentValue = parseFloat(row.querySelector('.equipment-price-visible')?.value) || 0;

            let unitMaterial, unitLabor, unitEquipment;

            if (isTotalMode()) {
                unitMaterial = (qty > 0) ? materialValue / qty : 0;
                unitLabor = (qty > 0) ? laborValue / qty : 0;
                unitEquipment = (qty > 0) ? equipmentValue / qty : 0;
            } else {
                unitMaterial = materialValue;
                unitLabor = laborValue;
                unitEquipment = equipmentValue;
            }

            if(row.querySelector('.material-price-hidden')) row.querySelector('.material-price-hidden').value = unitMaterial.toFixed(2);
            if(row.querySelector('.labor-price-hidden')) row.querySelector('.labor-price-hidden').value = unitLabor.toFixed(2);
            if(row.querySelector('.equipment-price-hidden')) row.querySelector('.equipment-price-hidden').value = unitEquipment.toFixed(2);
            
            const unitPriceTotal = unitMaterial + unitLabor + unitEquipment;
            const lineTotal = qty * unitPriceTotal;

            if (settings.showUnitPrice) row.querySelector('.unit-price-total').textContent = unitPriceTotal.toFixed(2);
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            grandTotal += lineTotal;
        });
        grandTotalElem.textContent = grandTotal.toFixed(2) + ' RON';
    }
    
    function updatePriceModeLabels() {
        const newLabel = isTotalMode() ? '(total)' : '(unitar)';
        priceModeLabels.forEach(label => label.textContent = newLabel);
    }

    priceModeToggle.addEventListener('change', togglePriceMode);
    
    function updateEventListenersForRow(row) {
        row.querySelector('.remove-item-btn').onclick = function() {
            this.closest('tr').remove();
            updateCalculations();
        };
        row.querySelectorAll('.quantity, .price-input-visible').forEach(input => {
            input.oninput = updateCalculations;
        });
    }

    tbody.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') return;

        const targetInput = event.target;
        const currentRow = targetInput.closest('.offer-item-row');
        if (!currentRow) return;

        const visibleInputs = Array.from(currentRow.querySelectorAll('input:not([type="hidden"])'));
        const lastVisibleInput = visibleInputs[visibleInputs.length - 1];
        const allRows = Array.from(tbody.querySelectorAll('.offer-item-row'));
        const lastRow = allRows[allRows.length - 1];

        if (currentRow === lastRow && targetInput === lastVisibleInput) {
            event.preventDefault();
            addRow();
        }
    });
    
    addBtn.addEventListener('click', addRow);
    
    // Pentru pagina de editare, trebuie să pre-populăm rândurile existente
    if (tbody.children.length > 0) {
        itemIndex = tbody.children.length;
        document.querySelectorAll('.offer-item-row').forEach(row => {
            updateEventListenersForRow(row);
        });
        updateCalculations();
    } else {
        // Pentru pagina de creare, adăugăm primul rând
        addRow();
    }
});
</script>
@endpush
@endsection

<style>
.price-col { min-width: 120px; }
</style>