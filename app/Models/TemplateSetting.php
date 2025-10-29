<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'layout',
        'font_family',
        'table_style',
        'accent_color',
        'document_title',
        'logo_alignment',
        'footer_text',
    ];
}
