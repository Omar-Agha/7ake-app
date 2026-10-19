<?php

namespace App\Core\Order;

use App\Core\Inventory\InventoryDomain;
use App\Events\OrderWasCancelled;
use App\Events\OrderWasConfirmed;
use App\Events\OrderWasDelivered;
use App\Events\OrderWasShipped;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use Carbon\Carbon;

class OrderFulfillmentDomain
{
    // This class will encapsulate domain logic for order fulfillment.
    /**
     * OrderFulfillmentDomain constructor.
     */
    public function __construct(protected InventoryDomain $inventoryDomain)
    {
        // Inject dependencies here if needed in future (e.g., repositories, services).
    }


    public function confirmOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::PENDING) {
            throw new \DomainException("order Cannot be confirmed");
        }
        
        $order->confirm(Carbon::now());
        $order->save();
        event(new OrderWasConfirmed($order));
    }
    public function cancelOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::PENDING && $order->status != OrderStatus::CONFIRMED) {
   
            throw new \DomainException("order Cannot be confirmed");
        }
        $order->load(["reservations", "orderItems"]);
        // some business validation
        \DB::transaction(function () use ($order) {
            $order->cancel(Carbon::now());
            foreach ($order->reservations as $reservation) {
                $this->inventoryDomain->cancelReservation($reservation);
            }
            $order->save();

        });
        event(new OrderWasCancelled($order));
    }
    public function shipOrder(Order $order): void {
        if ($order->status !== OrderStatus::CONFIRMED) {
            throw new \DomainException("order cannot be shipped");
        }
        $order->load(["reservations", "orderItems"]);

        \DB::transaction(function () use ($order) {
            $order->shipping(Carbon::now());
            foreach ($order->reservations as $reservation) {
                $this->inventoryDomain->commitReservation($reservation);
            }
            $items = $order->orderItems->map(fn ($orderItem) => [
                'quantity' => $orderItem->quantity,
                'product_v_id' => $orderItem->product_variant_id,
            ])->toArray();
            $this->inventoryDomain->createSaleMovement($items);
            $order->save();
        });
        event(new OrderWasShipped($order));

    }
    public function deliverOrder(Order $order): void {
        if ($order->status !== OrderStatus::SHIPPED) {
            throw new \DomainException("order cannot be delivered");
        }
        $order->deliver(Carbon::now());
        $order->save();
        event(new OrderWasDelivered($order));
    }
}
