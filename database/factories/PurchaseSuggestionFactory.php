<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseSuggestion;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseSuggestionFactory extends Factory
{
    protected $model = PurchaseSuggestion::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'supplier_id' => Supplier::factory(),
            'suggested_quantity' => fake()->randomFloat(2, 10, 1000),
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
