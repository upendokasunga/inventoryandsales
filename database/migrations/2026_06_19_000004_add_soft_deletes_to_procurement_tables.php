<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('supplier_price_history', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('supplier_price_history', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
