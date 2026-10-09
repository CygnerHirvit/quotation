<?php

namespace Database\Factories;

use App\Enums\FunctionSide;
use App\Enums\FunctionType;
use App\Enums\GstMode;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
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
            'token' => Str::random(40),
            'number' => 'TST-'.now()->year.'-'.fake()->unique()->numerify('###'),
            'function_type' => FunctionType::Wedding,
            'function_side' => FunctionSide::BothSides,
            'bride' => fake()->firstNameFemale(),
            'groom' => fake()->firstNameMale(),
            'city' => fake()->city(),
            'dates_tbd' => true,
            'discount_percent' => 0,
            'gst_percent' => 18,
            'gst_mode' => GstMode::Included,
            'show_breakdown' => false,
            'status' => QuotationStatus::Draft,
            'valid_until' => now()->addDays(15),
        ];
    }

    public function dated(string $start, ?string $end = null): static
    {
        return $this->state(fn (array $attributes) => [
            'dates_tbd' => false,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }
}
