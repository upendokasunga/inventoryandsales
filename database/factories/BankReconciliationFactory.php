<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankReconciliationFactory extends Factory
{
    protected $model = BankReconciliation::class;

    public function definition(): array
    {
        return [
            'bank_account_id' => BankAccount::factory(),
            'start_date' => now()->subMonth(),
            'end_date' => now(),
            'opening_balance' => 500000,
            'closing_balance' => 750000,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}
