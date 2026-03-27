<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'contact',
        'email',
        'address',
        'image',

    ];

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }

    public function commissionPayments()
    {
        return $this->hasMany(SupplierCommissionPayment::class);
    }
}
