<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        // Validăm doar câmpurile care există în formular
        $validatedData = $request->validate([
            'layout' => ['required', Rule::in(['classic', 'modern', 'compact'])],
            'font_family' => ['required', Rule::in(['Roboto', 'Lato', 'Lora', 'Merriweather'])],
            'table_style' => ['required', Rule::in(['grid', 'striped'])],
            'accent_color' => 'required|string|starts_with:#|size:7',
            'footer_text' => 'nullable|string',
        ]);

        // Adăugăm manual valorile fixe pe care le-am eliminat din formular
        // pentru a nu se pierde din baza de date sau a avea erori
        $validatedData['document_title'] = 'DEVIZ OFERTĂ';
        $validatedData['logo_alignment'] = 'left';

        $company->templateSetting()->updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('template.show')->with('success', 'Setările șablonului au fost salvate cu succes!');
    }
}