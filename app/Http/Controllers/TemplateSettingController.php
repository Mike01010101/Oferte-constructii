<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TemplateSettingController extends Controller
{
    /**
     * Afișează formularul pentru creatorul de șabloane.
     */
    public function show()
    {
        $settings = Auth::user()->templateSetting;

        return view('template-settings.show', compact('settings'));
    }

    /**
     * Salvează sau actualizează setările de șablon.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'accent_color' => 'required|string|starts_with:#|size:7', // Validează formatul hex de culoare
            'logo_alignment' => ['required', Rule::in(['left', 'center', 'right'])],
            'footer_text' => 'nullable|string',
        ]);

        $user->templateSetting()->updateOrCreate(
            ['user_id' => $user->id],
            $validatedData
        );

        return redirect()->route('template.show')->with('success', 'Setările șablonului au fost salvate!');
    }
}