<?php

namespace App\Models\Order;

enum OrderPaymentStatus: int
{
    case PAID = 0;
    case UNPAID = 1;
    
}