<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        Articole
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
                    <tr>
                        <th style="width: 30%;">Descriere</th>
                        <th style="width: 8%;">U.M.</th>
                        <th style="width: 8%;">Cant.</th>
                        @if($settings->show_material_column) <th class="price-col">{{ $settings->material_column_name ?? 'Material' }} <span class="price-mode-label">(unitar)</span></th> @endif
                        @if($settings->show_labor_column) <th class="price-col">{{ $settings->labor_column_name ?? 'Manoperă' }} <span class="price-mode-label">(unitar)</span></th> @endif
                        @if($settings->show_equipment_column) <th class="price-col">{{ $settings->equipment_column_name ?? 'Utilaj' }} <span class="price-mode-label">(unitar)</span></th> @endif
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
                    
                    @foreach (old('items', $items->toArray()) as $index => $item)
                        <tr class="offer-item-row">
                            <td>
                                <input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm description-input" value="{{ $item['description'] }}" required>
                                <input type="hidden" name="items[{{ $index }}][material_price]" class="price-input-hidden material-price-hidden" value="{{ $item['material_price'] ?? '0.00' }}">
                                <input type="hidden" name="items[{{ $index }}][labor_price]" class="price-input-hidden labor-price-hidden" value="{{ $item['labor_price'] ?? '0.00' }}">
                                <input type="hidden" name="items[{{ $index }}][equipment_price]" class="price-input-hidden equipment-price-hidden" value="{{ $item['equipment_price'] ?? '0.00' }}">
                            </td>
                            <td><input type="text" name="items[{{ $index }}][unit_measure]" class="form-control form-control-sm" value="{{ $item['unit_measure'] }}" required></td>
                            <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm quantity" step="0.01" value="{{ $item['quantity'] }}" required></td>
                            @if($settings->show_material_column) <td><input type="number" class="form-control form-control-sm price-input-visible material-price-visible" step="0.01" value="{{ number_format($item['material_price'] ?? 0, 4, '.', '') }}"></td> @endif
                            @if($settings->show_labor_column) <td><input type="number" class="form-control form-control-sm price-input-visible labor-price-visible" step="0.01" value="{{ $item['labor_price'] ?? '0.00' }}"></td> @endif
                            @if($settings->show_equipment_column) <td><input type="number" class="form-control form-control-sm price-input-visible equipment-price-visible" step="0.01" value="{{ $item['equipment_price'] ?? '0.00' }}"></td> @endif
                            @if($settings->show_unit_price_column) <td class="text-end align-middle unit-price-total">0.00</td> @endif
                            <td class="text-end align-middle line-total">0.00</td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn">X</button></td>
                        </tr>
                    @endforeach
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