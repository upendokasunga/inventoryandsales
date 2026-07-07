<?php

namespace Database\Factories;

use App\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceListFactory extends Factory
{
    protected $model = PriceList::class;

    public function definition(): array
    {
        return [
            "name" => fake()->unique()->words(3, true),
            "description" => fake()->sentence(),
            "customer_group_id" => null,

            "is_active" => true,
            "valid_from" => null,
            "valid_until" => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            "is_active" => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            "valid_from" => now()->subDays(30),
            "valid_until" => now()->subDays(1),
        ]);
    }
}