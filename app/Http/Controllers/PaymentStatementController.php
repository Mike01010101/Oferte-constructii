<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\PaymentStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentStatementController extends Controller
{
    /**
     * NOU: Afișează lista de situații de plată PENTRU O ANUMITĂ OFERTĂ.
     */
    public function index(Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        // Încărcăm situațiile de plată asociate ofertei
        $statements = $offer->paymentStatements()->latest('statement_date')->paginate(15);

        return view('payment-statements.index', compact('offer', 'statements'));
    }

    /**
     * NOU: Afișează formularul de creare, pre-calculând cantitățile rămase.
     */
    public function create(Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        // Preluăm toate situațiile de plată anterioare pentru a calcula totalurile decontate
        $previousStatements = $offer->paymentStatements()->with('items')->get();

        // Construim un array cu cantitățile deja decontate pentru fiecare descriere de articol
        $decontatedQuantities = [];
        foreach ($previousStatements as $statement) {
            foreach ($statement->items as $item) {
                // Folosim descrierea ca o cheie unică
                $key = $item->description;
                if (!isset($decontatedQuantities[$key])) {
                    $decontatedQuantities[$key] = 0;
                }
                $decontatedQuantities[$key] += $item->quantity;
            }
        }

        // Creăm o colecție de itemi pentru noua situație, calculând cantitățile rămase
        $newItems = $offer->items->map(function ($offerItem) use ($decontatedQuantities) {
            $key = $offerItem->description;
            $decontatedQty = $decontatedQuantities[$key] ?? 0;
            
            // Clonăm itemul din ofertă pentru a nu-l modifica direct
            $newItem = $offerItem->replicate(); 
            
            // Calculăm cantitatea rămasă și ne asigurăm că nu este negativă
            $newItem->quantity = max(0, $offerItem->quantity - $decontatedQty); 
            
            return $newItem;
        })->filter(function ($item) {
            // Eliminăm itemii care au fost deja complet decontați
            return $item->quantity > 0;
        });

        // Pre-populăm datele situației de plată cu noul format
        $nextStatementNumber = 'SP' . ($previousStatements->count() + 1) . ' - ' . $offer->offer_number;
        $statement = new PaymentStatement([
            'client_id' => $offer->client_id,
            'object' => $offer->object,
            'statement_number' => $nextStatementNumber,
            'statement_date' => now(),
            'notes' => $offer->notes,
        ]);

        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $settings = $company->offerSetting;

        return view('payment-statements.create', [
            'offer' => $offer,
            'statement' => $statement,
            'items' => $newItems, // Trimitem itemii cu cantitățile calculate
            'clients' => $clients,
            'settings' => $settings,
        ]);
    }

    /**
     * NOU: Salvează noua situație de plată. Se așteaptă ID-ul ofertei.
     */
    public function store(Request $request, Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'statement_number' => 'required|string',
            'statement_date' => 'required|date',
            'object' => 'nullable|string|max:255',
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
            $company = Auth::user()->company;
            $items = $validatedData['items'];

            $subtotal = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['material_price'] ?? 0) + ($item['labor_price'] ?? 0) + ($item['equipment_price'] ?? 0);
                $subtotal += ($item['quantity'] ?? 0) * $lineTotal;
            }

            // Creăm situația de plată direct prin relația cu oferta
            $statement = $offer->paymentStatements()->create([
                'company_id' => $company->id,
                'client_id' => $validatedData['client_id'],
                'statement_number' => $validatedData['statement_number'],
                'statement_date' => Carbon::parse($validatedData['statement_date']),
                'object' => $validatedData['object'],
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal,
            ]);

            foreach ($items as $itemData) {
                 $lineTotalValue = ($itemData['material_price'] ?? 0) + ($itemData['labor_price'] ?? 0) + ($itemData['equipment_price'] ?? 0);
                 $itemData['total'] = ($itemData['quantity'] ?? 0) * $lineTotalValue;
                 $statement->items()->create($itemData);
            }

            DB::commit();
            // Redirecționăm către lista de situații de plată A ACESTEI OFERTE
            return redirect()->route('oferte.situatii-plata.index', $offer)->with('success', 'Situația de plată a fost creată cu succes!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Afișează formularul de editare pentru o situație de plată.
     */
    public function edit(Offer $offer, PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id || $statement->offer_id !== $offer->id) { abort(403); }

        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $settings = $company->offerSetting;
        
        return view('payment-statements.edit', [
            'statement' => $statement,
            'offer' => $offer,
            'items' => $statement->items,
            'clients' => $clients,
            'settings' => $settings,
        ]);
    }

    /**
     * Actualizează o situație de plată existentă.
     */
    public function update(Request $request, Offer $offer, PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id || $statement->offer_id !== $offer->id) { abort(403); }

        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'statement_number' => 'required|string',
            'statement_date' => 'required|date',
            'object' => 'nullable|string|max:255',
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
            $subtotal = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['material_price'] ?? 0) + ($item['labor_price'] ?? 0) + ($item['equipment_price'] ?? 0);
                $subtotal += ($item['quantity'] ?? 0) * $lineTotal;
            }

            $statement->update([
                'client_id' => $validatedData['client_id'],
                'statement_number' => $validatedData['statement_number'],
                'statement_date' => Carbon::parse($validatedData['statement_date']),
                'object' => $validatedData['object'],
                'notes' => $validatedData['notes'],
                'total_value' => $subtotal,
            ]);

            $statement->items()->delete();
            foreach ($items as $itemData) {
                 $lineTotalValue = ($itemData['material_price'] ?? 0) + ($itemData['labor_price'] ?? 0) + ($itemData['equipment_price'] ?? 0);
                 $itemData['total'] = ($itemData['quantity'] ?? 0) * $lineTotalValue;
                 $statement->items()->create($itemData);
            }

            DB::commit();
            return redirect()->route('oferte.situatii-plata.index', $offer)->with('success', 'Situația de plată a fost actualizată!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Șterge o situație de plată.
     */
    public function destroy(Offer $offer, PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id || $statement->offer_id !== $offer->id) { abort(403); }

        $statement->items()->delete();
        $statement->delete();

        return redirect()->route('oferte.situatii-plata.index', $offer)->with('success', 'Situația de plată a fost ștearsă.');
    }

    /**
     * Generează și descarcă PDF-ul pentru o situație de plată.
     */
    public function downloadPDF(Offer $offer, PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id || $statement->offer_id !== $offer->id) { abort(403); }
        
        $statement->load(['client', 'items', 'company.companyProfile', 'company.templateSetting', 'company.offerSetting']);
        
        // NOU: Calculăm indexul situației de plată
        $allStatements = $offer->paymentStatements()->orderBy('statement_date', 'asc')->get();
        $statementIndex = null;
        foreach ($allStatements as $index => $stmt) {
            if ($stmt->id == $statement->id) {
                $statementIndex = $index + 1; // Indexul este 0-based, deci adăugăm 1
                break;
            }
        }

        $calculations = new \App\Services\CalculationService($statement);
        
        $pdfFileName = 'Situatie-Plata-' . str_replace('/', '-', $statement->statement_number) . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payment-statements.pdf', [
            'statement' => $statement,
            'calculations' => $calculations,
            'offer' => $offer,
            'companyProfile' => $statement->company->companyProfile,
            'templateSettings' => $statement->company->templateSetting,
            'offerSettings' => $statement->company->offerSetting,
            'statementIndex' => $statementIndex, // NOU: Trimitem indexul către view
        ]);
        
        return $pdf->download($pdfFileName);
    }
}