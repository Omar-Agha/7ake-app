<?php

namespace App\Models\Order;

enum OrderStatus: int
{
    case PENDING = 0;
    case CONFIRMED = 1;
    case SHIPPED = 2;
    case DELIVERED = 3;
    case CANCELLED = 4;
    case RETURNED = 5;
}