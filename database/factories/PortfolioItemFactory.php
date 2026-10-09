<?php

namespace Database\Factories;

use App\Enums\PortfolioType;
use App\Models\PortfolioItem;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
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
            'type' => PortfolioType::Album,
            'title' => fake()->words(2, true),
            'url' => fake()->url(),
            'is_active' => true,
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PortfolioType::Video,
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }
}
