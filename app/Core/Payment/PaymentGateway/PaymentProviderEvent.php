<?php

namespace App\Core\Payment\PaymentGateway;
enum PaymentProviderEvent
{
    case PaymentInitiated;
    case PaymentSuccess;
    case PaymentFailed;
    case PaymentPending;
    case PaymentCancelled;
    case PaymentRefunded;
}