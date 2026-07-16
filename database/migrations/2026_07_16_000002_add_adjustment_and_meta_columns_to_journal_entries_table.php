<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'project_id')) {
                $table->bigInteger('project_id')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('journal_entries', 'branch_id')) {
                $table->bigInteger('branch_id')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('journal_entries', 'is_adjustment')) {
                $table->boolean('is_adjustment')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('journal_entries', 'project_id')) {
                $table->dropColumn('project_id');
            }
            if (Schema::hasColumn('journal_entries', 'is_adjustment')) {
                $table->dropColumn('is_adjustment');
            }
        });
    }
};
