<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prefix',
        'suffix',
        'start_number',
        'next_number',
    ];
}
