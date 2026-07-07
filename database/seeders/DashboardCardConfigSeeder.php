<?php

namespace Database\Seeders;

use App\Services\DashboardCardService;
use Illuminate\Database\Seeder;

class DashboardCardConfigSeeder extends Seeder
{
    public function run(): void
    {
        DashboardCardService::seedDefaults();
    }
}
