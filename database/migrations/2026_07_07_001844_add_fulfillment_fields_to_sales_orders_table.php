<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('picked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('picked_at')->nullable();
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('packed_at')->nullable();
            $table->foreignId('invoiced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invoiced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['picked_by']);
            $table->dropForeign(['packed_by']);
            $table->dropForeign(['invoiced_by']);
            $table->dropColumn(['picked_by', 'picked_at', 'packed_by', 'packed_at', 'invoiced_by', 'invoiced_at']);
        });
    }
};
