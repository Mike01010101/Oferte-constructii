<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;

class Offer extends Model
{
    use HasFactory;

    const STATUSES = [
        'Draft' => 'Draft',
        'Finala' => 'Finala',
        'Trimisa' => 'Trimisa',
        'Acceptata' => 'Acceptata',
        'In lucru' => 'In lucru',
        'Finalizata' => 'Finalizata',
        'Facturata' => 'Facturata',
        'Incasata' => 'Incasata',
    ];

    protected $fillable = [
        'company_id',
        'client_id',
        'assigned_to_user_id',
        'offer_number',
        'offer_date',
        'status',
        'total_value',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OfferItem::class);
    }
        public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Definește relația inversă cu modelul Company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
