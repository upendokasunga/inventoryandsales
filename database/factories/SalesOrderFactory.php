<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 50000);
        $tax = round($subtotal * 0.18, 2);
        $total = $subtotal + $tax;

        return [
            'customer_id' => Customer::factory(),
            'order_date' => fake()->date(),
            'delivery_date' => fake()->optional()->dateTimeBetween('+1 week', '+1 month'),
            'status' => 'pending_approval',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'discount_type' => 'fixed',
            'total' => $total,
            'payment_terms' => 'Net 30',
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
