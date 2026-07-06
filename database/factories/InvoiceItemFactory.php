<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'unit_price' => $this->faker->randomFloat(2, 1000, 50000),
            'discount' => 0,
            'tax' => 0,
            'line_total' => 0,
        ];
    }
}
