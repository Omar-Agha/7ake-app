<?php

namespace App\Models\Inventory;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $inventory_item_id
 * @property int $quantity
 * @property \Carbon\CarbonImmutable $reserved_at
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property ReservationStatus $status
 * @property int|null $order_item_id
 * @property-read \App\Models\Inventory\InventoryItem $inventoryItem
 * @property-read \App\Models\Order\OrderItem|null $orderItem
 * @method static \Database\Factories\Inventory\ReservationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereReservedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Reservation extends Model
{
    use HasFactory;
    // php artisan make:factory ReservationFactory --model=App\\Models\\Inventory\\Reservation
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_item_id',
        'quantity',
        'reserved_at',
        'expires_at',
        'status',
        'order_item_id'
    ];

    /**
     * Get the order item that owns the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem()
    {
        return $this->belongsTo(\App\Models\Order\OrderItem::class, 'order_item_id');
    }
   
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
        'status'=> ReservationStatus::class
    ];

    
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

  
}
