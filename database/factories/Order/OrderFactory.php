<?php

namespace Database\Factories\Order;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer\Customer::factory(),
            'order_number' => $this->faker->unique()->numerify('ORD###'),
            'sub_total' => $this->faker->randomFloat(2, 10, 500),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 50),
            'discount_amount' => $this->faker->randomFloat(2, 0, 30),
            'total_amount' => function (array $attributes) {
                return 
                    ($attributes['sub_total'] ?? 0) + 
                    ($attributes['shipping_amount'] ?? 0) - 
                    ($attributes['discount_amount'] ?? 0);
            },
            'currency' => 'USD',
            'shipping_address' => [
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'postal_code' => $this->faker->postcode,
                'country' => $this->faker->country,
            ],
            
            
       
        ];
    }
}
