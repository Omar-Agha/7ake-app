<?php

namespace Database\Factories\Customer;

use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
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
            'address_name' => $this->faker->word(),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'address_description' => $this->faker->address(),
            'is_default' => $this->faker->boolean(10), // about 10% as default
        ];
    }
}
