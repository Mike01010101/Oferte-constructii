<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferSetting;
use App\Models\PaymentStatement;

class CalculationService
{
    private Offer|PaymentStatement $model;
    private OfferSetting $settings;

    // Proprietăți publice
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

    public function __construct(Offer|PaymentStatement $model)
    {
        $this->model = $model;
        $this->settings = $model->company->offerSetting ?? new OfferSetting();
        $this->calculate();
    }

    private function calculate(): void
    {
        // Pasul 1: Resetăm totul și inițializăm
        $this->recapMultiplier = 1.0;
        $this->camValue = 0;
        $this->indirectValue = 0;
        $this->profitValue = 0;
        
        // Pasul 2: Calculăm valorile de recapitulatie DOAR dacă este necesar
        if ($this->settings->include_summary_in_prices || $this->settings->show_summary_block) {
            $fullBaseSubtotal = 0;
            $totalBaseLabor = 0;
            
            foreach ($this->model->items as $item) {
                $fullBaseSubtotal += $item->quantity * ($item->material_price + $item->labor_price + $item->equipment_price);
                $totalBaseLabor += $item->quantity * $item->labor_price;
            }

            $this->camValue = $totalBaseLabor * ($this->settings->summary_cam_percentage / 100);
            $totalPlusCam = $fullBaseSubtotal + $this->camValue;
            $this->indirectValue = $totalPlusCam * ($this->settings->summary_indirect_percentage / 100);
            $totalPlusIndirect = $totalPlusCam + $this->indirectValue;
            $this->profitValue = $totalPlusIndirect * ($this->settings->summary_profit_percentage / 100);
            
            // Setăm multiplicatorul DOAR dacă opțiunea de includere este bifată
            if ($this->settings->include_summary_in_prices) {
                $totalRecap = $this->camValue + $this->indirectValue + $this->profitValue;
                if ($fullBaseSubtotal > 0) {
                    $this->recapMultiplier = 1 + ($totalRecap / $fullBaseSubtotal);
                }
            }
        }

        // Pasul 3: Calculăm totalurile afișate, aplicând multiplicatorul (care va fi 1.0 dacă opțiunea e debifată)
        $this->baseSubtotal = 0;
        $this->totalMaterial = 0;
        $this->totalLabor = 0;
        $this->totalEquipment = 0;

        foreach ($this->model->items as $item) {
            $lineMaterialTotal = $item->quantity * $item->material_price * $this->recapMultiplier;
            $lineLaborTotal = $item->quantity * $item->labor_price * $this->recapMultiplier;
            $lineEquipmentTotal = $item->quantity * $item->equipment_price * $this->recapMultiplier;

            $this->totalMaterial += $lineMaterialTotal;
            $this->totalLabor += $lineLaborTotal;
            $this->totalEquipment += $lineEquipmentTotal;

            if ($this->settings->show_material_column) { $this->baseSubtotal += $lineMaterialTotal; }
            if ($this->settings->show_labor_column) { $this->baseSubtotal += $lineLaborTotal; }
            if ($this->settings->show_equipment_column) { $this->baseSubtotal += $lineEquipmentTotal; }
        }

        // Pasul 4: Calculăm totalurile finale ale documentului
        if ($this->settings->include_summary_in_prices) {
            $this->totalWithoutVat = $this->baseSubtotal;
        } 
        // Adunăm recapitulatiile doar dacă opțiunea de afișare a blocului este bifată
        elseif ($this->settings->show_summary_block) {
            $this->totalWithoutVat = $this->baseSubtotal + $this->camValue + $this->indirectValue + $this->profitValue;
        } 
        // Cazul tău: ambele opțiuni sunt debifate
        else {
            $this->totalWithoutVat = $this->baseSubtotal;
        }

        $this->vatValue = $this->totalWithoutVat * ($this->settings->vat_percentage / 100);
        $this->grandTotal = $this->totalWithoutVat + $this->vatValue;
    }
}