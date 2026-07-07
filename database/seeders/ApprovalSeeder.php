<?php

namespace Database\Seeders;

use App\Models\ApprovalConfiguration;
use App\Models\ApprovalLevel;
use App\Models\Group;
use Illuminate\Database\Seeder;

class ApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = Group::where('name', 'Administrators')->first();

        $modules = [
            ['module_key' => 'purchase_order',  'module_name' => 'Purchase Orders',    'approval_level' => 1],
            ['module_key' => 'sales_order',     'module_name' => 'Sales Orders',       'approval_level' => 1],
            ['module_key' => 'store_request',   'module_name' => 'Store Requests',     'approval_level' => 1],
            ['module_key' => 'stock_transfer',  'module_name' => 'Stock Transfers',    'approval_level' => 1],
            ['module_key' => 'expense',         'module_name' => 'Expenses',           'approval_level' => 1],
            ['module_key' => 'stock_adjustment','module_name' => 'Stock Adjustments',  'approval_level' => 1],
            ['module_key' => 'journal_entry',   'module_name' => 'Journal Entries',    'approval_level' => 1],
        ];

        foreach ($modules as $mod) {
            $config = ApprovalConfiguration::firstOrCreate(
                ['module_key' => $mod['module_key']],
                [
                    'module_name' => $mod['module_name'],
                    'approval_level' => $mod['approval_level'],
                    'is_active' => true,
                ]
            );

            if ($adminGroup && $config->wasRecentlyCreated) {
                ApprovalLevel::create([
                    'approval_configuration_id' => $config->id,
                    'level' => 1,
                    'name' => 'Administrator Approval',
                    'group_id' => $adminGroup->id,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
