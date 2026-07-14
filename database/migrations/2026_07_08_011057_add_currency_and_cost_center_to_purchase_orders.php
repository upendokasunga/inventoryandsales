<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'currency_code')) {
                $table->string('currency_code', 10)->default('TZS')->after('supplier_id');
            }
            if (!Schema::hasColumn('purchase_orders', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 8)->default(1)->after('currency_code');
            }
            if (!Schema::hasColumn('purchase_orders', 'cost_center_id')) {
                $table->foreignId('cost_center_id')->nullable()->after('notes')
                    ->constrained('cost_centers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
            if (Schema::hasColumn('purchase_orders', 'exchange_rate')) {
                $table->dropColumn('exchange_rate');
            }
            if (Schema::hasColumn('purchase_orders', 'cost_center_id')) {
                $table->dropForeign(['cost_center_id']);
                $table->dropColumn('cost_center_id');
            }
        });
    }
};
