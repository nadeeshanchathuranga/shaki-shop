<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_order_id',
        'customer_name',
        'rental_item_id',
        'quantity',
        'unit_price',
        'total_price',
        'rental_date_from',
        'rental_date_to',
        'advance_amount',
        'status',
    ];

    public function rentalItem()
    {
        return $this->belongsTo(RentalItem::class, 'rental_item_id', 'id');
    }
}
