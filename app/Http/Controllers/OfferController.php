<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Auth::user()->offers()->with('client')->latest()->paginate(15);
        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $clients = Auth::user()->clients()->orderBy('name')->get();
        $settings = Auth::user()->offerSetting;

        if (!$settings) {
            return redirect()->route('offer-settings.show')
                ->with('error', 'Vă rugăm să configurați mai întâi setările de ofertare.');
        }

        $nextNumber = $settings->next_number;
        $offerNumber = ($settings->prefix ?? '') . $nextNumber . ($settings->suffix ?? '');
        
        return view('offers.create', compact('clients', 'offerNumber'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'offer_number' => 'required|string|unique:offers,offer_number',
            'offer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_measure' => 'required|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $grandTotal = 0;

            // Calculăm totalul general
            foreach ($validatedData['items'] as $item) {
                $grandTotal += $item['quantity'] * $item['unit_price'];
            }

            // Creăm oferta
            $offer = $user->offers()->create([
                'client_id' => $validatedData['client_id'],
                'offer_number' => $validatedData['offer_number'],
                'offer_date' => Carbon::parse($validatedData['offer_date']),
                'notes' => $validatedData['notes'],
                'total_value' => $grandTotal,
            ]);

            // Adăugăm elementele
            foreach ($validatedData['items'] as $itemData) {
                $offer->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_measure' => $itemData['unit_measure'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            // Actualizăm numărul următor în setări
            $settings = $user->offerSetting;
            if ($settings) {
                $settings->increment('next_number');
            }

            DB::commit();

            return redirect()->route('oferte.index')->with('success', 'Oferta a fost creată cu succes!');

                } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'A apărut o eroare la salvarea ofertei: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Afișează detaliile unei oferte specifice.
     */
    public function show(Offer $oferte)
    {
        // Asigură-te că oferta aparține utilizatorului autentificat
        if ($oferte->user_id !== Auth::id()) {
            abort(403);
        }

        // Încarcă relațiile necesare pentru a le folosi în view
        $oferte->load(['client', 'items']);
        
        $companyProfile = Auth::user()->companyProfile;
        $templateSettings = Auth::user()->templateSetting;

        return view('offers.show', [
            'offer' => $oferte,
            'companyProfile' => $companyProfile,
            'templateSettings' => $templateSettings,
        ]);
    }

    public function downloadPDF(Offer $oferte)
    {
        // Securitate: verifică dacă oferta aparține utilizatorului
        if ($oferte->user_id !== Auth::id()) {
            abort(403);
        }

        $oferte->load(['client', 'items']);
        $companyProfile = Auth::user()->companyProfile;
        $templateSettings = Auth::user()->templateSetting;

        // Creează numele fișierului PDF
        $pdfFileName = 'Oferta-' . str_replace('/', '-', $oferte->offer_number) . '.pdf';
        
        // Generează PDF-ul folosind un view special pentru PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('offers.pdf', compact('oferte', 'companyProfile', 'templateSettings'));
        
        // Descarcă fișierul în browser
        return $pdf->download($pdfFileName);
    }
}