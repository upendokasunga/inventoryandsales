<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'default_credit_limit' => fake()->randomFloat(2, 0, 100000),
            'default_payment_terms' => fake()->randomElement(['Net 30', 'Net 60', 'COD']),
            'is_active' => true,
        ];
    }
}
