<?php

namespace Database\Factories;

use App\Enums\QuotationTemplate;
use App\Models\Studio;
use App\Models\TermTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TermTemplate>
 */
class TermTemplateFactory extends Factory
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
            'template' => QuotationTemplate::Wedding,
            'title' => fake()->words(2, true),
            'body' => fake()->sentence(12),
        ];
    }
}
