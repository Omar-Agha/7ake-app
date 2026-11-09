<?php

use App\Core\Order\OrderFulfillmentDomain;
use App\Enums\ReservationStatus;
use App\Events\OrderWasConfirmed;
use App\Events\OrderWasDelivered;
use App\Filament\Resources\Order\Orders\Pages\ListOrders;
use App\Models\Catalog\Color;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\Reservation;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderPaymentStatus;
use App\Models\Order\OrderStatus;
use App\Models\Order\PaymentMethod;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'local']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('executes an eligible action and refreshes the displayed status', function (string $action, OrderStatus $initial, OrderStatus $expected, string $label, string $event) {
    $this->actingAs(User::factory()->create());
    $order = Order::factory()->create(['status' => $initial, 'payment_method' => PaymentMethod::COD, 'payment_status' => OrderPaymentStatus::UNPAID]);
    Event::fake([$event]);

    Livewire::test(ListOrders::class)->callAction(TestAction::make($action)->table($order))
        ->assertNotified()->assertTableColumnFormattedStateSet('status', $label, record: $order);

    expect($order->fresh()->status)->toBe($expected);
    expect($order->fresh()->payment_status)->toBe(OrderPaymentStatus::UNPAID);
    Event::assertDispatchedTimes($event, 1);
})->with([
    ['confirmOrder', OrderStatus::PENDING, OrderStatus::CONFIRMED, 'Confirmed', OrderWasConfirmed::class],
    ['deliverOrder', OrderStatus::SHIPPED, OrderStatus::DELIVERED, 'Delivered', OrderWasDelivered::class],
]);

it('shows lifecycle actions only in their eligible statuses', function (OrderStatus $status) {
    $this->actingAs(User::factory()->create());
    $order = Order::factory()->create(['status' => $status]);
    $component = Livewire::test(ListOrders::class);

    $confirmAssertion = $status === OrderStatus::PENDING ? 'assertActionVisible' : 'assertActionHidden';
    $deliverAssertion = $status === OrderStatus::SHIPPED ? 'assertActionVisible' : 'assertActionHidden';
    $component->{$confirmAssertion}(TestAction::make('confirmOrder')->table($order));
    $component->{$deliverAssertion}(TestAction::make('deliverOrder')->table($order));
})->with(OrderStatus::cases());

it('rejects invalid transitions in the service', function (string $method, OrderStatus $status) {
    $order = Order::factory()->create(['status' => $status]);
    Event::fake([OrderWasConfirmed::class, OrderWasDelivered::class]);

    expect(fn () => app(OrderFulfillmentDomain::class)->{$method}($order))->toThrow(DomainException::class);

    expect($order->fresh()->status)->toBe($status);
    Event::assertNothingDispatched();
})->with([
    ['confirmOrder', OrderStatus::CONFIRMED],
    ['confirmOrder', OrderStatus::SHIPPED],
    ['confirmOrder', OrderStatus::DELIVERED],
    ['confirmOrder', OrderStatus::CANCELLED],
    ['confirmOrder', OrderStatus::RETURNED],
    ['deliverOrder', OrderStatus::PENDING],
    ['deliverOrder', OrderStatus::CONFIRMED],
    ['deliverOrder', OrderStatus::DELIVERED],
    ['deliverOrder', OrderStatus::CANCELLED],
    ['deliverOrder', OrderStatus::RETURNED],
]);

it('rejects duplicate transitions from stale model instances', function (string $method, OrderStatus $initial, string $event) {
    $order = Order::factory()->create(['status' => $initial]);
    $staleOrder = $order->fresh();
    Event::fake([$event]);
    $service = app(OrderFulfillmentDomain::class);
    $service->{$method}($order);

    expect(fn () => $service->{$method}($staleOrder))->toThrow(DomainException::class);

    Event::assertDispatchedTimes($event, 1);
})->with([
    ['confirmOrder', OrderStatus::PENDING, OrderWasConfirmed::class],
    ['deliverOrder', OrderStatus::SHIPPED, OrderWasDelivered::class],
]);

it('honors denied update permissions when invoking an action', function (string $action, OrderStatus $status) {
    $this->actingAs(User::factory()->create());
    $order = Order::factory()->create(['status' => $status]);
    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'update' ? false : null);

    Livewire::test(ListOrders::class)
        ->assertActionHidden(TestAction::make($action)->table($order))
        ->mountAction(TestAction::make($action)->table($order))
        ->assertSet('mountedActions', []);

    expect($order->fresh()->status)->toBe($status);
})->with([
    ['confirmOrder', OrderStatus::PENDING],
    ['deliverOrder', OrderStatus::SHIPPED],
]);

it('reports domain errors without changing the order', function () {
    $this->actingAs(User::factory()->create());
    $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
    $this->mock(OrderFulfillmentDomain::class)->shouldReceive('confirmOrder')->once()->andThrow(new DomainException('Order state changed.'));

    Livewire::test(ListOrders::class)->callAction(TestAction::make('confirmOrder')->table($order))
        ->assertNotified('Order could not be confirmed');

    expect($order->fresh()->status)->toBe(OrderStatus::PENDING);
});

it('does not expose actions with incomplete inventory or payment workflows', function () {
    $this->actingAs(User::factory()->create());
    Order::factory()->create(['status' => OrderStatus::CONFIRMED, 'payment_method' => PaymentMethod::COD]);

    Livewire::test(ListOrders::class)
        ->assertTableActionDoesNotExist('cancelOrder')
        ->assertTableActionDoesNotExist('shipOrder')
        ->assertTableActionDoesNotExist('confirmCodCollection');
});

it('confirms orders created with the legacy pending default', function () {
    $order = Order::factory()->create();

    app(OrderFulfillmentDomain::class)->confirmOrder($order);

    expect($order->status)->toBe(OrderStatus::CONFIRMED);
});

it('confirms without changing stock or active reservations', function () {
    $color = Color::query()->create(['name' => 'Black', 'code' => 'BLACK', 'hex_code' => '#000000']);
    $variant = ProductVariant::factory()->afterMaking(function (ProductVariant $variant) use ($color): void {
        unset($variant->color);
        $variant->color_id = $color->id;
    })->create();
    $inventory = $variant->inventory;
    $inventory->update(['quantity' => 10, 'reserved_quantity' => 2]);
    $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
    $item = OrderItem::factory()->for($order)->for($variant)->create(['quantity' => 2]);
    $reservation = Reservation::factory()->create([
        'inventory_item_id' => $inventory->id,
        'order_item_id' => $item->id,
        'quantity' => 2,
    ]);

    app(OrderFulfillmentDomain::class)->confirmOrder($order);

    expect($inventory->fresh()->quantity)->toBe(10);
    expect($inventory->fresh()->reserved_quantity)->toBe(2);
    $this->assertModelExists($reservation);
    expect($reservation->fresh()->status)->toBe(ReservationStatus::Active);
    $this->assertDatabaseCount('stock_movements', 0);
});

it('rolls back a transition when a synchronous domain listener fails', function (string $method, OrderStatus $status, string $event) {
    $order = Order::factory()->create(['status' => $status]);
    Event::listen($event, function (): void {
        throw new RuntimeException('Listener failed.');
    });

    expect(fn () => app(OrderFulfillmentDomain::class)->{$method}($order))->toThrow(RuntimeException::class);

    expect($order->fresh()->status)->toBe($status);
})->with([
    ['confirmOrder', OrderStatus::PENDING, OrderWasConfirmed::class],
    ['deliverOrder', OrderStatus::SHIPPED, OrderWasDelivered::class],
]);
