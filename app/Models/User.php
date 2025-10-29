<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
    ];

    public function company()
{
    return $this->belongsTo(Company::class);
}
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Definește relația "one-to-one" cu profilul companiei.
     */
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    /**
     * Definește relația "one-to-one" cu setările de ofertare.
     */
    public function offerSetting()
    {
        return $this->hasOne(OfferSetting::class);
    }

    /**
     * Definește relația "one-to-one" cu setările de șablon.
     */
    public function templateSetting()
    {
        return $this->hasOne(TemplateSetting::class);
    }

    /**
     * Definește relația "one-to-many" cu clienții.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Definește relația "one-to-many" cu ofertele.
     */
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}