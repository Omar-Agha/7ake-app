<?php

namespace App\Core\Inventory;

use App\Enums\StockMovementReason;
use App\Models\Inventory\InventoryItem;
use Illuminate\Support\Facades\DB;

class InventoryDomain
{
    /**
     * Reserve a quantity of an inventory item.
     *
     * @param InventoryItem $inventoryItem
     * @param int $quantity
     * @param \DateTimeInterface $expiresAt
     * @return \App\Models\Inventory\Reservation
     *
     * @throws \InvalidArgumentException
     */
    public function reserveInventoryItem(
        InventoryItem $inventoryItem,
        int $quantity,
        \DateTimeInterface $expiresAt
    ): \App\Models\Inventory\Reservation {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Reservation quantity must be at least 1.');
        }

        $available = $inventoryItem->getAvailableQuantityAttribute();

        if ($quantity > $available) {
            throw new \InvalidArgumentException('Not enough inventory to reserve the requested quantity.');
        }

        return DB::transaction(function () use ($inventoryItem, $quantity, $expiresAt) {
            $reservation = new \App\Models\Inventory\Reservation([
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => $quantity,
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
                'status' => \App\Enums\ReservationStatus::Active,
            ]);
            $reservation->save();

            $inventoryItem->reserved_quantity = ($inventoryItem->reserved_quantity ?? 0) + $quantity;
            $inventoryItem->save();

            return $reservation;
        });
    }

    /**
     * Checks if the given reservation is expired, and marks it as expired if so.
     *
     * @param \App\Models\Inventory\Reservation $reservation
     * @return bool True if the reservation was just marked as expired, false otherwise.
     */
    public function expireReservationIfNeeded(\App\Models\Inventory\Reservation $reservation): bool
    {
        if (
            !($reservation->status === \App\Enums\ReservationStatus::Active &&
            $reservation->expires_at &&
            now()->greaterThanOrEqualTo($reservation->expires_at))
        ) {
            return false;
        }
        
        return DB::transaction(function()use($reservation){

            $reservation->status = \App\Enums\ReservationStatus::Expired;
                $reservation->save();
    
                // Reduce reserved inventory
                $inventoryItem = $reservation->inventoryItem;
                if ($inventoryItem) {
                    $this->releaseReservation($inventoryItem->id,$reservation->quantity);
                }
    
                return true;
        });
    }

    /**
     * Releases (cancels) an active reservation for the given inventory item and quantity.
     *
     * @param int $inventoryItemId
     * @param int $quantity
     * @return bool True if a reservation was released, false otherwise.
     */
    public function releaseReservation(int $inventoryItemId, int $quantity): bool
    {
        // Find an active reservation for the inventory item with the requested quantity
        $item = InventoryItem::find($inventoryItemId);
        
            if ($item) {
                $item->reserved_quantity = max(0, $item->reserved_quantity  - $quantity);
                $item->save();
                return true;
            }
            return false;
    }


    public function moveStock(int $product_variant_id, int $quantity, StockMovementReason $reason): bool
    {
        return \DB::transaction(function () use ($product_variant_id, $quantity, $reason) {
            $inventoryItem = InventoryItem::firstOrCreate(
                ['product_variant_id' => $product_variant_id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $newQuantity = $inventoryItem->quantity + $quantity;
            if ($newQuantity < 0) {
                throw new \DomainException('Inventory quantity cannot be negative.');
            }

            // Update the inventory item's quantity
            $inventoryItem->quantity = $newQuantity;
            $inventoryItem->save();
    

            // Create a stock movement record
            \App\Models\Inventory\StockMovement::create([
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => $quantity,
                'reason' => $reason,
                // Add referenceable fields if context is available, else leave blank or null
            ]);

            return true;
        });

    }

    
}