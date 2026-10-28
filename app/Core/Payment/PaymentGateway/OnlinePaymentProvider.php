<?php

namespace App\Core\Payment\PaymentGateway;

interface OnlinePaymentProvider
{
    public function initiatePayment(array $data);
    public function getPaymentStatus(string $reference): string; //reconciliation
    public function parseWebhook(array $headers,string $rawBody): PaymentProviderEvent;
    
    
    
}
