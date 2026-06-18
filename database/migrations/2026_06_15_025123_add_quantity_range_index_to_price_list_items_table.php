<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->index(['min_quantity', 'max_quantity'], 'pli_qty_range_index');
        });
    }

    public function down(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->dropIndex('pli_qty_range_index');
        });
    }
};
