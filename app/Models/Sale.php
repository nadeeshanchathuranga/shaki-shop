<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'user_id',
        'order_id',
        'total_amount',
        'discount',
        'payment_method',
        'sale_date',
        'total_cost',
        'cash',
        'custom_discount',
        'rental_date_from',
        'rental_date_to',
        'advance_amount',
        'deposit',
        'rental_type',
        'booking_id',
        'is_rental_returned',
        'late_fee',
        'late_days',
        'damage_amount',
        'deposit_refund',
    ];

    public function booking()
    {
        return $this->belongsTo(\App\Models\RentalBooking::class, 'booking_id', 'id');
    }


    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id','id');
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class, 'order_id','id');
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
