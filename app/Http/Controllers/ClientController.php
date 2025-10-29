<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Afișează o listă cu toți clienții utilizatorului, cu funcționalitate de căutare.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');

        // Păstrăm aceeași logică de query
        $clientsQuery = Auth::user()->company
            ->clients()
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('vat_number', 'like', "%{$searchTerm}%")
                      ->orWhere('contact_person', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%");
                });
            })
            ->latest();

        $clients = $clientsQuery->paginate(15)->appends($request->query());

        // Dacă cererea este de tip AJAX (trimisă de scriptul nostru)
        if ($request->ajax()) {
            // Returnăm doar partea HTML a tabelului și paginarea
            return view('clients.partials.clients-table', compact('clients'))->render();
        }

        // Altfel, returnăm întreaga pagină (la încărcarea inițială)
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

        Auth::user()->company->clients()->create($validatedData);

        return redirect()->route('clienti.index')->with('success', 'Clientul a fost adăugat cu succes.');
    }

    /**
     * Afișează formularul de editare pentru un client.
     */
    public function edit(Client $clienti)
    {
        $client = $clienti;
        // Securitate: asigură-te că utilizatorul editează un client al firmei sale
        if ($client->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        return view('clients.edit', compact('client'));
    }

    /**
     * Actualizează datele unui client.
     */
    public function update(Request $request, Client $clienti)
    {
        $client = $clienti;
        if ($client->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'trade_register_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $client->update($validatedData);

        return redirect()->route('clienti.index')->with('success', 'Datele clientului au fost actualizate.');
    }

    /**
     * Șterge un client.
     */
    public function destroy(Client $clienti)
    {
        $client = $clienti;
        if ($client->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $client->delete();

        return redirect()->route('clienti.index')->with('success', 'Clientul a fost șters.');
    }
}