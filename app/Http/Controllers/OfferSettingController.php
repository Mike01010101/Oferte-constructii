<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OfferSettingController extends Controller
{
    /**
     * Afișează formularul cu setările de ofertare.
     */
    public function show()
    {
        $settings = Auth::user()->company->offerSetting;
        return view('offer-settings.show', compact('settings'));
    }

    /**
     * Salvează sau actualizează setările de ofertare.
     */
    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $validatedData = $request->validate([
            'numbering_mode' => ['required', Rule::in(['auto', 'manual'])],
            'prefix' => 'nullable|string|max:50',
            'start_number' => 'required_if:numbering_mode,auto|integer|min:1',
            'vat_percentage' => 'required|numeric|min:0',
            'summary_cam_percentage' => 'nullable|numeric|min:0',
            'summary_indirect_percentage' => 'nullable|numeric|min:0',
            'summary_profit_percentage' => 'nullable|numeric|min:0',
            'pdf_price_display_mode' => ['required', Rule::in(['unit', 'total'])],
            
            // NOU: Regulile de validare pentru noile câmpuri
            'material_column_name' => 'nullable|string|max:100',
            'labor_column_name' => 'nullable|string|max:100',
            'equipment_column_name' => 'nullable|string|max:100',
        ]);

        // Tratăm manual TOATE checkbox-urile: dacă nu sunt trimise, valoarea lor va fi false (0)
        $validatedData['show_unit_price_column'] = $request->has('show_unit_price_column');
        $validatedData['include_summary_in_prices'] = $request->has('include_summary_in_prices');
        $validatedData['show_material_column'] = $request->has('show_material_column');
        $validatedData['show_labor_column'] = $request->has('show_labor_column');
        $validatedData['show_equipment_column'] = $request->has('show_equipment_column');
        
        // NOU: Tratăm noile checkbox-uri pentru totaluri
        $validatedData['show_material_total'] = $request->has('show_material_total');
        $validatedData['show_labor_total'] = $request->has('show_labor_total');
        $validatedData['show_equipment_total'] = $request->has('show_equipment_total');


        // Dacă opțiunea de includere în prețuri e bifată, forțăm blocul de sumar să fie ascuns
        if ($validatedData['include_summary_in_prices']) {
            $validatedData['show_summary_block'] = false;
        } else {
            $validatedData['show_summary_block'] = $request->has('show_summary_block');
        }
        
        // Actualizăm numărul următor doar dacă modul este auto
        if ($validatedData['numbering_mode'] === 'auto' && $request->has('start_number')) {
            $validatedData['next_number'] = $validatedData['start_number'];
        }

        $company->offerSetting()->updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('offer-settings.show')->with('success', 'Setările de ofertare au fost salvate!');
    }
}