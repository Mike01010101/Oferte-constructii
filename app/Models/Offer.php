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
        'object',
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
    public function matching_items()
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
    public function getStatusColorClass(): string
    {
        return match ($this->status) {
            'Acceptata', 'Incasata', 'Facturata', 'Finalizata' => 'text-bg-success',
            'Trimisa', 'In lucru' => 'text-bg-info',
            'Draft' => 'text-bg-secondary',
            'Anulata', 'Respinsa' => 'text-bg-danger',
            default => 'text-bg-light',
        };
    }
    /**
     * NOU: Returnează doar numele culorii Bootstrap bazată pe status.
     */
    public static function getColorNameForStatus(string $status): string
    {
        return match ($status) {
            'Acceptata', 'Incasata', 'Facturata', 'Finalizata' => 'success',
            'Trimisa', 'In lucru' => 'info',
            'Draft' => 'secondary',
            'Anulata', 'Respinsa' => 'danger',
            default => 'light',
        };
    }
}
