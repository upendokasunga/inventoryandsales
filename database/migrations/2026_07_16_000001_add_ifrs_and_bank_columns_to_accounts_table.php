<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('account_number', 100)->nullable()->after('code');
            $table->string('ifrs_category', 50)->nullable()->after('category');
            $table->enum('current_noncurrent', ['current', 'non_current'])->nullable()->after('ifrs_category');
            $table->integer('presentation_order')->nullable()->after('current_noncurrent');
            $table->enum('function_of_expense', ['cogs', 'selling', 'admin'])->nullable()->after('presentation_order');
            $table->boolean('reportable')->default(true)->after('function_of_expense');
            $table->foreignId('user_id')->nullable()->after('reportable')->constrained('users')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->after('user_id')->constrained('cost_centers')->nullOnDelete();
            $table->bigInteger('branch_id')->nullable()->after('cost_center_id');
            $table->string('currency_code', 10)->nullable()->after('branch_id');
            $table->boolean('allow_overdraft')->default(false)->after('currency_code');
            $table->decimal('overdraft_limit', 18, 2)->default(0)->after('allow_overdraft');
            $table->string('bank_name')->nullable()->after('overdraft_limit');
            $table->string('bank_swift_code', 50)->nullable()->after('bank_name');
            $table->string('bank_branch', 100)->nullable()->after('bank_swift_code');
            $table->boolean('include_in_income_statement')->default(false)->after('bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'account_number', 'ifrs_category', 'current_noncurrent',
                'presentation_order', 'function_of_expense', 'reportable',
                'user_id', 'cost_center_id', 'branch_id', 'currency_code',
                'allow_overdraft', 'overdraft_limit', 'bank_name',
                'bank_swift_code', 'bank_branch', 'include_in_income_statement',
            ]);
        });
    }
};
