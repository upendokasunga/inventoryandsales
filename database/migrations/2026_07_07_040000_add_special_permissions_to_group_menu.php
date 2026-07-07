<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_menu', function (Blueprint $table) {
            $table->boolean('can_print')->default(false)->after('can_2fa');
            $table->boolean('can_export')->default(false)->after('can_print');
            $table->boolean('can_import')->default(false)->after('can_export');
            $table->boolean('can_reverse')->default(false)->after('can_import');
            $table->boolean('can_cancel')->default(false)->after('can_reverse');
        });
    }

    public function down(): void
    {
        Schema::table('group_menu', function (Blueprint $table) {
            $table->dropColumn(['can_print', 'can_export', 'can_import', 'can_reverse', 'can_cancel']);
        });
    }
};
