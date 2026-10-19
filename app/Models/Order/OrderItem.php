<?php

namespace App\Models\Order;

use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Define your fillable attributes for OrderItem here, for example:
        'order_id',
        'product_variant_id',
        'product_name',
        'quantity',
        'price',
        'total',
        'sku',
        'currency',
    ];

    /**
     * Get the order that owns this order item.
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the reservation associated with this order item.
     */
    public function reservation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Inventory\Reservation::class, 'order_item_id');
    }

    /**
     * Get the product variants associated with the order item.
     */
    public function productVariant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    
}
