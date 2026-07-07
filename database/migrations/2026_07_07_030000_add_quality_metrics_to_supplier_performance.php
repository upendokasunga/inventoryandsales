<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_performance', function (Blueprint $table) {
            $table->integer('total_items_received')->default(0)->after('total_purchase_value');
            $table->integer('damaged_items')->default(0)->after('total_items_received');
            $table->integer('returned_items')->default(0)->after('damaged_items');
            $table->decimal('quality_rate', 5, 2)->default(0)->after('order_accuracy_rate');
            $table->decimal('return_rate', 5, 2)->default(0)->after('quality_rate');
            $table->decimal('damage_rate', 5, 2)->default(0)->after('return_rate');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_performance', function (Blueprint $table) {
            $table->dropColumn(['total_items_received', 'damaged_items', 'returned_items', 'quality_rate', 'return_rate', 'damage_rate']);
        });
    }
};
