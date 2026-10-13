<?php

namespace App\Models\Inventory;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

   
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

    /**
     * Get the inventory item associated with the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

  
}
