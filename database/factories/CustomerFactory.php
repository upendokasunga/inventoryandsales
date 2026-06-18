<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'code' => 'CUS-' . fake()->unique()->numerify('######'),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'country' => 'Tanzania',
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->email(),
            'customer_group_id' => CustomerGroup::factory(),
            'credit_limit' => fake()->randomFloat(2, 0, 10000000),
            'available_credit' => 0,
            'outstanding_balance' => 0,
            'payment_terms' => fake()->randomElement(['Net 30', 'Net 60', 'COD']),
            'credit_status' => 'good',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => ['is_active' => false]);
    }

    public function onHold(): static
    {
        return $this->state(fn(array $attrs) => [
            'credit_status' => 'suspended',
            'credit_hold_at' => now(),
            'credit_hold_reason' => 'Test hold reason',
        ]);
    }
}
