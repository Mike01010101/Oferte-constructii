<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users() { return $this->hasMany(User::class); }
    public function companyProfile() { return $this->hasOne(CompanyProfile::class); }
    public function offerSetting() { return $this->hasOne(OfferSetting::class); }
    public function templateSetting() { return $this->hasOne(TemplateSetting::class); }
    public function clients() { return $this->hasMany(Client::class); }
    public function offers() { return $this->hasMany(Offer::class); }
    public function paymentStatements() { return $this->hasMany(PaymentStatement::class);}
}
