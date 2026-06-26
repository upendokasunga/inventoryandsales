<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->default(0)->after('tax');
            $table->string('discount_type', 10)->default('fixed')->after('discount');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('receipt_number', 20)->unique()->nullable()->after('uuid');
            $table->index('receipt_number');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['discount', 'discount_type']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropIndex(['receipt_number']);
            $table->dropColumn('receipt_number');
        });
    }
};
