<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MainStoreSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Store',
                'type' => 'goods',
                'location' => 'Head Office',
                'is_active' => true,
            ]
        );
    }
}
