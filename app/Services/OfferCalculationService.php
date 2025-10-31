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

    public float $totalMaterial = 0;
    public float $totalLabor = 0;
    public float $totalEquipment = 0;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
        // Ne asigurăm că avem mereu setări valide, chiar dacă nu există în DB
        $this->settings = $offer->company->offerSetting ?? new OfferSetting();
        $this->calculate();
    }

    private function calculate(): void
    {
        // --- Pasul 1: Calculăm multiplicatorul de recapitulatie (dacă există) ---
        $fullBaseSubtotal = $this->offer->total_value;
        $totalBaseLabor = 0;
        foreach ($this->offer->items as $item) {
            $totalBaseLabor += $item->quantity * $item->labor_price;
        }

        $this->camValue = $totalBaseLabor * ($this->settings->summary_cam_percentage / 100);
        $totalPlusCam = $fullBaseSubtotal + $this->camValue;
        $this->indirectValue = $totalPlusCam * ($this->settings->summary_indirect_percentage / 100);
        $totalPlusIndirect = $totalPlusCam + $this->indirectValue;
        $this->profitValue = $totalPlusIndirect * ($this->settings->summary_profit_percentage / 100);

        if ($this->settings->include_summary_in_prices) {
            $totalRecap = $this->camValue + $this->indirectValue + $this->profitValue;
            if ($fullBaseSubtotal > 0) {
                $this->recapMultiplier = 1 + ($totalRecap / $fullBaseSubtotal);
            }
        }

        // --- Pasul 2: Resetăm și calculăm totalurile finale, aplicând multiplicatorul ---
        $this->baseSubtotal = 0;
        $this->totalMaterial = 0;
        $this->totalLabor = 0;
        $this->totalEquipment = 0;

        foreach ($this->offer->items as $item) {
            // Aplicăm multiplicatorul pe fiecare resursă
            $adjMaterialPrice = $item->material_price * $this->recapMultiplier;
            $adjLaborPrice = $item->labor_price * $this->recapMultiplier;
            $adjEquipmentPrice = $item->equipment_price * $this->recapMultiplier;

            // Calculăm totalurile pe linie pentru fiecare resursă, cu prețurile ajustate
            $lineMaterialTotal = $item->quantity * $adjMaterialPrice;
            $lineLaborTotal = $item->quantity * $adjLaborPrice;
            $lineEquipmentTotal = $item->quantity * $adjEquipmentPrice;

            // Adunăm la totalurile generale pe resurse (acestea vor include acum recapitulatiile)
            $this->totalMaterial += $lineMaterialTotal;
            $this->totalLabor += $lineLaborTotal;
            $this->totalEquipment += $lineEquipmentTotal;

            // Adunăm la subtotalul vizibil doar resursele active
            if ($this->settings->show_material_column) {
                $this->baseSubtotal += $lineMaterialTotal;
            }
            if ($this->settings->show_labor_column) {
                $this->baseSubtotal += $lineLaborTotal;
            }
            if ($this->settings->show_equipment_column) {
                $this->baseSubtotal += $lineEquipmentTotal;
            }
        }

        // --- Pasul 3: Stabilim totalurile finale ale ofertei ---
        if ($this->settings->include_summary_in_prices) {
            // Dacă recapitulatiile sunt incluse, subtotalul vizibil este direct totalul fără tva
            $this->totalWithoutVat = $this->baseSubtotal;
        } else {
            // Altfel, adunăm recapitulatiile la subtotalul vizibil
            $this->totalWithoutVat = $this->baseSubtotal + $this->camValue + $this->indirectValue + $this->profitValue;
        }

        $this->vatValue = $this->totalWithoutVat * ($this->settings->vat_percentage / 100);
        $this->grandTotal = $this->totalWithoutVat + $this->vatValue;
    }
}