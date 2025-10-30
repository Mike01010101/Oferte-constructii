<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Services\OfferCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    /**
     * Afișează lista de oferte.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');

        $offersQuery = Auth::user()->company->offers()
            ->with(['client', 'assignedTo'])
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('offer_number', 'like', "%{$searchTerm}%")
                      ->orWhere('status', 'like', "%{$searchTerm}%")
                      // Căutare în relația cu clienții
                      ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                          $clientQuery->where('name', 'like', "%{$searchTerm}%");
                      })
                      // NOU: Căutare în relația cu articolele din ofertă
                      ->orWhereHas('items', function ($itemsQuery) use ($searchTerm) {
                          $itemsQuery->where('description', 'like', "%{$searchTerm}%");
                      });
                });
            })
            ->latest();

        $offers = $offersQuery->paginate(15)->appends($request->query());

        // NOU: Definim variabila $users care lipsea
        $users = Auth::user()->company->users()->orderBy('name')->get();

        if ($request->ajax()) {
            // Trimitem și utilizatorii la cererile AJAX, pentru ca dropdown-ul
            // să funcționeze și după căutarea live
            return view('offers.partials.offers-table', compact('offers', 'users'))->render();
        }

        return view('offers.index', compact('offers', 'users'));
    }

    /**
     * Afișează formularul de creare a unei oferte noi.
     */
    public function create()
    {
        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $settings = $company->offerSetting;

        if (!$settings) {
            return redirect()->route('offer-settings.show')->with('error', 'Vă rugăm să configurați mai întâi setările de ofertare.');
        }

        $offerNumber = ''; // Default este gol pentru modul manual
        if ($settings->numbering_mode === 'auto') {
            // Am eliminat 'suffix' și am adăugat anul curent
            $offerNumber = ($settings->prefix ?? '') . $settings->next_number . '/' . now()->year;
        }
        
        return view('offers.create', compact('clients', 'offerNumber', 'settings'));
    }

    /**
     * Salvează o ofertă nouă în baza de date.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'offer_number' => 'required|string|unique:offers,offer_number',
            'offer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_measure' => 'required|string|max:20',
            'items.*.material_price' => 'nullable|numeric|min:0',
            'items.*.labor_price' => 'nullable|numeric|min:0',
            'items.*.equipment_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            $company = Auth::user()->company;
            $items = $validatedData['items'];

            $subtotal_de_baza = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['material_price'] ?? 0) + ($item['labor_price'] ?? 0) + ($item['equipment_price'] ?? 0);
                $subtotal_de_baza += ($item['quantity'] ?? 0) * $lineTotal;
            }

            $offer = $company->offers()->create([
                'client_id' => $validatedData['client_id'],
                'status' => 'Draft',
                'offer_number' => $validatedData['offer_number'],
                'offer_date' => Carbon::parse($validatedData['offer_date']),
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal_de_baza, // Salvăm MEREU subtotalul de bază
            ]);

            foreach ($items as $itemData) {
                 $lineTotalValue = ($itemData['material_price'] ?? 0) + ($itemData['labor_price'] ?? 0) + ($itemData['equipment_price'] ?? 0);
                 $itemData['total'] = ($itemData['quantity'] ?? 0) * $lineTotalValue;
                 $offer->items()->create($itemData);
            }

            // Incrementăm numărul doar dacă numerotarea este automată
            if ($company->offerSetting?->numbering_mode === 'auto') {
                $company->offerSetting->increment('next_number');
            }
            DB::commit();
            return redirect()->route('oferte.index')->with('success', 'Oferta a fost creată cu succes!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare la salvarea ofertei: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * NOU: Actualizează rapid statusul unei oferte din lista principală.
     */
    public function updateStatus(Request $request, Offer $offer)
    {
        // Securitate: Verificăm dacă oferta aparține companiei utilizatorului
        if ($offer->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Neautorizat'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Offer::STATUSES))],
        ]);

        $offer->update(['status' => $validated['status']]);

        // Returnăm un răspuns JSON pentru a confirma succesul
        return response()->json([
            'success' => 'Statusul a fost actualizat!',
            'new_status_class' => $offer->getStatusColorClass()
        ]);
    }

    /**
     * Afișează formularul de editare pentru o ofertă existentă.
     */
    public function edit(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $users = $company->users()->orderBy('name')->get();
        $statuses = Offer::STATUSES;
        $settings = $company->offerSetting;
        
        return view('offers.edit', compact('offer', 'clients', 'users', 'statuses', 'settings'));
    }

    /**
     * Actualizează o ofertă existentă în baza de date.
     */
    public function update(Request $request, Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(array_keys(Offer::STATUSES))],
            'offer_number' => ['required', 'string', Rule::unique('offers')->ignore($offer->id)],
            'offer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_measure' => 'required|string|max:20',
            'items.*.material_price' => 'nullable|numeric|min:0',
            'items.*.labor_price' => 'nullable|numeric|min:0',
            'items.*.equipment_price' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $items = $request->input('items', []);
            $subtotal_de_baza = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['material_price'] ?? 0) + ($item['labor_price'] ?? 0) + ($item['equipment_price'] ?? 0);
                $subtotal_de_baza += ($item['quantity'] ?? 0) * $lineTotal;
            }

            $offer->update([
                'client_id' => $validatedData['client_id'],
                'assigned_to_user_id' => $validatedData['assigned_to_user_id'],
                'status' => $validatedData['status'],
                'offer_number' => $validatedData['offer_number'],
                'offer_date' => Carbon::parse($validatedData['offer_date']),
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal_de_baza, // Salvăm MEREU subtotalul de bază
            ]);

            $offer->items()->delete();
            foreach ($items as $itemData) {
                 $lineTotalValue = ($itemData['material_price'] ?? 0) + ($itemData['labor_price'] ?? 0) + ($itemData['equipment_price'] ?? 0);
                 $itemData['total'] = ($itemData['quantity'] ?? 0) * $lineTotalValue;
                 $offer->items()->create($itemData);
            }

            DB::commit();
            return redirect()->route('oferte.index')->with('success', 'Oferta a fost actualizată!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * NOU: Alocă o ofertă unui utilizator.
     */
    public function assignUser(Request $request, Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Neautorizat'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        $offer->update(['assigned_to_user_id' => $validated['user_id']]);

        // Încărcăm numele noului utilizator pentru a-l trimite înapoi
        $assignedUserName = $offer->assignedTo ? $offer->assignedTo->name : 'N/A';

        return response()->json([
            'success' => 'Oferta a fost alocată!',
            'assigned_user_name' => $assignedUserName
        ]);
    }
    /**
     * Șterge o ofertă din baza de date.
     */
    public function destroy(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }
        
        // Ștergerea în cascadă este deja definită în migrație, dar o facem explicit pentru siguranță
        $offer->items()->delete();
        $offer->delete();
        
        return redirect()->route('oferte.index')->with('success', 'Oferta a fost ștearsă cu succes.');
    }

        /**
     * Afișează detaliile unei oferte (pagina de vizualizare).
     */
    public function show(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(43); }

        $offer->load(['client', 'items', 'assignedTo', 'company.companyProfile', 'company.templateSetting', 'company.offerSetting']);
        
        // NOU: Folosim serviciul pentru a pre-calcula toate datele
        $calculations = new OfferCalculationService($offer);

        return view('offers.show', [
            'offer' => $offer,
            'calculations' => $calculations, // Trimitem obiectul cu toate calculele gata făcute
            'companyProfile' => $offer->company->companyProfile,
            'templateSettings' => $offer->company->templateSetting,
            'offerSettings' => $offer->company->offerSetting
        ]);
    }

    /**
     * Generează și descarcă PDF-ul pentru o ofertă.
     */
    public function downloadPDF(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }
        
        $offer->load(['client', 'items', 'assignedTo', 'company.companyProfile', 'company.templateSetting', 'company.offerSetting']);

        // NOU: Folosim ACELAȘI serviciu și aici
        $calculations = new OfferCalculationService($offer);
        
        $pdfFileName = 'Oferta-' . str_replace('/', '-', $offer->offer_number) . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('offers.pdf', [
            'offer' => $offer,
            'calculations' => $calculations, // Trimitem exact același pachet de date
            'companyProfile' => $offer->company->companyProfile,
            'templateSettings' => $offer->company->templateSetting,
            'offerSettings' => $offer->company->offerSetting
        ]);
        
        return $pdf->download($pdfFileName);
    }
}