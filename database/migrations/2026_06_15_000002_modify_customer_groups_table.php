<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->decimal('default_credit_limit', 15, 2)->default(0)->change();
            $table->softDeletes();
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['is_active']);
            $table->decimal('default_credit_limit', 12, 2)->default(0)->change();
        });
    }
};
