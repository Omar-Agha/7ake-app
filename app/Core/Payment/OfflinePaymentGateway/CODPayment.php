<?php

namespace App\Core\Payment\OfflinePaymentGateway;

use App\Core\Order\OrderDomain;
use App\Core\Payment\PaymentDomain;
use App\Models\Order\Order;
use App\Models\Order\PaymentMethod;
use App\Models\Payment\Payment;

class CODPayment
{
    public function __construct(protected PaymentDomain $paymentDomain, protected OrderDomain $order_domain) {}
    public function initiate(float $amount, string $currency,int $order_id): Payment
    {
        return $this->paymentDomain->createPayment($amount,$currency,$order_id,PaymentMethod::COD);
    }
    public function cancel(int $payment_id,string $reason ="") {
        return $this->paymentDomain->cancelPayment($payment_id,$reason);

    }
    public function confirmCollection(Order $order,int $payment_id) {
        $payment =  $this->paymentDomain->payPayment($payment_id);
        $this->order_domain->markAsPaid($order);
        return $payment;

    }

}