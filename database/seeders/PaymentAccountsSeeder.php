<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class PaymentAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Create operational cash accounts (children of root Cash 1000)
        $cashParent = Account::where('code', '1000')->first();

        $cashAccounts = [
            ['code' => '1010', 'name' => 'Cash on Hand', 'ifrs_category' => 'cash', 'category' => 'current_asset', 'reportable' => false],
            ['code' => '1020', 'name' => 'Petty Cash', 'ifrs_category' => 'cash', 'category' => 'current_asset', 'reportable' => false],
            ['code' => '1030', 'name' => 'Till - Main', 'ifrs_category' => 'cash', 'category' => 'current_asset', 'reportable' => false],
        ];

        foreach ($cashAccounts as $cash) {
            Account::updateOrCreate(
                ['code' => $cash['code']],
                array_merge($cash, [
                    'type' => 'asset',
                    'current_noncurrent' => 'current',
                    'parent_id' => $cashParent?->id,
                    'is_active' => true,
                ])
            );
        }

        // Create operational bank accounts (children of root Bank 1100)
        $bankParent = Account::where('code', '1100')->first();

        $bankAccounts = [
            ['code' => '1110', 'name' => 'CRDB Bank - Main', 'ifrs_category' => 'bank', 'category' => 'current_asset', 'reportable' => false, 'bank_name' => 'CRDB Bank', 'bank_branch' => 'Main Branch'],
            ['code' => '1120', 'name' => 'NMB Bank - Main', 'ifrs_category' => 'bank', 'category' => 'current_asset', 'reportable' => false, 'bank_name' => 'NMB Bank', 'bank_branch' => 'Main Branch'],
            ['code' => '1130', 'name' => 'NBC Bank - Main', 'ifrs_category' => 'bank', 'category' => 'current_asset', 'reportable' => false, 'bank_name' => 'NBC Bank', 'bank_branch' => 'Main Branch'],
        ];

        foreach ($bankAccounts as $bank) {
            Account::updateOrCreate(
                ['code' => $bank['code']],
                array_merge($bank, [
                    'type' => 'asset',
                    'current_noncurrent' => 'current',
                    'parent_id' => $bankParent?->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
