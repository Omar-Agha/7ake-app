<?php

namespace App\Models\Order;

use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use App\Models\Inventory\Reservation;
use App\Models\Payment\Payment;
use App\Models\Payment\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Ramsey\Collection\Collection;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $customer_id
 * @property string $order_number
 * @property float $sub_total
 * @property float $shipping_amount
 * @property float $discount_amount
 * @property float $total_amount
 * @property string $currency
 * @property array<array-key, mixed> $shipping_address
 * @property \App\Models\Order\OrderStatus $status
 * @property \App\Models\Order\PaymentMethod|null $payment_method
 * @property \App\Models\Order\OrderPaymentStatus $payment_status
 * @property string|null $cancelled_at
 * @property string|null $confirmed_at
 * @property string|null $shipping_at
 * @property string|null $delivered_at
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Reservation> $reservations
 * @property-read int|null $reservations_count
 * @method static \Database\Factories\Order\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'payment_status' => OrderPaymentStatus::class,
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

   
    public function reservations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Reservation::class,
            OrderItem::class,
            'order_id',        // Foreign key on OrderItem (intermediate)...
            'order_item_id',   // Foreign key on Reservation (related)...
            'id',              // Local key on Order (parent)...
            'id'               // Local key on OrderItem (intermediate)...
        )->select('reservations.*'); // Avoid ambiguous "id" columns
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
            'payment_status' => OrderPaymentStatus::UNPAID,
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
    public function pay(){
        $this->payment_status = OrderPaymentStatus::PAID;
        $this->paid_at = Carbon::now();
    }
}
