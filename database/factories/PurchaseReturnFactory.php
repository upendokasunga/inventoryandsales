<?php

namespace Database\Factories;

use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnFactory extends Factory
{
    protected $model = PurchaseReturn::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'return_number' => 'PR-' . now()->year . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'supplier_id' => Supplier::factory(),
            'total_amount' => $this->faker->randomFloat(2, 1000, 500000),
            'status' => 'pending_approval',
            'created_by' => User::factory(),
        ];
    }
}
