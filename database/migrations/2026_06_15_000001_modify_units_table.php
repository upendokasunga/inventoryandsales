<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('short_code', 10)->nullable()->unique();
            $table->boolean('is_base')->default(false);
            $table->softDeletes();
        });

        DB::statement('UPDATE units SET short_code = abbreviation');

        Schema::table('units', function (Blueprint $table) {
            $table->string('short_code', 10)->nullable(false)->change();
            $table->dropColumn('abbreviation');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('abbreviation', 20)->nullable();
        });

        DB::statement('UPDATE units SET abbreviation = short_code');

        Schema::table('units', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('is_base');
            $table->dropColumn('short_code');
        });
    }
};
