<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptItemFactory extends Factory
{
    protected $model = GoodsReceiptItem::class;

    public function definition(): array
    {
        $expected = fake()->randomFloat(2, 10, 500);

        return [
            'goods_receipt_id' => GoodsReceipt::factory(),
            'product_id' => Product::factory(),
            'expected_quantity' => $expected,
            'received_quantity' => $expected,
            'condition' => 'good',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
