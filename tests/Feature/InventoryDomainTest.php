<?php

use Illuminate\Support\Facades\Http;
use App\Core\Inventory\InventoryDomain;
use App\Enums\ReservationStatus;
use App\Enums\StockMovementReason;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Concurrency;

function createProductWithVariant(): \App\Models\Catalog\ProductVariant
{
    $product = \App\Models\Catalog\Product::create([
        'name' => 'Test Product',
        'description' => 'A variant-enabled product',
        'product_status' => \App\Enums\PublishStatus::Published,
        'category_id' => null, // Set category if needed
    ]);

    $variant = \App\Models\Catalog\ProductVariant::create([
        'product_id' => $product->id,
        'name' => 'Default Variant',
        'price' => 100.00,
        'sku' => 'TEST-001',
        'color' => 'Red',
        'product_size' => \App\Enums\ProductSize::Medium,
    ]);

    return $variant;
}

describe('ReverseInventoryTest', function () {
    test('reserves inventory item and creates active reservation', function () {
        $domain = new InventoryDomain();
        $expiresAt = Carbon::now()->addHour();
        $variant = createProductWithVariant();
        $inventoryItem =  InventoryItem::create([
            'quantity' => 10,
            'reserved_quantity' => 2,
            'product_variant_id' => $variant->id,
        ]);


        $domain->reserveInventoryItem($inventoryItem, 3, $expiresAt);
        $inventoryItem->refresh();
        expect($inventoryItem->reserved_quantity)->toBe(5);
        $reservation = $inventoryItem->reservations()->latest()->first();
        expect($reservation)->not()->toBeNull();
        expect($reservation->quantity)->toBe(3);
        expect($reservation->reserved_at->eq($reservation->created_at))->toBeTrue();
        expect($reservation->expires_at->format('Y-m-d H:i:s'))->toBe($expiresAt->format('Y-m-d H:i:s'));

        expect($reservation->status)->toBe(\App\Enums\ReservationStatus::Active);
    });


    test('reserveInventoryItem prevents lost update in concurrent scenario', function () {

        $domain = new InventoryDomain();
        $variant = createProductWithVariant();

        $inventoryItem = InventoryItem::create([
            'quantity' => 10,
            'reserved_quantity' => 3,
            'product_variant_id' => $variant->id,
        ]);

        $expiresAt = Carbon::now()->addMinutes(30);

        // Simulate two concurrent processes attempting to reserve inventory at same time
        // (re-fetches, does not share object reference)
        $itemProcess1 = InventoryItem::find($inventoryItem->id);
        $itemProcess2 = InventoryItem::find($inventoryItem->id);

        // Process 1 reserves 2 units
        DB::enableQueryLog();
        $domain->reserveInventoryItem($itemProcess1, 2, $expiresAt);

        // Process 2 (with stale reserved_quantity) reserves 4 units
        $domain->reserveInventoryItem($itemProcess2, 4, $expiresAt);

        $inventoryItem->refresh();

        // Reserved quantity should reflect *both* reservations without conflict (total 3+2+4 = 9 only if both succeeded)
        // But a lost update bug would result in either just 7 (last write wins) or not 9.
        // Let's check it is correct
        $totalReservedQuantity = $inventoryItem->reserved_quantity;

        // Fetch reservations as well for verification
        $reservations = $inventoryItem->reservations()->orderBy('created_at')->get();

        expect($totalReservedQuantity)->toBe(9);
        expect($reservations)->toHaveCount(2);
        expect($reservations[0]->quantity)->toBe(2);
        expect($reservations[1]->quantity)->toBe(4);

        // Also check that available quantity is correct
        expect($inventoryItem->available_quantity)->toBe(1);
    });

    test('reserveInventoryItem prevents overselling in concurrent scenario', function () {

        $domain = new InventoryDomain();
        $variant = createProductWithVariant();

        $inventoryItem = InventoryItem::create([
            'quantity' => 10,
            'reserved_quantity' => 0,
            'product_variant_id' => $variant->id,
        ]);

        $expiresAt = Carbon::now()->addMinutes(30);

        // Simulate two concurrent processes attempting to reserve inventory at same time
        // (re-fetches, does not share object reference)
        $itemProcess1 = InventoryItem::find($inventoryItem->id);
        $itemProcess2 = InventoryItem::find($inventoryItem->id);


        // Process 1 reserves 10 units (should succeed)
        $domain->reserveInventoryItem($itemProcess1, 10, $expiresAt);

        // Process 2 (with stale reserved_quantity) tries to reserve 10 units (should fail and throw exception)
        expect(fn() => $domain->reserveInventoryItem($itemProcess2, 10, $expiresAt))
            ->toThrow(\InvalidArgumentException::class);

        $freshItem = InventoryItem::find($itemProcess1->id);

        // Assert the final state of $freshItem
        expect($freshItem->quantity)->toBe(10);
        expect($freshItem->reserved_quantity)->toBe(10);
        expect($freshItem->available_quantity)->toBe(0);
    });
});


