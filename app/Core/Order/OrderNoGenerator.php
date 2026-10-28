<?php

namespace App\Core\Order;

class OrderNoGenerator
{
   
    public function generate(): string
    {
        $datePart = now()->format('Ymd');
        $randomPart = random_int(1000, 9999);
        return 'ORD' . $datePart . $randomPart;
    }
}