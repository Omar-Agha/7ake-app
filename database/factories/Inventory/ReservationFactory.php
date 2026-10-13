<?php

namespace Database\Factories\Inventory;

use App\Enums\ReservationStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Reservation;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'reserved_at' => $this->faker->dateTime(),
            'expires_at' => $this->faker->dateTime(),
            'status' => ReservationStatus::Active,
        ];
    }
}
