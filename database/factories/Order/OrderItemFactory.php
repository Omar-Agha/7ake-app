<?php

namespace Database\Factories\Order;

use App\Models\AppModelsOrderOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppModelsOrderOrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        
        'product_variant_id' => \App\Models\Catalog\ProductVariant::factory(),
   
        'product_name'       => $this->faker->words(3, true),
        'quantity'           => $this->faker->numberBetween(1, 10),
        'price'              => $this->faker->randomFloat(2, 10, 500),
        'total'              => fn (array $attributes) =>
            ($attributes['price'] ?? 0) * ($attributes['quantity'] ?? 1),
        'sku'                => $this->faker->bothify('SKU-#####'),
        'currency'           => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
   
        ];
    }
}
