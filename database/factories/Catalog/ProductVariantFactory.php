<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Color;
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

            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'sku' => strtoupper($this->faker->bothify('SKU-####??')),
            'color_id' => Color::factory()->create(),
            'product_size' => $this->faker->randomElement(\App\Enums\ProductSize::cases()),
        ];
    }
}
