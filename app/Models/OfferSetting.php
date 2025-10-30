<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'prefix',
        'suffix',
        'start_number',
        'next_number',
        'numbering_mode',
        'show_unit_price_column',
        'vat_percentage',
        'show_material_column',
        'show_labor_column',
        'show_equipment_column',
        'show_unit_price_column',
        'show_summary_block',
        'summary_cam_percentage',
        'summary_indirect_percentage',
        'summary_profit_percentage',
        'include_summary_in_prices',
        'pdf_price_display_mode'
    ];
}
