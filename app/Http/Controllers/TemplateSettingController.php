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

        // Am actualizat regulile de validare pentru a include toate opțiunile noi
        $validatedData = $request->validate([
            'layout' => ['required', Rule::in(['classic', 'modern', 'compact', 'elegant', 'minimalist'])],
            'document_title' => 'required|string|max:255',
            'font_family' => ['required', Rule::in(['Roboto', 'Lato', 'Merriweather', 'Open Sans', 'Montserrat', 'Source Serif Pro'])],
            'table_style' => ['required', Rule::in(['grid', 'striped'])],
            'accent_color' => 'required|string|starts_with:#|size:7',
            'footer_text' => 'nullable|string|max:5000',
        ]);

        $company->templateSetting()->updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('template.show')->with('success', 'Setările șablonului au fost salvate cu succes!');
    }
}