<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Assets — Current
            ['key' => 'asset_cash', 'label' => 'Cash Account', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 1],
            ['key' => 'asset_bank', 'label' => 'Bank Account', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 2],
            ['key' => 'asset_accounts_receivable', 'label' => 'Accounts Receivable', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 3],
            ['key' => 'asset_inventory', 'label' => 'Inventory', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 4],
            ['key' => 'asset_prepaid', 'label' => 'Prepaid Expenses', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 5],
            ['key' => 'asset_securities', 'label' => 'Marketable Securities', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 6],
            ['key' => 'asset_short_term_investments', 'label' => 'Short-term Investments', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 7],
            ['key' => 'asset_other_current', 'label' => 'Other Current Assets', 'base_type' => 'asset', 'asset_class' => 'current', 'display_order' => 8],

            // Assets — Non-current
            ['key' => 'asset_ppe', 'label' => 'Property, Plant & Equipment', 'base_type' => 'asset', 'asset_class' => 'non_current', 'display_order' => 9],
            ['key' => 'asset_intangible', 'label' => 'Intangible Assets', 'base_type' => 'asset', 'asset_class' => 'non_current', 'display_order' => 10],
            ['key' => 'asset_lt_investments', 'label' => 'Long-term Investments', 'base_type' => 'asset', 'asset_class' => 'non_current', 'display_order' => 11],
            ['key' => 'asset_deferred_tax', 'label' => 'Deferred Tax Assets', 'base_type' => 'asset', 'asset_class' => 'non_current', 'display_order' => 12],
            ['key' => 'asset_other_non_current', 'label' => 'Other Long-term Assets', 'base_type' => 'asset', 'asset_class' => 'non_current', 'display_order' => 13],

            // Liabilities — Current
            ['key' => 'liability_accounts_payable', 'label' => 'Accounts Payable', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 14],
            ['key' => 'liability_accrued', 'label' => 'Accrued Expenses', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 15],
            ['key' => 'liability_st_loans', 'label' => 'Short-term Loans', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 16],
            ['key' => 'liability_current_ltd', 'label' => 'Current Portion of Long-term Debt', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 17],
            ['key' => 'liability_taxes', 'label' => 'Taxes Payable', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 18],
            ['key' => 'liability_unearned', 'label' => 'Unearned Revenue', 'base_type' => 'liability', 'asset_class' => 'current', 'display_order' => 19],

            // Liabilities — Non-current
            ['key' => 'liability_lt_loans', 'label' => 'Long-term Loans', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 20],
            ['key' => 'liability_bonds', 'label' => 'Bonds Payable', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 21],
            ['key' => 'liability_lease', 'label' => 'Lease Obligations', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 22],
            ['key' => 'liability_pension', 'label' => 'Pension Liabilities', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 23],
            ['key' => 'liability_deferred_tax', 'label' => 'Deferred Tax Liabilities', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 24],
            ['key' => 'liability_other_non_current', 'label' => 'Other Long-term Liabilities', 'base_type' => 'liability', 'asset_class' => 'non_current', 'display_order' => 25],

            // Equity
            ['key' => 'equity_capital', 'label' => "Owner's Equity / Capital", 'base_type' => 'equity', 'asset_class' => null, 'display_order' => 26],
            ['key' => 'equity_retained', 'label' => 'Retained Earnings', 'base_type' => 'equity', 'asset_class' => null, 'display_order' => 27],

            // Income
            ['key' => 'income_sales', 'label' => 'Sales Revenue', 'base_type' => 'income', 'asset_class' => null, 'display_order' => 28],
            ['key' => 'income_other', 'label' => 'Other Income', 'base_type' => 'income', 'asset_class' => null, 'display_order' => 29],

            // Expenses
            ['key' => 'expense_cogs', 'label' => 'Cost of Goods Sold', 'base_type' => 'expense', 'asset_class' => null, 'display_order' => 30],
            ['key' => 'expense_operating', 'label' => 'Operating Expenses', 'base_type' => 'expense', 'asset_class' => null, 'display_order' => 31],
        ];

        foreach ($types as $type) {
            AccountType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
