<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entry_lines', 'balance_sheet_item_id')) {
                $table->bigInteger('balance_sheet_item_id')->nullable()->after('credit');
            }
            if (!Schema::hasColumn('journal_entry_lines', 'project_id')) {
                $table->bigInteger('project_id')->nullable()->after('balance_sheet_item_id');
            }
            if (!Schema::hasColumn('journal_entry_lines', 'branch_id')) {
                $table->bigInteger('branch_id')->nullable()->after('project_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropColumn(['balance_sheet_item_id', 'project_id', 'branch_id']);
        });
    }
};
