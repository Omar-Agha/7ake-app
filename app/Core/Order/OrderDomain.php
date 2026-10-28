<?php

namespace App\Core\Order;

use App\Core\Customer\CurrentCustomer;
use App\Core\FeeCalculator\CustomerDiscountCalculator;
use App\Core\FeeCalculator\ShipmentFeeCalculator;
use App\Core\Inventory\InventoryDomain;
use App\Events\OrderPlaced;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Customer\CustomerAddress;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use App\Models\Order\PaymentMethod;


class OrderDomain
{
    /**
     * OrderDomain constructor.
     */
    public function __construct(
        protected CurrentCustomer $currentCustomer,
        protected InventoryDomain $inventoryDomain,
        protected CustomerDiscountCalculator $customerDiscountCalculator,
        protected ShipmentFeeCalculator $shipmentFeeCalculator,
        protected OrderNoGenerator $orderNoGenerator
    ) {
        // No dependencies to inject at the moment.
    }
    public function createOrder(array $data): Order
    {


        $customer_address = CustomerAddress::find($data['address_id']);

        $orderItems = collect($data['order_items'])->map(function ($item) {
            $product_v = ProductVariant::with('product')->find($item['product_variant_id']);
            return new \App\Models\Order\OrderItem([
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'product_name' => $product_v->product->name,
                'price' => $product_v->price,
                'total' => $item['quantity'] * $product_v->price,
                'sku' => $product_v->product->sku,
                'currency' => 'syp'
            ]);
        });

        $discount_amount = $this->customerDiscountCalculator->calculate($this->currentCustomer->get(), $orderItems);
        $shipment_amount = $this->shipmentFeeCalculator->calculate($customer_address, $orderItems);
        $order_no = $this->orderNoGenerator->generate();
        return \DB::transaction(function () use ($data, $order_no, $shipment_amount, $discount_amount, $orderItems, $customer_address) {



            [$order, $items] = Order::placeNewOrder(
                $this->currentCustomer->get()->id,
                $customer_address,
                $order_no,
                PaymentMethod::from($data['payment_method']),
                $shipment_amount,
                $discount_amount,
                $orderItems
            );

            $order->save();
            $order->orderItems()->saveMany($items);
            foreach ($items as  $value) {
                $this->inventoryDomain->reserveForOrderItem($value->product_variant_id, $value->quantity, $value->id);
            }

            event(new OrderPlaced($order));
            return $order;
        });
    }

    public function markAsPaid(Order $order)
    {
        if ($order->payment_status === \App\Models\Order\OrderPaymentStatus::PAID) {
            throw new \DomainException('Order is already paid.');
        }
   
        $order->pay();
    }
}
