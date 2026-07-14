<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'payment_account_id')) {
                $table->foreignId('payment_account_id')->nullable()->after('payment_type')
                    ->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales_invoices', 'currency_code')) {
                $table->string('currency_code', 10)->default('TZS')->after('customer_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 8)->default(1)->after('currency_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $columns = ['payment_account_id', 'currency_code', 'exchange_rate'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('sales_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
