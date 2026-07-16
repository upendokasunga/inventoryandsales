<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('website');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('bank_swift_code')->nullable()->after('bank_branch');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('notes');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('bank_swift_code')->nullable()->after('bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_branch', 'bank_swift_code']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_branch', 'bank_swift_code']);
        });
    }
};
