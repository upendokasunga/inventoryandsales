<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('product_units')->nullOnDelete();
        });

        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('product_units')->nullOnDelete();
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('product_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->foreign('product_unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }
};
