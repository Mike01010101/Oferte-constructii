<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'start_number' => 'required|integer|min:1',
            'vat_percentage' => 'required|numeric|min:0',
            // Validăm doar câmpurile care ar putea fi trimise
            'summary_cam_percentage' => 'nullable|numeric|min:0',
            'summary_indirect_percentage' => 'nullable|numeric|min:0',
            'summary_profit_percentage' => 'nullable|numeric|min:0',
        ]);

        // Tratăm manual checkbox-urile: dacă nu sunt trimise, le setăm valoarea la 0 (false)
        $validatedData['include_summary_in_prices'] = $request->has('include_summary_in_prices');
        // Dacă noua opțiune e bifată, forțăm cealaltă să fie falsă
        if ($validatedData['include_summary_in_prices']) {
            $validatedData['show_summary_block'] = false;
        }
        $validatedData['show_material_column'] = $request->has('show_material_column');
        $validatedData['show_labor_column'] = $request->has('show_labor_column');
        $validatedData['show_equipment_column'] = $request->has('show_equipment_column');
        $validatedData['show_unit_price_column'] = $request->has('show_unit_price_column');
        $validatedData['show_summary_block'] = $request->has('show_summary_block');
        
        $currentSettings = $company->offerSetting;

        if ($currentSettings) {
            if ($validatedData['start_number'] > $currentSettings->next_number) {
                $validatedData['next_number'] = $validatedData['start_number'];
            }
            $currentSettings->update($validatedData);
        } else {
            $validatedData['next_number'] = $validatedData['start_number'];
            $company->offerSetting()->create($validatedData);
        }

        return redirect()->route('offer-settings.show')->with('success', 'Setările de ofertare au fost salvate cu succes!');
    }
}