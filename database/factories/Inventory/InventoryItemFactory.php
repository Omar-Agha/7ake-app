<?php

namespace Database\Factories\Inventory;

use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'reserved_quantity' => $this->faker->numberBetween(0, 10),
        ];
    }
}
