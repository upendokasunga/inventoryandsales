<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mobile_money']),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'payment_date' => now(),
            'received_by' => User::factory(),
        ];
    }
}
