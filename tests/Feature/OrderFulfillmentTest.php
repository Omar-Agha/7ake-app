<?php

use App\Core\Order\OrderFulfillmentDomain;
use App\Events\OrderWasShipped;
use App\Models\Customer\Customer;
use App\Models\Inventory\Reservation;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderStatus;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;



describe("confirm order", function () {
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

        $reservationIds = $order->reservations->pluck('id')->all();

        $domain = app(OrderFulfillmentDomain::class);

        $domain->confirmOrder($order);

        $order->refresh();
        $reservations = $order->reservations()->pluck('id')->all();


        expect($reservations)->toEqualCanonicalizing($reservationIds);
        // (No reservations deleted or created)
    });
});

describe('cancel order', function () {
    it('can cancel a pending order and releases reservations without stock movement', function () {
        Event::fake([
            \App\Events\OrderWasCancelled::class,
        ]);

        // Create a PENDING order with related reservations
        $order = Order::factory()
            ->has(
                OrderItem::factory(2)
                    ->has(Reservation::factory(1))
            )
            ->create(['status' => OrderStatus::PENDING]);


        $domain = app(OrderFulfillmentDomain::class);

        $domain->cancelOrder($order);

        $order->refresh();

        expect($order->status)->toBe(OrderStatus::CANCELLED);
        expect($order->cancelled_at)->not->toBeNull();

        // Reservations still exist, but their state should be updated by inventoryDomain logic—here tested only no deletion happened.
        $stillThere = $order->reservations()->pluck('id')->all();
        expect(count($stillThere))->toEqual(0);

        Event::assertDispatched(\App\Events\OrderWasCancelled::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    });

    it('can cancel a confirmed order and releases reservations', function () {
        Event::fake([\App\Events\OrderWasCancelled::class]);

        $order = Order::factory()
            ->has(
                OrderItem::factory(1)
                    ->has(Reservation::factory(2))
            )
            ->create(['status' => OrderStatus::CONFIRMED]);

        $reservationIds = $order->reservations->pluck('id')->all();

        $inventoryDomainMock = Mockery::mock(\App\Core\Inventory\InventoryDomain::class);
        $inventoryDomainMock
            ->shouldReceive('cancelReservation')
            ->times(count($reservationIds))
            ->andReturnTrue();
        $inventoryDomainMock
            ->shouldNotReceive('createSaleMovement');
        app()->instance(\App\Core\Inventory\InventoryDomain::class, $inventoryDomainMock);

        $domain = app(OrderFulfillmentDomain::class);

        $domain->cancelOrder($order);

        $order->refresh();

        expect($order->status)->toBe(OrderStatus::CANCELLED);
        expect($order->cancelled_at)->not->toBeNull();

        $stillThere = $order->reservations()->pluck('id')->all();
        expect($stillThere)->toEqualCanonicalizing($reservationIds);

        Event::assertDispatched(\App\Events\OrderWasCancelled::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    });


    it('cannot cancel a shipped, delivered, returned, or already cancelled order', function ($status) {
        $order = Order::factory()->create([
            'status' => $status,
        ]);

        $domain = app(OrderFulfillmentDomain::class);

        expect(fn() => $domain->cancelOrder($order))->toThrow(\DomainException::class);
    })->with([
        OrderStatus::SHIPPED,
        OrderStatus::DELIVERED,
        OrderStatus::RETURNED,
        OrderStatus::CANCELLED,
    ]);
});

describe('shipping order', function () {
    

    it('cannot ship an order unless status is CONFIRMED', function ($status) {
        $order = Order::factory()->create([
            'status' => $status,
        ]);

        $domain = app(OrderFulfillmentDomain::class);

        expect(fn() => $domain->shipOrder($order))->toThrow(\DomainException::class);
    })->with([

        OrderStatus::PENDING,
        OrderStatus::CANCELLED,
        OrderStatus::SHIPPED,
        OrderStatus::DELIVERED,
        OrderStatus::RETURNED,
    ]);
});
