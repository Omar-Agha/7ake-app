<?php

use App\Core\Customer\CurrentCustomer;
use App\Core\FeeCalculator\CustomerDiscountCalculator;
use App\Core\FeeCalculator\ShipmentFeeCalculator;
use App\Core\Inventory\InventoryDomain;
use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use App\Core\Order\OrderManager;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Catalog\ProductVariant;
use App\Models\Customer\CustomerAddress;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use App\Models\Order\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use App\Events\OrderPlaced;
use App\Models\Inventory\InventoryItem;
use Database\Factories\Inventory\InventoryItemFactory;

function createProducts(int $count,int $product_v_count, Sequence $sequence)
{
    return  Product::factory($count)
        ->has(
            ProductVariant::factory($product_v_count)
                ->has(
                    InventoryItem::factory(1)->state($sequence),
                    'inventory'
                )
        )->create();
}

function setup()
{
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->hasAddresses(4)->create();
    $address = $customer->addresses->first();



    return [$user, $customer, $address];
}
// Use Pest's withFaker to get access to the faker instance
uses(WithFaker::class);

it('creates an order successfully with valid data', function () {

    Event::fake([OrderPlaced::class]);
    [$user, $customer, $address] = setup();

    $this->mock(CurrentCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('get')
            ->andReturn($customer);
    });

    $product = createProducts(1,1, new Sequence(['quantity' => 2, 'reserved_quantity' => 0]));


    /** @var ProductVariant $productVariant */
    $productVariant = $product->first()->productVariants()->first();

    // Prepare request data
    $orderItems = [
        [
            'product_variant_id' => $productVariant->id,
            'quantity' => 2,
        ],
    ];

    $requestData = [
        'address_id' => $address->id,
        'order_items' => $orderItems,
        'payment_method' => PaymentMethod::COD->value,
    ];



    $orderManager = app(OrderManager::class);


    \DB::enableQueryLog();
    $order = $orderManager->createOrder($requestData);
    $x = \DB::getQueryLog();
    expect($order)
        ->toBeInstanceOf(Order::class)
        ->and($order->customer_id)->toBe($customer->id)
        ->and($order->shipping_address['id'])->toBe($address->id)
        ->and($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems[0]->product_variant_id)->toBe($productVariant->id)
        ->and($order->orderItems[0]->quantity)->toBe(2)
        ->and($order->status)->toBe(OrderStatus::PENDING);

    // Assert event was dispatched
    Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
});

it('creates an order from 3 different products and 5 product variants', function () {
    Event::fake([OrderPlaced::class]);
    [$user, $customer, $address] = setup();
    $this->mock(CurrentCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('get')
            ->andReturn($customer);
    });

    $orderManager = app(OrderManager::class);

    $products = createProducts(10,1, new Sequence(['quantity' => 99, 'reserved_quantity' => 0]));



    $productVariants = $products->flatMap(function ($product) {
        return $product->productVariants;
    })->take(5);

    expect($productVariants)->toHaveCount(5);


    // Build order_items with each variant having random quantity between 1 and 4
    $orderItems = $productVariants->map(function ($variant) {
        return [
            'product_variant_id' => $variant->id,
            'quantity' => rand(1, 4),
        ];
    })->values()->toArray();

    $requestData = [
        'address_id' => $address->id,
        'order_items' => $orderItems,
        'payment_method' => PaymentMethod::COD->value,
    ];




    
    $order = $orderManager->createOrder($requestData);

    expect($order)
        ->toBeInstanceOf(Order::class)
        ->and($order->customer_id)->toBe($customer->id)
        ->and($order->shipping_address['id'])->toBe($address->id)
        ->and($order->orderItems)->toHaveCount(5)
        ->and(collect($order->orderItems)->pluck('product_variant_id')->sort()->values()->all())
        ->toEqual($productVariants->pluck('id')->sort()->values()->all())
        ->and($order->status)->toBe(OrderStatus::PENDING);

    // Assert event was dispatched
    Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
});

it('throw exception when no inventory available', function () {
    Event::fake([OrderPlaced::class]);
    [$user, $customer, $address] = setup();
    $this->mock(CurrentCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('get')
            ->andReturn($customer);
    });
    


    $orderManager = app(OrderManager::class);

    $products = createProducts(10,2, new Sequence(
        ['quantity' => 5, 'reserved_quantity' => 0],
        ['quantity' => 1, 'reserved_quantity' => 0]
        ));



    $productVariants = $products->flatMap(function ($product) {
        return $product->productVariants;
    })->take(5);

    expect($productVariants)->toHaveCount(5);


    // Build order_items with each variant having random quantity between 1 and 4
    $orderItems = $productVariants->sortByDesc('quantity')->map(function ($variant) {
        return [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ];
    })->values()->toArray();

    $requestData = [
        'address_id' => $address->id,
        'order_items' => $orderItems,
        'payment_method' => PaymentMethod::COD->value,
    ];





    expect(fn() => $orderManager->createOrder($requestData))
    ->toThrow(DomainException::class);
});

it('check amount & fee values to be valid', function () {
    Event::fake([OrderPlaced::class]);
    [$user, $customer, $address] = setup();

    $this->mock(CurrentCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('get')
            ->andReturn($customer);
    });

    $this->mock(CustomerDiscountCalculator::class, function ($mock) {
        $mock->shouldReceive('calculate')
            ->andReturn(30);
    });

    $this->mock(ShipmentFeeCalculator::class, function ($mock) {
        $mock->shouldReceive('calculate')
            ->andReturn(10);
    });

    $orderManager = app(OrderManager::class);

    $products = createProducts(10, 1, new Sequence(['quantity' => 99, 'reserved_quantity' => 0]));
    $productVariants = $products->flatMap(function ($product) {
        return $product->productVariants;
    })->take(5);

    expect($productVariants)->toHaveCount(5);

    $orderItems = $productVariants->map(function ($variant) {
        // Fixed quantity to ensure deterministic subtotal
        return [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ];
    })->values()->toArray();

    $requestData = [
        'address_id' => $address->id,
        'order_items' => $orderItems,
        'payment_method' => PaymentMethod::COD->value,
    ];

    $order = $orderManager->createOrder($requestData);

    // Check the order amounts
    $expectedSubtotal = collect($orderItems)->reduce(function ($carry, $item) use ($productVariants) {
        $variant = $productVariants->firstWhere('id', $item['product_variant_id']);
        return $carry + ($variant->price * $item['quantity']);
    }, 0);

    expect($order->sub_total)->toBe($expectedSubtotal);
    expect($order->discount_amount)->toBeFloat()->toBe(30.0);
    expect($order->shipping_amount)->toBeFloat()->toBe(10.0);
    expect($order->total_amount)->toBeFloat()->toBe($expectedSubtotal + 10.0 - 30.0);



    
});