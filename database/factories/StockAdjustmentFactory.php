<?php

namespace Database\Factories;

use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    public function definition(): array
    {
        return [
            'adjustment_number' => 'ADJ-' . now()->format('Ymd') . '-' . strtoupper(fake()->bothify('??##')),
            'type' => fake()->randomElement(['positive', 'negative']),
            'reason' => fake()->randomElement(['damaged', 'lost', 'found', 'recount']),
            'description' => fake()->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}
