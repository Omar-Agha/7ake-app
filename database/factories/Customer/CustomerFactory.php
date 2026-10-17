<?php

namespace Database\Factories\Customer;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'f_name' => $this->faker->firstName(),
            'l_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            // Avoid writing to the filesystem in factories (especially for avatars).
            // Instead, generate a fake file path or use a placeholder.
            'avatar' => 'avatars/' . $this->faker->uuid . '.jpg',
 
            'user_id' => \App\Models\User::factory(),
       
        ];

    }
}
