<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $image
 */
class RentalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'barcode',
        'category_id',
        'color_id',
        'size',
        'supplier_id',
        'customer_name',
        'rental_quantity',
        'rent_price',
        'commission_type_shop',
        'commission_percentage_shop',
        'commission_amount_shop',
        'commission_type_supplier',
        'commission_percentage_supplier',
        'commission_amount_supplier',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
