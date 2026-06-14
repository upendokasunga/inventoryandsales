<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone1' => fake()->phoneNumber(),
            'phone2' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'tax_id' => fake()->numerify('TIN-########'),
            'payment_terms' => fake()->randomElement(['Net 30', 'Net 60', 'Net 90', 'COD']),
            'notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
