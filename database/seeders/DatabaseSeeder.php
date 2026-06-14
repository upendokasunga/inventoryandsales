<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SystemMenusSeeder::class,
            SuperAdminGroupSeeder::class,
            UnitsSeeder::class,
        ]);
    }
}
