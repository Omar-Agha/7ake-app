<?php

namespace App\Models\Order;

enum PaymentMethod: int
{
    case COD = 0;
    case ONLINE = 1;
    case REMITTANCE = 2;

    
}