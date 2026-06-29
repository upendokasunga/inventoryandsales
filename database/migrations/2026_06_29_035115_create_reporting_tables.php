<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // sales, inventory, customer, supplier, tax, payment, kpi
            $table->string('frequency'); // daily, weekly, monthly, quarterly, annual
            $table->json('filters')->nullable();
            $table->json('recipients'); // email addresses
            $table->json('format'); // pdf, excel, csv
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('period'); // daily, weekly, monthly, quarterly, annual
            $table->date('snapshot_date');
            $table->json('metrics');
            $table->timestamp('generated_at')->useCurrent();
            $table->index(['period', 'snapshot_date']);
        });

        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // report_generated, export, scheduled, analytics_refreshed, dashboard_viewed, kpi_snapshot
            $table->string('report_name')->nullable();
            $table->string('format')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_logs');
        Schema::dropIfExists('kpi_snapshots');
        Schema::dropIfExists('scheduled_reports');
    }
};
