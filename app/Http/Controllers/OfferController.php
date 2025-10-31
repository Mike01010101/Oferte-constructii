<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Services\CalculationService;
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
            // Încărcăm relațiile standard
            ->with(['client', 'assignedTo'])
            ->with(['client', 'assignedTo', 'paymentStatement'])
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('offer_number', 'like', "%{$searchTerm}%")
                      ->orWhere('status', 'like', "%{$searchTerm}%")
                      ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                          $clientQuery->where('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('items', function ($itemsQuery) use ($searchTerm) {
                          $itemsQuery->where('description', 'like', "%{$searchTerm}%");
                      });
                })
                // NOU ȘI CRUCIAL: Încărcăm relația `matching_items` DOAR cu articolele care se potrivesc
                ->with(['matching_items' => function ($query) use ($searchTerm) {
                    $query->where('description', 'like', "%{$searchTerm}%");
                }]);
            })
            ->latest();

        $offers = $offersQuery->paginate(15)->appends($request->query());

        // Calculăm valoarea vizibilă doar dacă NU există o căutare activă
        if (empty($searchTerm)) {
            $settings = Auth::user()->company->offerSetting;
            // Ne asigurăm că relația 'items' este încărcată, pentru a nu face N+1 queries
            $offers->load('items');

            foreach ($offers as $offer) {
                $visibleTotal = 0;
                foreach ($offer->items as $item) {
                    $lineSubtotal = 0;
                    if ($settings && $settings->show_material_column) {
                        $lineSubtotal += $item->material_price;
                    }
                    if ($settings && $settings->show_labor_column) {
                        $lineSubtotal += $item->labor_price;
                    }
                    if ($settings && $settings->show_equipment_column) {
                        $lineSubtotal += $item->equipment_price;
                    }
                    $visibleTotal += $item->quantity * $lineSubtotal;
                }
                // Adăugăm noua proprietate la obiectul ofertă
                $offer->visible_total_value = $visibleTotal;
            }
        }

        $users = Auth::user()->company->users()->orderBy('name')->get();

        if ($request->ajax()) {
            // Trimitem și searchTerm la view-ul partial pentru AJAX
            return view('offers.partials.offers-table', compact('offers', 'users', 'searchTerm'))->render();
        }

        return view('offers.index', compact('offers', 'users', 'searchTerm'));
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
            'object' => 'nullable|string|max:255',
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
                'object' => $validatedData['object'],
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal_de_baza,
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
            'object' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_measure' => 'required|string|max:20',
            // Validăm prețurile doar dacă sunt trimise (vor fi trimise și cu valoarea 0)
            'items.*.material_price' => 'nullable|numeric|min:0',
            'items.*.labor_price' => 'nullable|numeric|min:0',
            'items.*.equipment_price' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Obținem setările curente ale companiei
            $settings = Auth::user()->company->offerSetting;
            $items = $request->input('items', []);

            // Încărcăm articolele vechi într-un format ușor de accesat (keyed by description or a unique key if available)
            // Pentru simplitate, vom folosi un array simplu și îl vom parcurge
            $oldItems = $offer->items;

            $subtotal_de_baza = 0;
            $newItemsData = [];

            foreach ($items as $index => $item) {
                // Încercăm să găsim un articol vechi corespunzător. Această logică poate fi îmbunătățită
                // dacă ai un ID ascuns pentru fiecare articol în formular.
                $oldItem = $oldItems->get($index); // Presupunem că ordinea se păstrează

                $newItemData = [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_measure' => $item['unit_measure'],
                ];

                // Păstrăm valorile vechi pentru câmpurile care nu sunt afișate/trimise
                $newItemData['material_price'] = $settings->show_material_column 
                    ? ($item['material_price'] ?? 0) 
                    : ($oldItem->material_price ?? 0);

                $newItemData['labor_price'] = $settings->show_labor_column 
                    ? ($item['labor_price'] ?? 0) 
                    : ($oldItem->labor_price ?? 0);

                $newItemData['equipment_price'] = $settings->show_equipment_column 
                    ? ($item['equipment_price'] ?? 0) 
                    : ($oldItem->equipment_price ?? 0);
                
                // Calculăm totalul liniei pe baza tuturor valorilor (vechi + noi)
                $lineTotalValue = $newItemData['material_price'] + $newItemData['labor_price'] + $newItemData['equipment_price'];
                $newItemData['total'] = $newItemData['quantity'] * $lineTotalValue;
                
                $subtotal_de_baza += $newItemData['total'];
                $newItemsData[] = $newItemData;
            }

            $offer->update([
                'client_id' => $validatedData['client_id'],
                'assigned_to_user_id' => $validatedData['assigned_to_user_id'],
                'status' => $validatedData['status'],
                'offer_number' => $validatedData['offer_number'],
                'offer_date' => Carbon::parse($validatedData['offer_date']),
                'object' => $validatedData['object'],
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal_de_baza,
            ]);

            $offer->items()->delete();
            foreach ($newItemsData as $itemData) {
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
        $calculations = new CalculationService($offer);

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
        $calculations = new CalculationService($offer);
        
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
    /**
     * Verifică dacă o ofertă are deja o situație de plată și returnează un răspuns JSON.
     */
    public function checkPaymentStatement(Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Neautorizat'], 403);
        }

        $hasStatement = $offer->paymentStatement()->exists();

        return response()->json(['has_statement' => $hasStatement]);
    }
}