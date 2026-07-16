<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_advances', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_advances', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('payment_method')->constrained('accounts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_advances', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
