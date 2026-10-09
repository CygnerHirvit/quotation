<?php

namespace Database\Factories;

use App\Enums\PriceCategory;
use App\Models\PriceItem;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceItem>
 */
class PriceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'studio_id' => Studio::factory(),
            'category' => fake()->randomElement(PriceCategory::cases()),
            'name' => fake()->words(3, true),
            'rate' => fake()->numberBetween(10, 300) * 100,
            'is_active' => true,
        ];
    }
}
