<?php

namespace Database\Factories;

use App\Models\InventoryBalance;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBalanceFactory extends Factory
{
    protected $model = InventoryBalance::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(3, 10, 1000);
        $avgCost = fake()->randomFloat(2, 10, 500);

        return [
            'product_id' => Product::factory(),
            'quantity_on_hand' => $qty,
            'quantity_reserved' => 0,
            'quantity_available' => $qty,
            'quantity_incoming' => 0,
            'average_cost' => $avgCost,
            'total_value' => $qty * $avgCost,
        ];
    }
}
