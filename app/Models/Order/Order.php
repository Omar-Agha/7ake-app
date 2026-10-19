<?php

namespace App\Models\Order;

use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use App\Models\Inventory\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Ramsey\Collection\Collection;

class Order extends Model
{

    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here, for example:
        'customer_id',
        'order_number',
        'sub_total',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'shipping_address',
        'status',
        'payment_method',
        'payment_status'

    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'sub_total' => 'float',
        'shipping_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'shipping_address' => 'array',
    ];

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /**
     * Get all product variants for the order (through order items), eager loading their related product.
     */
    public function productVariants(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            ProductVariant::class,
            OrderItem::class,
        )->with('product');
    }

    /**
     * Get the reservations associated with the order via its order items.
     */
    /**
     * Get all reservations associated with the order through its order items.
     */
    public function reservations():HasManyThrough
    {
        //  SQL: select "id" from "reservations" inner join "order_items" on "order_items"."id" = "reservations"."order_item_id" where "order_items"."order_id" = 1)
        return $this->hasManyThrough(
            Reservation::class,
            \App\Models\Order\OrderItem::class,
            'order_id',           // Foreign key on OrderItem table...
            'order_item_id',      // Foreign key on Reservation table...
            'id',                 // Local key on Order table...
            'id'                  // Local key on OrderItem table...
        );
    }

    /**
     * Create a new Order instance.
     *
     * @param array $attributes
     */
    /**
     * Place a new order with the provided attributes.
     *
     * @param array $attributes
     
     */
    public static function placeNewOrder(
        int $customer_id,
        CustomerAddress $customerAddress,
        string $order_no,
        PaymentMethod $payment_method,
        float $shipping_amount,
        float $discount_amount,
        $orderItems,
    ) {

        $sub_total = collect($orderItems)->sum(function (OrderItem $orderItem) {

            return $orderItem->price  * $orderItem->quantity;
        });

        $attributes = [
            'customer_id' => $customer_id,
            'order_number' => $order_no,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::UNPAID,
            'payment_method' => $payment_method,
            'shipping_amount' => $shipping_amount,
            'discount_amount' => $discount_amount,
            'sub_total' => $sub_total,
            'shipping_address' => $customerAddress->toArray(),
            'total_amount' => $sub_total + $shipping_amount - $discount_amount,

        ];
        $order = static::make($attributes);

        return [$order, $orderItems];
    }

    /**
     * Cancel the order and update related reservations.
     *
     * @return void
     */
    public function cancel(\DateTimeInterface $cancelledAt): void
    {
        $this->status = OrderStatus::CANCELLED;
        $this->cancelled_at = $cancelledAt;
    }
    public function confirm(\DateTimeInterface $confirmedAt): void
    {
        $this->status = OrderStatus::CONFIRMED;
        $this->confirmed_at = $confirmedAt;
    }
    public function shipping(\DateTimeInterface $shipping_at): void
    {
        $this->status = OrderStatus::SHIPPED;
        $this->shipping_at = $shipping_at;
    }
    public function deliver(\DateTimeInterface $delivered_at): void
    {
        $this->status = OrderStatus::SHIPPED;
        $this->delivered_at = $delivered_at;
    }
}
