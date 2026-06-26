<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierPriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierPriceHistoryFactory extends Factory
{
    protected $model = SupplierPriceHistory::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'product_id' => Product::factory(),
            'unit_price' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'TZS',
            'effective_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
