<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TemplateSettingController extends Controller
{
    public function show()
    {
        $settings = Auth::user()->company->templateSetting;
        return view('template-settings.show', compact('settings'));
    }

    public function update(Request $request)
    {
        $company = Auth::user()->company;
        $settings = $company->templateSetting; // Obținem setările curente pentru a șterge fișierul vechi

        // Am adăugat validare pentru noile câmpuri: stamp și stamp_size
        $validatedData = $request->validate([
            'layout' => ['required', Rule::in(['classic', 'modern', 'compact', 'elegant', 'minimalist'])],
            'document_title' => 'required|string|max:255',
            'font_family' => ['required', Rule::in(['Roboto', 'Lato', 'Merriweather', 'Open Sans', 'Montserrat', 'Source Serif Pro'])],
            'table_style' => ['required', Rule::in(['grid', 'striped'])],
            'accent_color' => 'required|string|starts_with:#|size:7',
            'footer_text' => 'nullable|string|max:5000',
            'stamp' => 'nullable|image|mimes:png|max:2048', // Doar imagini PNG de maxim 2MB
            'stamp_size' => 'required|integer|min:50|max:300',
        ]);

        // Gestionarea încărcării fișierului de ștampilă
        if ($request->hasFile('stamp')) {
            // Ștergem ștampila veche, dacă există, pentru a nu umple spațiul de stocare
            if ($settings && $settings->stamp_path) {
                Storage::disk('public')->delete($settings->stamp_path);
            }
            // Salvăm noua ștampilă în folderul 'stamps' din 'storage/app/public'
            // și stocăm calea în baza de date
            $validatedData['stamp_path'] = $request->file('stamp')->store('stamps', 'public');
        }

        $company->templateSetting()->updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('template.show')->with('success', 'Setările șablonului au fost salvate cu succes!');
    }
}