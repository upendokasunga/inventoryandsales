<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('supplier:recalculate-performance', function () {
    $this->info('Recalculating supplier performance...');

    $service = app(\App\Services\SupplierAnalyticsService::class);
    $service->recalculatePerformance();

    $this->info('Supplier performance recalculated successfully.');
})->purpose('Recalculate supplier performance metrics including quality data');
