<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentStatementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_statement_id',
        'description',
        'quantity',
        'unit_measure',
        'material_price',
        'labor_price',
        'equipment_price',
        'total',
    ];

    public function paymentStatement()
    {
        return $this->belongsTo(PaymentStatement::class);
    }
}