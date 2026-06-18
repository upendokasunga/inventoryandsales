<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    public function definition(): array
    {
        static $factor = 1;

        return [
            'product_id' => Product::factory(),
            'unit_id' => Unit::factory(),
            'conversion_factor' => $factor++,
            'purchase_price' => fake()->randomFloat(2, 1, 100),
            'selling_price' => fake()->randomFloat(2, 1, 200),
            'wholesale_price' => fake()->randomFloat(2, 1, 150),
            'bulk_price' => fake()->randomFloat(2, 1, 120),
            'is_default_sale' => false,
            'is_default_purchase' => false,
            'barcode' => fake()->unique()->numerify('3#############'),
        ];
    }

    public function defaultSale(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default_sale' => true,
        ]);
    }

    public function defaultPurchase(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default_purchase' => true,
        ]);
    }
}
