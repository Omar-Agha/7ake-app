<?php

namespace App\Models\Inventory;

use App\Enums\StockMovementReason;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $inventory_item_id
 * @property int $quantity
 * @property StockMovementReason $reason
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $movement_type
 * @property-read \App\Models\Catalog\ProductVariant|null $productVariant
 * @property-read Model|\Eloquent|null $referenceable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function inventoryItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryItem::class, 'inventory_item_id');
    }


    public function productVariant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\ProductVariant::class, 'product_variant_id');
    }
}
