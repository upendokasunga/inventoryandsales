<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_price_history', function (Blueprint $table) {
            $table->decimal('previous_price', 15, 2)->nullable()->after('unit_price');
            $table->decimal('price_change', 15, 2)->nullable()->after('previous_price');
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_price_history', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn(['previous_price', 'price_change', 'product_unit_id']);
        });
    }
};
