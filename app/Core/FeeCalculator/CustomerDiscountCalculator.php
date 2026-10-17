<?php

namespace App\Core\FeeCalculator;

use App\Models\Customer\Customer;
use Illuminate\Support\Collection;

class CustomerDiscountCalculator
{
    public function calculate(Customer $customer, Collection $orderItems): int
    {
        return 0;
    }
}