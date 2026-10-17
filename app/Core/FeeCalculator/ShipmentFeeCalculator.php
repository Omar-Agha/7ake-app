<?php

namespace App\Core\FeeCalculator;

use App\Models\Customer\CustomerAddress;
use Illuminate\Support\Collection;

class ShipmentFeeCalculator
{
    
    public function calculate(CustomerAddress $address, Collection $orderItems): int
    {
        return 0;
    }
}