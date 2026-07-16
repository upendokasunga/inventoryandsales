<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'ifrs_category' => 'cash', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1100', 'name' => 'Bank', 'type' => 'asset', 'ifrs_category' => 'bank', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'ifrs_category' => 'ar', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1300', 'name' => 'Inventory', 'type' => 'asset', 'ifrs_category' => 'inventory', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1400', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'ifrs_category' => 'prepaid', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1500', 'name' => 'Marketable Securities', 'type' => 'asset', 'ifrs_category' => 'securities', 'category' => 'current_asset', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1600', 'name' => 'Property, Plant & Equipment', 'type' => 'asset', 'ifrs_category' => 'ppe', 'category' => 'non_current_asset', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1610', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'ifrs_category' => 'accumulated_depreciation', 'category' => 'non_current_asset', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1700', 'name' => 'Intangible Assets', 'type' => 'asset', 'ifrs_category' => 'intangible', 'category' => 'non_current_asset', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '1800', 'name' => 'Long-term Investments', 'type' => 'asset', 'ifrs_category' => 'lt_investments', 'category' => 'non_current_asset', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'ifrs_category' => 'ap', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => 'liability', 'ifrs_category' => 'accrued', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2200', 'name' => 'Short-term Loans', 'type' => 'liability', 'ifrs_category' => 'st_loans', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2300', 'name' => 'Taxes Payable', 'type' => 'liability', 'ifrs_category' => 'taxes_payable', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2400', 'name' => 'Unearned Revenue', 'type' => 'liability', 'ifrs_category' => 'unearned', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2500', 'name' => 'VAT Payable', 'type' => 'liability', 'ifrs_category' => 'taxes_payable', 'category' => 'current_liability', 'current_noncurrent' => 'current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2600', 'name' => 'Long-term Loans', 'type' => 'liability', 'ifrs_category' => 'lt_loans', 'category' => 'non_current_liability', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '2700', 'name' => 'Bonds Payable', 'type' => 'liability', 'ifrs_category' => 'bonds', 'category' => 'non_current_liability', 'current_noncurrent' => 'non_current', 'reportable' => true, 'include_in_income_statement' => false],

            // Equity
            ['code' => '3000', 'name' => "Owner's Equity", 'type' => 'equity', 'ifrs_category' => 'equity', 'category' => 'equity', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'ifrs_category' => 'retained_earnings', 'category' => 'equity', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => false],
            ['code' => '3200', 'name' => 'Capital Injection', 'type' => 'equity', 'ifrs_category' => 'capital', 'category' => 'equity', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => false],

            // Income
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income', 'ifrs_category' => 'revenue', 'category' => 'income', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => true],
            ['code' => '4100', 'name' => 'Other Income', 'type' => 'income', 'ifrs_category' => 'other_income', 'category' => 'income', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => true],
            ['code' => '4200', 'name' => 'Interest Income', 'type' => 'income', 'ifrs_category' => 'interest_income', 'category' => 'income', 'current_noncurrent' => null, 'reportable' => true, 'include_in_income_statement' => true],

            // Expenses
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'ifrs_category' => 'cogs', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'cogs', 'include_in_income_statement' => true],
            ['code' => '5100', 'name' => 'Salaries Expense', 'type' => 'expense', 'ifrs_category' => 'salary', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
            ['code' => '5200', 'name' => 'Rent Expense', 'type' => 'expense', 'ifrs_category' => 'rent', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
            ['code' => '5300', 'name' => 'Utilities Expense', 'type' => 'expense', 'ifrs_category' => 'utilities', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
            ['code' => '5400', 'name' => 'Depreciation Expense', 'type' => 'expense', 'ifrs_category' => 'depreciation', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
            ['code' => '5500', 'name' => 'Marketing Expense', 'type' => 'expense', 'ifrs_category' => 'marketing', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'selling', 'include_in_income_statement' => true],
            ['code' => '5600', 'name' => 'Transport Expense', 'type' => 'expense', 'ifrs_category' => 'transport', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'selling', 'include_in_income_statement' => true],
            ['code' => '5700', 'name' => 'Bank Charges', 'type' => 'expense', 'ifrs_category' => 'bank_charges', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
            ['code' => '5800', 'name' => 'Exchange Gain/Loss', 'type' => 'expense', 'ifrs_category' => 'fx_gain_loss', 'category' => 'expense', 'current_noncurrent' => null, 'reportable' => true, 'function_of_expense' => 'admin', 'include_in_income_statement' => true],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
