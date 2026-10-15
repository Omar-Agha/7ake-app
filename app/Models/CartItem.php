<?php

namespace App\Models;

use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here, for example:
        'cart_id',
        'product_variant_id',
        'quantity',
        
        
    ];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart():BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
