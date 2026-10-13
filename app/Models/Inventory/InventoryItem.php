<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'product_variant_id', 'on_hand_quantity','reserved_quantity'//, 'location'
    ];
    /**
     * Get the available quantity for the inventory item.
     *
     * @return int
     */
    public function getAvailableQuantityAttribute(): int
    {
        $quantity = $this->quantity ?? 0;
        $reserved = $this->reserved_quantity ?? 0;
        return max(0, $quantity - $reserved);
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
