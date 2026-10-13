<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 100),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
