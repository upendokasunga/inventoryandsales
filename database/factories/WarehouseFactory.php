<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Warehouse',
            'code' => strtoupper(fake()->unique()->bothify('WH-###')),
            'type' => 'goods',
            'branch_id' => Branch::factory(),
            'is_active' => true,
        ];
    }
}
