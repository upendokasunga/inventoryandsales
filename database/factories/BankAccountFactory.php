<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->company() . ' Account',
            'account_number' => fake()->bankAccountNumber(),
            'bank_name' => fake()->company(),
            'account_type' => 'checking',
            'opening_balance' => 0,
            'current_balance' => 500000,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
