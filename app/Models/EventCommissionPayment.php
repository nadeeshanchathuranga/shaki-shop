<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCommissionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_commission_id',
        'amount',
        'payment_date',
        'notes',
    ];

    public function eventCommission()
    {
        return $this->belongsTo(EventCommission::class);
    }
}
