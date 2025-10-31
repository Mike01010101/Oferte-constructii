<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'offer_id',
        'client_id',
        'statement_number',
        'statement_date',
        'object',
        'total_value',
        'notes',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function offer() { return $this->belongsTo(Offer::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function items() { return $this->hasMany(PaymentStatementItem::class); }
}