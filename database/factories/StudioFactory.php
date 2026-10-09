<?php

namespace Database\Factories;

use App\Enums\StudioStatus;
use App\Enums\StudioTheme;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Studio>
 */
class StudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Studio';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'tagline' => fake()->sentence(4),
            'email' => fake()->companyEmail(),
            'phone' => '+91 '.fake()->numerify('98########'),
            'city' => fake()->city(),
            'theme' => StudioTheme::TealMarigold,
            'quote_prefix' => strtoupper(Str::random(3)),
            'gst_percent' => 18,
            'validity_days' => 15,
            'status' => StudioStatus::Active,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudioStatus::Blocked,
        ]);
    }
}
