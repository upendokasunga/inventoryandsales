<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnItemFactory extends Factory
{
    protected $model = SalesReturnItem::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'sales_return_id' => SalesReturn::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'unit_price' => $this->faker->randomFloat(2, 1000, 50000),
            'line_total' => 0,
            'reason' => $this->faker->randomElement(['damaged', 'wrong_item', 'expired']),
        ];
    }
}
