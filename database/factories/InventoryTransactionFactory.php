<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => 'purchase_receipt',
            'quantity' => fake()->randomFloat(3, 1, 100),
            'unit_cost' => fake()->randomFloat(2, 10, 500),
            'total_cost' => 0,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_by' => User::factory(),
        ];
    }
}
