<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'is_printing_process')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_printing_process');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'is_printing_process')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_printing_process')->default(false)->after('cost_center');
            });
        }
    }
};
