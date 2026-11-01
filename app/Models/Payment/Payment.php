<?php

namespace App\Models\Payment;

use App\Models\Order\Order;
use App\Models\Order\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property PaymentMethod $payment_method
 * @property \App\Models\Payment\PaymentStatus $status
 * @property-read Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable fields here, e.g.:
        'no',
        'amount',
        'currency',

        'payment_method',
        'status',
        'paid_at',
        'provider_reference',
        'provider',
        'order_id',
        'cancel_reason',
        'cancelled_at',
        'failed_at',
        'fail_reason'


    ];

    public function cancel(string $reason = ""): void
    {
        if ($this->status === PaymentStatus::CANCELLED) {
            return; 
        }
        if ($this->status !== PaymentStatus::PENDING) {

            throw new \DomainException('Only pending payments can be cancelled.');
        }
        $this->status = PaymentStatus::CANCELLED;
        $this->cancelled_at = Carbon::now();
        $this->cancel_reason = $reason;
    }

    public function pay(?string $reference = null): void
    {
        
        $this->status = PaymentStatus::PAID;
        $this->paid_at = Carbon::now();
        $this->provider_reference = $reference;
    }

    public function fail( string $reason = ""): void
    {
        
        $this->status = PaymentStatus::FAILED;
        $this->failed_at = Carbon::now();
        $this->fail_reason = $reason;
    }

    /**
     * Cast attributes to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',

        'paid_at' => 'datetime',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class
    ];



    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Create a new Payment instance with the provided attributes.
     *
     * @param float $amount
     * @param string $currency

     * @param PaymentMethod $payment_method
     * @param string|null $provider_reference
     * @param string|null $provider
     * @param int $order_id
     *
     * @return static
     */
    public static function createPendingPayment(
        string $no,
        float $amount,
        string $currency,

        int $order_id,
        PaymentMethod $payment_method,
        ?string $provider_reference,
        ?string $provider
    ): self {
        return new self([
            'no' => $no,
            'amount' => $amount,
            'currency' => $currency,

            'payment_method' => $payment_method,
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
            'provider_reference' => $provider_reference,
            'provider' => $provider,
            'order_id' => $order_id,
        ]);
    }
}
