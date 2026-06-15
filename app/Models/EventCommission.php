<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_title',
        'event_date',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'advance_amount',
        'payment_status',
        'notes',
    ];

    protected $appends = ['remaining_balance'];

    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->advance_amount;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
