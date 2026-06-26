<?php

namespace Database\Factories;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockReservation;
use App\Models\StockReservationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockReservationItemFactory extends Factory
{
    protected $model = StockReservationItem::class;

    public function definition(): array
    {
        return [
            'stock_reservation_id' => StockReservation::factory(),
            'product_id' => Product::factory(),
            'inventory_batch_id' => InventoryBatch::factory(),
            'quantity' => fake()->randomFloat(3, 1, 100),
        ];
    }
}
