<?php

use App\Filament\Resources\Order\Orders\OrderResource;
use App\Filament\Resources\Order\Orders\Pages\ListOrders;
use App\Models\Catalog\Color;
use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderStatus;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'local']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('requires authentication for the listing', function () {
    $this->get(OrderResource::getUrl())->assertRedirect(route('filament.admin.auth.login'));
});

it('respects resource authorization', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'viewAny' ? false : null);

    $this->get(OrderResource::getUrl())->assertForbidden();
});

it('renders a read only listing with customer status currency and units', function () {
    $this->actingAs(User::factory()->create());
    $customer = Customer::factory()->create(['f_name' => 'Lina', 'l_name' => 'Saleh']);
    $order = Order::factory()->for($customer)->create(['status' => OrderStatus::CONFIRMED, 'total_amount' => 1234.50]);
    $color = Color::query()->create(['name' => 'Black', 'code' => 'BLACK', 'hex_code' => '#000000']);
    $variant = ProductVariant::factory()->afterMaking(function (ProductVariant $variant) use ($color): void {
        unset($variant->color);
        $variant->color_id = $color->id;
    })->create();
    OrderItem::factory()->for($order)->for($variant)->create(['quantity' => 3]);
    OrderItem::factory()->for($order)->for($variant)->create(['quantity' => 2]);

    $this->get(OrderResource::getUrl())->assertOk();
    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords([$order])
        ->assertTableColumnStateSet('customer_name', 'Lina Saleh', record: $order)
        ->assertTableColumnFormattedStateSet('status', 'Confirmed', record: $order)
        ->assertTableColumnFormattedStateSet('total_amount', '$1,234.50', record: $order)
        ->assertTableColumnStateSet('order_items_sum_quantity', 5, record: $order)
        ->assertSee('Unavailable')
        ->assertActionDoesNotExist('create')
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete')
        ->assertTableActionDoesNotExist('view');
});

it('searches order numbers and customer contact details', function (string $search) {
    $this->actingAs(User::factory()->create());
    $customer = Customer::factory()->create(['f_name' => 'Lina', 'l_name' => 'Saleh', 'email' => 'lina@example.test', 'phone' => '0991234567']);
    $match = Order::factory()->for($customer)->create(['order_number' => 'ORDER-MATCH']);
    $other = Order::factory()->create();

    Livewire::test(ListOrders::class)->searchTable($search)
        ->assertCanSeeTableRecords([$match])->assertCanNotSeeTableRecords([$other]);
})->with(['ORDER-MATCH', 'Lina Saleh', 'lina@example.test', '0991234567']);

it('filters enum and legacy order statuses including pending zero', function () {
    $this->actingAs(User::factory()->create());
    $legacy = Order::factory()->create();
    $pending = Order::factory()->create(['status' => OrderStatus::PENDING]);
    $confirmed = Order::factory()->create(['status' => OrderStatus::CONFIRMED]);

    Livewire::test(ListOrders::class)->filterTable('status', OrderStatus::PENDING->value)
        ->assertCanSeeTableRecords([$legacy, $pending])->assertCanNotSeeTableRecords([$confirmed])
        ->filterTable('status', OrderStatus::CONFIRMED->value)
        ->assertCanSeeTableRecords([$confirmed])->assertCanNotSeeTableRecords([$legacy, $pending]);
});

it('filters an inclusive creation date range', function () {
    $this->actingAs(User::factory()->create());
    $start = Order::factory()->create(['created_at' => '2026-10-10 00:00:00']);
    $end = Order::factory()->create(['created_at' => '2026-10-12 23:59:59']);
    $outside = Order::factory()->create(['created_at' => '2026-10-13 00:00:00']);

    Livewire::test(ListOrders::class)->filterTable('created_at', ['from' => '2026-10-10', 'until' => '2026-10-12'])
        ->assertCanSeeTableRecords([$start, $end])->assertCanNotSeeTableRecords([$outside]);
});

it('sorts newest first and supports date and amount sorting', function () {
    $this->actingAs(User::factory()->create());
    $older = Order::factory()->create(['created_at' => '2026-10-10', 'total_amount' => 500]);
    $newer = Order::factory()->create(['created_at' => '2026-10-11', 'total_amount' => 100]);

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true)
        ->sortTable('created_at', 'asc')->assertCanSeeTableRecords([$older, $newer], inOrder: true)
        ->sortTable('total_amount', 'asc')->assertCanSeeTableRecords([$newer, $older], inOrder: true)
        ->sortTable('total_amount', 'desc')->assertCanSeeTableRecords([$older, $newer], inOrder: true);
});

it('paginates orders', function () {
    $this->actingAs(User::factory()->create());
    $orders = Order::factory()->count(6)->sequence(fn ($sequence) => ['created_at' => '2026-10-'.(10 + $sequence->index)])->create();

    Livewire::test(ListOrders::class)->set('tableRecordsPerPage', 5)
        ->assertCanSeeTableRecords($orders->slice(1))->assertCanNotSeeTableRecords([$orders->first()])
        ->call('gotoPage', 2)->assertCanSeeTableRecords([$orders->first()])->assertCanNotSeeTableRecords($orders->slice(1));
});

it('handles missing contact details and order items', function () {
    $this->actingAs(User::factory()->create());
    $customer = Customer::factory()->create(['f_name' => '', 'l_name' => '', 'email' => null, 'phone' => null]);
    $order = Order::factory()->for($customer)->create();

    Livewire::test(ListOrders::class)
        ->assertTableColumnStateSet('customer_name', 'Unknown customer', record: $order)
        ->assertTableColumnFormattedStateSet('status', 'Pending', record: $order)
        ->assertTableColumnStateSet('order_items_sum_quantity', 0, record: $order);
});

it('keeps query counts constant as the number of displayed orders grows', function () {
    $this->actingAs(User::factory()->create());
    Order::factory()->create();
    Livewire::test(ListOrders::class);
    DB::enableQueryLog();
    DB::flushQueryLog();
    Livewire::test(ListOrders::class);
    $singleOrderQueries = count(DB::getQueryLog());
    DB::disableQueryLog();
    Order::factory()->count(8)->create();
    DB::enableQueryLog();
    DB::flushQueryLog();

    Livewire::test(ListOrders::class)->assertCountTableRecords(9);
    $multipleOrderQueries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($multipleOrderQueries)->toBe($singleOrderQueries);
});
