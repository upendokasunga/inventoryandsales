<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentItemFactory extends Factory
{
    protected $model = StockAdjustmentItem::class;

    public function definition(): array
    {
        $expected = fake()->randomFloat(3, 0, 100);
        $actual = fake()->randomFloat(3, 0, 100);

        return [
            'stock_adjustment_id' => StockAdjustment::factory(),
            'product_id' => Product::factory(),
            'expected_quantity' => $expected,
            'actual_quantity' => $actual,
            'difference' => $actual - $expected,
        ];
    }
}
