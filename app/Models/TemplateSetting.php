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
    'document_title',
    'font_family',
    'accent_color',
    'table_style',
    'footer_text',
    'stamp_path',
    'stamp_size',
    'intro_text',
];
}
