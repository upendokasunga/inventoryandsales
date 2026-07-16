<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('bank_name')->constrained('banks')->nullOnDelete();
            $table->foreignId('account_type_id')->nullable()->after('account_type')->constrained('account_types')->nullOnDelete();
        });

        // Migrate existing bank_name strings to bank_id FK where possible
        $bankAccounts = DB::table('bank_accounts')->whereNotNull('bank_name')->get();
        foreach ($bankAccounts as $ba) {
            $bank = DB::table('banks')->where('name', $ba->bank_name)->first();
            if ($bank) {
                DB::table('bank_accounts')->where('id', $ba->id)->update(['bank_id' => $bank->id]);
            }
        }

        // Migrate existing account_type strings to account_type_id FK where possible
        $typeMap = [
            'checking' => 'bank_current',
            'savings' => 'bank_savings',
            'fixed_deposit' => 'bank_fixed_deposit',
        ];
        foreach ($typeMap as $oldKey => $typeKey) {
            $type = DB::table('account_types')->where('key', $typeKey)->first();
            if ($type) {
                DB::table('bank_accounts')
                    ->where('account_type', $oldKey)
                    ->update(['account_type_id' => $type->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropForeign(['account_type_id']);
            $table->dropColumn(['bank_id', 'account_type_id']);
        });
    }
};
