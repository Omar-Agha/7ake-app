<?php

namespace App\Models\Inventory;

use App\Enums\StockMovementReason;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_item_id',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
    ];
    /**
     * Get the parent referenceable model (morph-to).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function referenceable()
    {
        return $this->morphTo('reference');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'reason' => StockMovementReason::class,
        'movement_type' => 'string',
    ];

    /**
     * Get the product variant this inventory movement belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productVariant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\ProductVariant::class, 'product_variant_id');
    }

    
}
