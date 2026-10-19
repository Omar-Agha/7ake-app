<?php

namespace App\Models\Inventory;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
