<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
    /**
     * Afișează formularul cu profilul firmei.
     */
    public function show()
    {
        // Preluăm profilul de la firmă
        $profile = Auth::user()->company->companyProfile ?? new CompanyProfile();
        
        return view('profile.show', compact('profile'));
    }

    /**
     * Salvează sau actualizează profilul firmei.
     */
    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'trade_register_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Gestionarea upload-ului pentru logo
        if ($request->hasFile('logo')) {
            // Ștergem logo-ul vechi dacă există
            if ($company->companyProfile && $company->companyProfile->logo_path) {
                Storage::disk('public')->delete($company->companyProfile->logo_path);
            }
            
            // Salvăm noul logo și obținem calea
            $path = $request->file('logo')->store('logos', 'public');
            $validatedData['logo_path'] = $path;
        }

        // Actualizăm sau creăm profilul folosind relația de la firmă
        $company->companyProfile()->updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('profile.show')->with('success', 'Profilul firmei a fost salvat cu succes!');
    }
}