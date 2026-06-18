<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => 'SKU-' . strtoupper(Str::random(8)),
            'barcode' => fake()->unique()->numerify('2#############'),
            'barcode_image' => null,
            'description' => fake()->sentence(),
            'tax_rate' => fake()->randomElement([0, 16, 18]),
            'tax_inclusive' => true,
            'is_active' => true,
            'track_stock' => true,
            'reorder_level' => fake()->randomFloat(3, 0, 100),
            'image' => null,
            'weight' => fake()->randomFloat(3, 0.1, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
