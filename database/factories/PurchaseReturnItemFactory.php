<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnItemFactory extends Factory
{
    protected $model = PurchaseReturnItem::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'purchase_return_id' => PurchaseReturn::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'unit_price' => $this->faker->randomFloat(2, 1000, 50000),
            'line_total' => 0,
            'reason' => $this->faker->randomElement(['damaged', 'wrong_item', 'expired']),
        ];
    }
}