describe('ReservationExpirationTest', function () {

    test('expireReservationIfNeeded marks expired reservations and updates reserved quantity', function () {
        $domain = new InventoryDomain();
        $variant = createProductWithVariant();

        // Create an inventory item and an active reservation that is already expired
        $item = InventoryItem::create([
            'quantity' => 5,
            'reserved_quantity' => 2,
            'product_variant_id' => $variant->id,
        ]);
        $expiredAt = Carbon::now()->subMinute();

        /** @var Reservation $reservation */
        $reservation = $item->reservations()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'reserved_at' => Carbon::now()->subHours(2),
            'expires_at' => $expiredAt,
            'status' => ReservationStatus::Active,
        ]);

        // Ensure record state before expiration
        $item->refresh();
        expect($item->reserved_quantity)->toBe(2);
        expect($reservation->status)->toBe(ReservationStatus::Active);

        // Expire the reservation
        $result = $domain->expireReservationIfNeeded($reservation->fresh());

        // Expect it to return true (was expired now)
        expect($result)->toBeTrue();

        // The reservation status is updated
        $reservation->refresh();
        expect($reservation->status)->toBe(ReservationStatus::Expired);

        // The reserved quantity is decreased
        $item->refresh();
        expect($item->reserved_quantity)->toBe(0);
    });

    test('expireReservationIfNeeded returns false if reservation is not expired or not active', function () {
        $domain = new InventoryDomain();
        $variant = createProductWithVariant();

        // Create an inventory item and a reservation that's not yet expired
        $item = InventoryItem::create([
            'quantity' => 5,
            'reserved_quantity' => 2,
            'product_variant_id' => $variant->id,
        ]);
        $futureExpires = Carbon::now()->addMinutES(20);

        $reservation = $item->reservations()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'reserved_at' => Carbon::now()->subMinutes(1),
            'expires_at' => $futureExpires,
            'status' => ReservationStatus::Active,
        ]);

        // Not yet expired
        $result = $domain->expireReservationIfNeeded($reservation->fresh());
        expect($result)->toBeFalse();
        expect($reservation->fresh()->status)->toBe(ReservationStatus::Active);

        // Reservation not active
        $reservation->update(['status' => ReservationStatus::Expired]);
        $result2 = $domain->expireReservationIfNeeded($reservation->fresh());
        expect($result2)->toBeFalse();
        expect($reservation->fresh()->status)->toBe(ReservationStatus::Expired);
    });
    test('concurrent expireReservationIfNeeded does not cause lost update on reserved quantities', function () {
        $domain = new InventoryDomain();
        $variant = createProductWithVariant();

        // Create an inventory item and TWO active, already-expired reservations
        $item = InventoryItem::create([
            'quantity' => 10,
            'reserved_quantity' => 6,
            'product_variant_id' => $variant->id,
        ]);
        $expiredAt = Carbon::now()->subMinute();

        /** @var Reservation $reservation1 */
        $reservation1 = $item->reservations()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'reserved_at' => Carbon::now()->subHours(2),
            'expires_at' => $expiredAt,
            'status' => ReservationStatus::Active,
        ]);

        /** @var Reservation $reservation2 */
        $reservation2 = $item->reservations()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 4,
            'reserved_at' => Carbon::now()->subHours(1),
            'expires_at' => $expiredAt,
            'status' => ReservationStatus::Active,
        ]);

        // Ensure initial state
        $item->refresh();
        expect($item->reserved_quantity)->toBe(6);
        expect($reservation1->status)->toBe(ReservationStatus::Active);
        expect($reservation2->status)->toBe(ReservationStatus::Active);

        // Call expireReservationIfNeeded "concurrently" (in quick succession, as Laravel can't genuinely race in tests)

        $resultA = $domain->expireReservationIfNeeded($reservation1->fresh());

        $resultB = $domain->expireReservationIfNeeded($reservation2->fresh());
        // Print the DB queries executed during the concurrent expireReservationIfNeeded calls

        // Both should report they expired the reservation
        expect($resultA)->toBeTrue();
        expect($resultB)->toBeTrue();

        // Both reservation statuses are updated
        expect($reservation1->fresh()->status)->toBe(ReservationStatus::Expired);
        expect($reservation2->fresh()->status)->toBe(ReservationStatus::Expired);

        // The reserved quantity should be properly reduced by both (never go below zero, and no lost update)
        $item->refresh();
        expect($item->reserved_quantity)->toBe(0);
    });
});


