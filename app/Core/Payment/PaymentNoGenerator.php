<?php

namespace App\Core\Payment;

class PaymentNoGenerator
{
    
    /**
     * Generate a payment number based on the order number and current payment count for that order.
     *
     * Format: PAY-{ORDERNO}-{N}
     *
     * @param int $order_id
     * @return string
     */
    public function generate(int $order_id): string
    {
        // Fetch the related order
        $order = \App\Models\Order\Order::findOrFail($order_id);

        // Get the order number
        $order_no = $order->order_number;

        // Count existing payments for this order
        $payment_count = $order->payments()->count();

        // The payment number is one higher (this will be for the "next" payment)
        $next_payment_number = $payment_count + 1;

        // Final format: PAY-{ORDERNO}-{N}
        return "PAY-{$order_no}-{$next_payment_number}";
    }
}