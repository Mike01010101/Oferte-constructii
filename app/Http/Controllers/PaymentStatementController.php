<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\PaymentStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\PaymentStatementCalculationService;
use App\Services\CalculationService;

class PaymentStatementController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');

        $statementsQuery = Auth::user()->company->paymentStatements()
            ->with(['client', 'offer']) // Încărcăm relațiile
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('statement_number', 'like', "%{$searchTerm}%")
                      ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                          $clientQuery->where('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('offer', function ($offerQuery) use ($searchTerm) {
                          $offerQuery->where('offer_number', 'like', "%{$searchTerm}%");
                      });
                });
            })
            ->latest('statement_date');

        $statements = $statementsQuery->paginate(15)->appends($request->query());

        return view('payment-statements.index', compact('statements', 'searchTerm'));
    }
    /**
     * Afișează formularul de creare a unei noi situații de plată,
     * pre-populat cu datele din oferta originală.
     */
    public function create(Offer $offer)
    {
        if ($offer->company_id !== Auth::user()->company_id) { abort(403); }

        // Pre-populăm datele situației de plată cu cele din ofertă
        $statement = new PaymentStatement([
            'client_id' => $offer->client_id,
            'object' => $offer->object,
            'statement_number' => 'SP-' . $offer->offer_number,
            'statement_date' => now(),
            'notes' => $offer->notes,
        ]);

        // Copiem itemii
        $items = $offer->items;

        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $settings = $company->offerSetting;

        // Folosim o vedere similară cu cea de editare ofertă
        return view('payment-statements.create', [
            'offer' => $offer,
            'statement' => $statement,
            'items' => $items,
            'clients' => $clients,
            'settings' => $settings,
        ]);
    }

    /**
     * Salvează noua situație de plată în baza de date.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'offer_id' => 'required|exists:offers,id',
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

            $statement = $company->paymentStatements()->create([
                'offer_id' => $validatedData['offer_id'],
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
            // TODO: Redirecționăm către lista de situații de plată, pe care o vom crea mai târziu
            return redirect()->route('oferte.index')->with('success', 'Situația de plată a fost creată cu succes!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Afișează formularul de editare pentru o situație de plată existentă.
     */
    public function edit(PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id) { abort(403); }

        // Încărcăm relația cu oferta originală pentru a avea acces la numărul ei
        $statement->load('offer');

        $company = Auth::user()->company;
        $clients = $company->clients()->orderBy('name')->get();
        $settings = $company->offerSetting;
        
        // Trimitem datele către vederea de editare
        return view('payment-statements.edit', [
            'statement' => $statement,
            'offer' => $statement->offer, // Trimitem și oferta originală
            'items' => $statement->items,
            'clients' => $clients,
            'settings' => $settings,
        ]);
    }

    /**
     * Actualizează o situație de plată existentă.
     */
    public function update(Request $request, PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id) { abort(403); }

        $validatedData = $request->validate([
            // Nu mai validăm offer_id, deoarece nu se poate schimba
            'client_id' => 'required|exists:clients,id',
            'statement_number' => 'required|string',
            'statement_date' => 'required|date',
            'object' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            // ... (regulile de validare pentru itemi)
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
            return redirect()->route('situatii-plata.index')->with('success', 'Situația de plată a fost actualizată!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generează și descarcă PDF-ul pentru o situație de plată.
     */
    public function downloadPDF(PaymentStatement $statement)
    {
        if ($statement->company_id !== Auth::user()->company_id) { abort(403); }
        
        $statement->load(['client', 'items', 'offer', 'company.companyProfile', 'company.templateSetting', 'company.offerSetting']);

        // Folosim serviciul de calcul și trimitem variabila
        $calculations = new CalculationService($statement);
        
        $pdfFileName = 'Situatie-Plata-' . str_replace('/', '-', $statement->statement_number) . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payment-statements.pdf', [
            'statement' => $statement,
            'calculations' => $calculations,
            'offer' => $statement->offer,
            'companyProfile' => $statement->company->companyProfile,
            'templateSettings' => $statement->company->templateSetting,
            'offerSettings' => $statement->company->offerSetting
        ]);
        
        return $pdf->download($pdfFileName);
    }
}