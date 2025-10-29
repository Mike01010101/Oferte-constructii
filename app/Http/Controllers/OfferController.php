<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\User;
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
    public function index()
    {
        // Preluăm ofertele inclusiv relațiile 'client' și 'assignedTo' pentru a evita query-uri multiple
        $offers = Auth::user()->company->offers()->with(['client', 'assignedTo'])->latest()->paginate(15);
        return view('offers.index', compact('offers'));
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

        $offerNumber = ($settings->prefix ?? '') . $settings->next_number . ($settings->suffix ?? '');
        
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

            $company->offerSetting?->increment('next_number');
            DB::commit();
            return redirect()->route('oferte.index')->with('success', 'Oferta a fost creată cu succes!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare la salvarea ofertei: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Afișează detaliile unei oferte (pagina de vizualizare).
     */
    public function show(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        $offer->load(['client', 'items', 'assignedTo']);
        
        $company = Auth::user()->company;
        $companyProfile = $company->companyProfile;
        $templateSettings = $company->templateSetting;
        $offerSettings = $company->offerSetting;

        return view('offers.show', compact('offer', 'companyProfile', 'templateSettings', 'offerSettings'));
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
     * Generează și descarcă PDF-ul pentru o ofertă.
     */
    public function downloadPDF(Offer $oferte)
    {
        $offer = $oferte;
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        $offer->load(['client', 'items', 'assignedTo']);
        
        $company = Auth::user()->company;
        $companyProfile = $company->companyProfile;
        $templateSettings = $company->templateSetting;
        $offerSettings = $company->offerSetting;

        $pdfFileName = 'Oferta-' . str_replace('/', '-', $offer->offer_number) . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('offers.pdf', compact('offer', 'companyProfile', 'templateSettings', 'offerSettings'));
        
        return $pdf->download($pdfFileName);
    }
}