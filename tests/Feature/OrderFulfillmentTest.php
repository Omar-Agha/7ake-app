<?php

use App\Core\Order\OrderFulfillmentDomain;
use App\Models\Customer\Customer;
use App\Models\Inventory\Reservation;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderStatus;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;




it('confirms a pending order', function () {
    Event::fake();

    $customer = Customer::factory()->create();

    /** @var \App\Models\Order\Order $order */
    $order = Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::PENDING,
        ]);

    // Assume inventoryDomain dependency is not important for confirmOrder
    $domain = app(OrderFulfillmentDomain::class);

    $now = now();
    Carbon::setTestNow($now);

    $domain->confirmOrder($order->refresh());

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::CONFIRMED);
    // Make sure the confirmed_at (or similar) timestamp is set if exists
    if (array_key_exists('confirmed_at', $order->getAttributes())) {
        expect($order->confirmed_at)->not->toBeNull();
    }

    Event::assertDispatched(\App\Events\OrderWasConfirmed::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
});

it('does not confirm non-pending order', function ($status) {
    $order = Order::factory()->create([
        'status' => $status,
    ]);

    $domain = app(OrderFulfillmentDomain::class);

    expect(fn() => $domain->confirmOrder($order))->toThrow(\DomainException::class);
})->with([
    OrderStatus::CONFIRMED,
    OrderStatus::SHIPPED,
    OrderStatus::DELIVERED,
    OrderStatus::CANCELLED,
    OrderStatus::RETURNED,
]);

it('confirmation does not change or remove existing inventory reservations', function () {
    // Assume order has a relationship 'reservations' and reservations are not affected by confirmOrder
    $order = Order::factory()
        ->has(
            OrderItem::factory(2)
            ->has(Reservation::factory())
        )
        ->create(['status' => OrderStatus::PENDING]);
    $v = $order->toArray();
    $h = $order->reservations;
    $reservationIds = $order->reservations->pluck('id')->all();

    $domain = app(OrderFulfillmentDomain::class);

    $domain->confirmOrder($order);

    $order->refresh();
    $reservations = $order->reservations()->pluck('id')->all();
    $x = $reservations = $order->reservations()->get();

    expect($reservations)->toEqualCanonicalizing($reservationIds);
    // (No reservations deleted or created)
});

it('confirmation does not deduct from physical stock', function () {
    // This assumes inventory stock and movements are out of scope of confirmOrder
    // We'll assert that InventoryDomain->createSaleMovement etc is NOT called during confirmation

    $order = Order::factory()->hasReservations(1)->create([
        'status' => OrderStatus::PENDING,
    ]);

    // Mock the InventoryDomain dependency and expect no createSaleMovement or similar call
    $inventoryDomain = Mockery::mock(\App\Core\Inventory\InventoryDomain::class);
    // No method should be called on this mock

    app()->bind(\App\Core\Inventory\InventoryDomain::class, fn() => $inventoryDomain);

    $domain = app(OrderFulfillmentDomain::class);

    $domain->confirmOrder($order);

    // (If InventoryDomain is not used, test passes. If it is, test will fail.)
});
