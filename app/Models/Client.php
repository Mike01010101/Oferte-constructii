<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'vat_number',
        'trade_register_number',
        'address',
        'contact_person',
        'email',
        'phone',
    ];
}