<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_code' => 'pc', 'is_base' => true],
            ['name' => 'Bottle', 'short_code' => 'btl', 'is_base' => false],
            ['name' => 'Carton', 'short_code' => 'ctn', 'is_base' => false],
            ['name' => 'Pallet', 'short_code' => 'plt', 'is_base' => false],
            ['name' => 'Box', 'short_code' => 'box', 'is_base' => false],
            ['name' => 'Kilogram', 'short_code' => 'kg', 'is_base' => false],
            ['name' => 'Liter', 'short_code' => 'L', 'is_base' => false],
            ['name' => 'Meter', 'short_code' => 'm', 'is_base' => false],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }
    }
}
