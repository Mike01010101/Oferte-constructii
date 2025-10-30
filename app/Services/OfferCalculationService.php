<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferSetting;

class OfferCalculationService
{
    private Offer $offer;
    private OfferSetting $settings;

    // Proprietăți publice pentru a fi accesate direct în Blade
    public float $baseSubtotal = 0;
    public float $recapMultiplier = 1.0;
    public float $camValue = 0;
    public float $indirectValue = 0;
    public float $profitValue = 0;
    public float $totalWithoutVat = 0;
    public float $vatValue = 0;
    public float $grandTotal = 0;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
        // Ne asigurăm că avem mereu setări valide, chiar dacă nu există în DB
        $this->settings = $offer->company->offerSetting ?? new OfferSetting();
        $this->calculate();
    }

    private function calculate(): void
    {
        $this->baseSubtotal = $this->offer->total_value;

        // Calculăm totalurile pe resurse, necesare pentru CAM și alte calcule
        $totalBaseLabor = 0;
        foreach ($this->offer->items as $item) {
            $totalBaseLabor += $item->quantity * $item->labor_price;
        }

        // --- Calculăm valorile de recapitulatie ---
        $this->camValue = $totalBaseLabor * ($this->settings->summary_cam_percentage / 100);
        $totalPlusCam = $this->baseSubtotal + $this->camValue;
        $this->indirectValue = $totalPlusCam * ($this->settings->summary_indirect_percentage / 100);
        $totalPlusIndirect = $totalPlusCam + $this->indirectValue;
        $this->profitValue = $totalPlusIndirect * ($this->settings->summary_profit_percentage / 100);

        // --- Stabilim totalurile finale ---
        if ($this->settings->include_summary_in_prices) {
            $totalRecap = $this->camValue + $this->indirectValue + $this->profitValue;
            if ($this->baseSubtotal > 0) {
                $this->recapMultiplier = 1 + ($totalRecap / $this->baseSubtotal);
            }
            $this->totalWithoutVat = $this->baseSubtotal * $this->recapMultiplier;
        } else {
            $this->totalWithoutVat = $this->baseSubtotal + $this->camValue + $this->indirectValue + $this->profitValue;
        }

        $this->vatValue = $this->totalWithoutVat * ($this->settings->vat_percentage / 100);
        $this->grandTotal = $this->totalWithoutVat + $this->vatValue;
    }
}