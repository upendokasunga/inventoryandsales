<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'status' => 'active',
            'reserved_at' => now(),
            'expires_at' => now()->addDays(3),
            'created_by' => User::factory(),
        ];
    }
}
