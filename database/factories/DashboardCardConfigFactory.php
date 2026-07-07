<?php

namespace Database\Factories;

use App\Models\DashboardCardConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class DashboardCardConfigFactory extends Factory
{
    protected $model = DashboardCardConfig::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'title' => fake()->words(3, true),
            'icon' => 'chart-bar',
            'color' => 'primary',
            'section' => 'kpi',
            'sort_order' => fake()->numberBetween(0, 20),
            'is_enabled' => true,
        ];
    }
}
