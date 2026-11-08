<?php

namespace App\Core\Inventory;

use App\Enums\StockMovementReason;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Reservation;
use App\Models\Inventory\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryDomain
{
    /**
     * Reserve a quantity of an inventory item.
     *
     * @param InventoryItem $inventoryItem
     * @param int $quantity
     * @param \DateTimeInterface $expiresAt
     * @return InventoryItem
     *
     * @throws \DomainException
     */
    public function reserveForOrderItem(int $product_variant_id, int $quantity, int $order_item_id): InventoryItem
    {
        $inventoryItem = InventoryItem::where('product_variant_id', $product_variant_id)->first();
        return $this->reserveInventoryItem($inventoryItem, $quantity, Carbon::now()->addHour(), $order_item_id);
    }
    /**
     * Reserve a quantity of an inventory item.
     *
     * @param InventoryItem $inventoryItem
     * @param int $quantity
     * @param \DateTimeInterface $expiresAt
     * @return InventoryItem
     *
     * @throws \DomainException
     */
    public function reserveInventoryItem(
        ?InventoryItem $inventoryItem,
        int $quantity,
        \DateTimeInterface $expiresAt,
        ?int $order_item_id = null

    ): InventoryItem {

        if ($quantity < 1) {
            throw new \DomainException('Reservation quantity must be at least 1.');
        }
        if (!$inventoryItem) {
            throw new \DomainException('Not enough inventory to reserve the requested quantity.');
        }
        $available = $inventoryItem->available_quantity;

        if ($quantity > $available) {

            throw new \DomainException('Not enough inventory to reserve the requested quantity.');
        }


        return DB::transaction(function () use ($inventoryItem, $quantity, $expiresAt, $order_item_id) {
            $affectedRows = InventoryItem::whereKey($inventoryItem->id)
                ->whereRaw(
                    'quantity - reserved_quantity >= ?',
                    [$quantity]
                )
                ->increment('reserved_quantity', $quantity);

            if ($affectedRows == 0) {
                throw new \DomainException('Not enough inventory to reserve the requested quantity.');
            }

            $inventoryItem = $inventoryItem->fresh();

            $inventoryItem->reservations()->create([
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => $quantity,
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
                'status' => \App\Enums\ReservationStatus::Active,
                'order_item_id' => $order_item_id,
            ]);
            $inventoryItem->save();
            return $inventoryItem;
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

        return DB::transaction(function () use ($reservation) {

            $reservation->status = \App\Enums\ReservationStatus::Expired;
            $reservation->save();
            $this->releaseReservation($reservation);
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
    public function releaseReservation(Reservation $reservation): bool
    {
        // Find an active reservation for the inventory item with the requested quantity
        $item = $reservation->inventoryItem;


        $item->decrement('reserved_quantity', $reservation->quantity);
        $item->save();
        $reservation->delete();

        return true;
    }


    public function cancelReservation(Reservation $reservation): bool
    {
        return $this->releaseReservation($reservation);
    }

    public function commitReservation(Reservation $reservation): bool
    {
        $quantity = $reservation->quantity;
        $this->releaseReservation($reservation);
        $reservation->inventoryItem->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Create sale movement records for an array of inventory item IDs.
     *
     * @param int[] $inventoryItemIds
     * @return void
     */
    public function createSaleMovement(array $items): void
    {
        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                'inventory_item_id' => $item['product_v_id'],
                'quantity' => $item['quantity'],
                'reason' => StockMovementReason::Sale,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($rows)) {
            StockMovement::insert($rows);
        }
    }

    public function initializeInventoryForProductVariant(int $product_variant_id): InventoryItem
    {
        return InventoryItem::firstOrCreate(
            ['product_variant_id' => $product_variant_id],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );
    }

    public function moveStock(int $product_variant_id, int $quantity, StockMovementReason $reason): bool
    {
        return \DB::transaction(function () use ($product_variant_id, $quantity, $reason) {
            $inventoryItem = InventoryItem::firstOrCreate(
                ['product_variant_id' => $product_variant_id],
            );
            if ($inventoryItem->available_quantity + $quantity < 0) {
                throw new \DomainException('Inventory quantity cannot be negative.');
            }

            // Update the inventory item's quantity

            $affected =
                InventoryItem::whereKey($inventoryItem->id)
                ->whereRaw(
                    'quantity - reserved_quantity + ? >= 0',
                    [$quantity]
                )
                ->increment('quantity', $quantity);


            if ($affected == 0) {
                throw new \DomainException('Inventory quantity cannot be negative.');
            }

            $inventoryItem->refresh();

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
