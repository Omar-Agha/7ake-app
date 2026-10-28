<?php

namespace App\Models\Payment;

enum PaymentStatus: int
{
    case PENDING = 0;
    case PAID = 1;
    case FAILED = 2;
    case CANCELLED = 3;
}