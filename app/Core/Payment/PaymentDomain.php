<?php

namespace App\Core\Payment;

use App\Events\PaymentWasPaid;
use App\Models\Order\PaymentMethod;
use App\Models\Payment\Payment;
use App\Models\Payment\PaymentStatus;
use Carbon\Carbon;

class PaymentDomain
{
    /**
     * @param PaymentNoGenerator $paymentNoGenerator
     */
    public function __construct(
        protected PaymentNoGenerator $paymentNoGenerator
    ) {
        //
    }
    /**
     * Create a payment entry.
     * @return Payment
     */
    public function createPayment(
        float $amount,
        string $currency,
        int $order_id,
        PaymentMethod $paymentMethod,
        ?string $reference = null,

    ): Payment {

        $payment_no = $this->paymentNoGenerator->generate($order_id);
        $payment = Payment::createPendingPayment($payment_no, $amount, $currency, $order_id, $paymentMethod, $reference, null);
        $payment->save();
        return $payment;
    }


    public function payPayment(
        int $payment_id,
    ): Payment {
        return \DB::transaction(function () use ($payment_id) {

            $payment = Payment::lockForUpdate()->whereKey($payment_id)->first();

            if ($payment->status === PaymentStatus::PAID) {

                return $payment;
            }
            if ($payment->status !== PaymentStatus::PENDING) {

                throw new \DomainException('Only pending payments can be paid.');
            }

            $payment->pay(Carbon::now());
            $payment->save();
            event(new PaymentWasPaid($payment));
            return $payment;
        });
    }

    /**
     * Cancel a payment.
     *
     * @param Payment $payment
     * @param string|null $reason
     * @return Payment
     */
    public function cancelPayment(int $payment_id, string $reason = ""): Payment
    {
        // Perform cancellation in a database transaction to guarantee atomicity
        return \DB::transaction(function () use ($payment_id, $reason) {
            $payment = Payment::lockForUpdate()->whereKey($payment_id)->first();

            if ($payment->status === PaymentStatus::CANCELLED) {
                return $payment;
            }
            if ($payment->status !== PaymentStatus::PENDING) {

                throw new \DomainException('Only pending payments can be cancelled.');
            }
            $payment->cancel(Carbon::now(), $reason);
            $payment->save();

            // Optionally, you can fire an event here if needed
            return $payment;
        });
    }

    /**
     * Mark a payment as failed.
     *
     * @param Payment $payment
     * @param string|null $reason
     * @return Payment
     */
    public function failPayment(int $payment_id, ?string $reason = null): Payment
    {
        return \DB::transaction(function () use ($payment_id, $reason) {

            $payment = Payment::lockForUpdate()->whereKey($payment_id)->first();
            if ($payment->status === PaymentStatus::FAILED) {
                return $payment;
            }
            if ($payment->status !== PaymentStatus::PENDING) {

                throw new \DomainException('Only pending payments can be cancelled.');
            }
            $payment->fail(Carbon::now(), $reason);
            $payment->save();
            return $payment;
        });
    }
}
