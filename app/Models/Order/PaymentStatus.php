<?php

namespace App\Models\Order;

enum PaymentStatus: int
{
    case PAID = 0;
    case UNPAID = 1;
    
}