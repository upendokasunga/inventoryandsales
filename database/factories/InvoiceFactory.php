<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'invoice_number' => 'INV-' . now()->year . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'invoice_date' => now(),
            'payment_type' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 0,
            'discount' => 0,
            'discount_type' => 'fixed',
            'tax' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'status' => 'pending_approval',
            'created_by' => User::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn(array $attrs) => [
            'payment_status' => 'paid',
            'amount_paid' => $attrs['total'] ?? 0,
            'balance_due' => 0,
        ]);
    }

    public function partial(): static
    {
        $total = 100000;
        return $this->state(fn() => [
            'payment_status' => 'partial',
            'total' => $total,
            'amount_paid' => $total / 2,
            'balance_due' => $total / 2,
        ]);
    }
}
