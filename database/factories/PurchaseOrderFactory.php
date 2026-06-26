<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 50000);
        $tax = round($subtotal * 0.18, 2);
        $total = $subtotal + $tax;

        return [
            'supplier_id' => Supplier::factory(),
            'order_date' => fake()->date(),
            'expected_date' => fake()->optional()->dateTimeBetween('+1 week', '+1 month'),
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
