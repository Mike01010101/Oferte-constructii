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
        $settings = Auth::user()->offerSetting;

        return view('offer-settings.show', compact('settings'));
    }

    /**
     * Salvează sau actualizează setările de ofertare.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'start_number' => 'required|integer|min:1',
        ]);
        
        // Verificăm dacă există deja setări
        $currentSettings = $user->offerSetting;

        if ($currentSettings) {
            // Dacă utilizatorul schimbă numărul de start și acesta este mai mare decât
            // numărul curent la care s-a ajuns, actualizăm și `next_number`.
            // Altfel, păstrăm progresul.
            if ($validatedData['start_number'] > $currentSettings->next_number) {
                $validatedData['next_number'] = $validatedData['start_number'];
            }
            
            $currentSettings->update($validatedData);

        } else {
            // Dacă nu există setări, creăm un rând nou.
            // `next_number` va fi același cu `start_number`.
            $validatedData['next_number'] = $validatedData['start_number'];
            $user->offerSetting()->create($validatedData);
        }

        return redirect()->route('offer-settings.show')->with('success', 'Setările de ofertare au fost salvate!');
    }
}