<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Afișează o listă cu toți clienții utilizatorului.
     */
    public function index()
    {
        $clients = Auth::user()->clients()->latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }

    /**
     * Afișează formularul pentru adăugarea unui client nou.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Salvează un client nou în baza de date.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'trade_register_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Auth::user()->clients()->create($validatedData);

        return redirect()->route('clienti.index')->with('success', 'Clientul a fost adăugat cu succes.');
    }
}