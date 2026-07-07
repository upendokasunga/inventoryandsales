<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerAdvanceFactory extends Factory
{
    protected $model = CustomerAdvance::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => Customer::factory(),
            'amount' => 100000,
            'balance' => 100000,
            'payment_method' => 'cash',
            'advance_date' => now(),
            'status' => 'completed',
            'created_by' => User::factory(),
        ];
    }
}
