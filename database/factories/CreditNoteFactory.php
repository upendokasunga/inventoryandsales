<?php

namespace Database\Factories;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditNoteFactory extends Factory
{
    protected $model = CreditNote::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'credit_note_number' => 'CN-' . now()->year . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 500000),
            'status' => 'issued',
            'issued_date' => now(),
            'created_by' => User::factory(),
        ];
    }
}
