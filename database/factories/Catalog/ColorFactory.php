<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Color>
 */
class ColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName(),
            'code' => $this->faker->unique()->word(),
            'hex_code' => $this->faker->hexColor(),
        ];
    }
}
