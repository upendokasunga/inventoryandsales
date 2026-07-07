<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->foreignId('sub_product_id')->nullable()->constrained('products')->nullOnDelete()->after('product_id');
            $table->foreignId('store_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('sub_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_product_id');
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
