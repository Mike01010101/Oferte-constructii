<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_name',
        'vat_number',
        'trade_register_number',
        'address',
        'contact_email',
        'phone_number',
        'iban',
        'bank_name',
        'logo_path',
    ];
}
