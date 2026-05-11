<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProductPurchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'product_id',
        'product_name',
        'quantity',
        'cost_price',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
