<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $product_variant_id
 * @property int $quantity
 * @property int $reserved_quantity
 * @property-read int $available_quantity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @method static \Database\Factories\Inventory\InventoryItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereReservedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InventoryItem extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your attributes here, for example:
        'quantity','product_variant_id','reserved_quantity'//, 'location'
    ];
    /**
     * Get the available quantity (quantity - reserved_quantity).
     *
     * @return int
     */
    public function getAvailableQuantityAttribute(): int
    {
        return (int) $this->quantity - (int) $this->reserved_quantity;
    }

    /**
     * Get the reservations associated with this inventory item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(\App\Models\Inventory\Reservation::class, 'inventory_item_id');
    }
    
    
}