describe('StockMovementTest', function () {
    it('can increment stock for a given product_variant_id', function () {
        $domain = new InventoryDomain();

        $productVariant = createProductWithVariant();

        $result = $domain->moveStock($productVariant->id, 10, StockMovementReason::Purchase);

        expect($result)->toBeTrue();

        $inventoryItem = InventoryItem::where('product_variant_id', $productVariant->id)->first();

        expect($inventoryItem)->not->toBeNull();
        expect($inventoryItem->quantity)->toBe(10);
        expect($inventoryItem->reserved_quantity)->toBe(0);

        $movement = \App\Models\Inventory\StockMovement::where('inventory_item_id', $inventoryItem->id)->latest()->first();
        expect($movement)->not->toBeNull();
        expect($movement->quantity)->toBe(10);
        expect($movement->reason)->toBe(StockMovementReason::Purchase);
    });

    it('can decrement stock for a given product_variant_id', function () {
        $domain = new InventoryDomain();

        $productVariant = createProductWithVariant();

        // Start with quantity 20
        $inventoryItem = \App\Models\Inventory\InventoryItem::factory()->create([
            'product_variant_id' => $productVariant->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
        ]);

        $result = $domain->moveStock($productVariant->id, -5, StockMovementReason::Sale);

        expect($result)->toBeTrue();

        $inventoryItem->refresh();
        expect($inventoryItem->quantity)->toBe(15);

        $movement = \App\Models\Inventory\StockMovement::where('inventory_item_id', $inventoryItem->id)->latest()->first();
        expect($movement)->not->toBeNull();
        expect($movement->quantity)->toBe(-5);
        expect($movement->reason)->toBe(StockMovementReason::Sale);
    });

    it('cannot decrement stock below zero', function () {
        $domain = new InventoryDomain();

        $productVariant = createProductWithVariant();

        InventoryItem::factory()->create([
            'product_variant_id' => $productVariant->id,
            'quantity' => 3,
            'reserved_quantity' => 0,
        ]);

        expect(function () use ($domain, $productVariant) {
            $domain->moveStock($productVariant->id, -5, StockMovementReason::Adjustment);
        })->toThrow(\DomainException::class, 'Inventory quantity cannot be negative.');

        $inventoryItem = InventoryItem::where('product_variant_id', $productVariant->id)->first();
        expect($inventoryItem->quantity)->toBe(3);
    });

    it('creates inventory item if not exists before moving stock', function () {
        $domain = new \App\Core\Inventory\InventoryDomain();

        $productVariant = createProductWithVariant();

        // InventoryItem does not exist yet

        $result = $domain->moveStock($productVariant->id, 7, StockMovementReason::Adjustment);

        expect($result)->toBeTrue();

        $inventoryItem = InventoryItem::where('product_variant_id', $productVariant->id)->first();
        expect($inventoryItem)->not->toBeNull();
        expect($inventoryItem->quantity)->toBe(7);
    });
    it('prevents lost updates when moving stock concurrently', function () {

        $domain = new InventoryDomain();
        $productVariant = createProductWithVariant();

        $inventoryItem = InventoryItem::create([
            'product_variant_id' => $productVariant->id,
            'quantity' => 15,
            'reserved_quantity' => 0,
        ]);

        // Simulate two separate "processes" reading the inventory item
        $itemProcess1 = InventoryItem::find($inventoryItem->id);
        $itemProcess2 = InventoryItem::find($inventoryItem->id);

        // Process 1 wants to decrement 5 due to Sale
        $result1 = $domain->moveStock($itemProcess1->product_variant_id, -5, StockMovementReason::Sale);
        expect($result1)->toBeTrue();

        // At this point inventory is 10, but itemProcess2 believes it's still 15
        // Process 2 decrements 3 due to Adjustment
        \DB::enableQueryLog();
        
        $result2 = $domain->moveStock($itemProcess2->product_variant_id, -3, StockMovementReason::Adjustment);
        $queries = \DB::getQueryLog();

        expect($result2)->toBeTrue();

        $inventoryItem->refresh();
        expect($inventoryItem->quantity)->toBe(7);

        // Check both stock movement records exist
        $movements = \App\Models\Inventory\StockMovement::where('inventory_item_id', $inventoryItem->id)
            ->orderBy('id')->get();

        expect($movements)->toHaveCount(2);
        expect($movements[0]->quantity)->toBe(-5);
        expect($movements[0]->reason)->toBe(StockMovementReason::Sale);
        expect($movements[1]->quantity)->toBe(-3);
        expect($movements[1]->reason)->toBe(StockMovementReason::Adjustment);
    });

});
