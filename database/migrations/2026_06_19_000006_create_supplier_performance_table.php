<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_performance', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->integer('total_orders')->default(0);
            $table->integer('on_time_orders')->default(0);
            $table->integer('late_orders')->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(0);
            $table->decimal('avg_lead_time_days', 8, 2)->default(0);
            $table->decimal('total_purchase_value', 15, 2)->default(0);
            $table->decimal('order_accuracy_rate', 5, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('supplier_id');
            $table->index('on_time_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_performance');
    }
};
