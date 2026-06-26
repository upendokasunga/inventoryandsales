<?php

namespace Database\Factories;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBatchFactory extends Factory
{
    protected $model = InventoryBatch::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(3, 10, 500);

        return [
            'product_id' => Product::factory(),
            'batch_number' => 'BATCH-' . strtoupper(fake()->bothify('??####')),
            'quantity' => $qty,
            'quantity_remaining' => $qty,
            'unit_cost' => fake()->randomFloat(2, 10, 500),
            'expiry_date' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }
}
