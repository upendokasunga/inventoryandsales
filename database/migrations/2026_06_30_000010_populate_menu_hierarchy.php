<?php

use App\Models\Group;
use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if parent menus already exist
        if (Menu::where('is_parent', true)->exists()) {
            return;
        }

        $parents = [
            'Dashboard'    => ['icon' => 'chart-pie',    'module' => 'Dashboard',      'sort_order' => 1],
            'Master Data'  => ['icon' => 'folder-tree',  'module' => 'Master Data',    'sort_order' => 10],
            'Procurement'  => ['icon' => 'truck',        'module' => 'Purchasing',     'sort_order' => 20],
            'Inventory'    => ['icon' => 'archive',      'module' => 'Inventory',      'sort_order' => 30],
            'Sales'        => ['icon' => 'shopping-cart', 'module' => 'Sales',         'sort_order' => 40],
            'Finance'      => ['icon' => 'currency-dollar', 'module' => 'Sales',       'sort_order' => 50],
            'Reports & Analytics' => ['icon' => 'chart-bar', 'module' => 'Reporting',  'sort_order' => 60],
            'Administration' => ['icon' => 'cog',        'module' => 'Authentication', 'sort_order' => 70],
        ];

        $parentIds = [];
        foreach ($parents as $name => $data) {
            $menu = Menu::create([
                'name' => $name,
                'route' => null,
                'icon' => $data['icon'],
                'module' => $data['module'],
                'sort_order' => $data['sort_order'],
                'is_parent' => true,
                'is_visible' => true,
            ]);
            $parentIds[$name] = $menu->id;
        }

        $children = [
            'Dashboard' => [
                'Dashboard'            => ['section' => 'Overview'],
                'Analytics Dashboard'  => ['section' => 'Analytics'],
                'KPI Dashboard'        => ['section' => 'Analytics'],
            ],
            'Master Data' => [
                'Products'             => ['section' => 'Products'],
                'Categories'           => ['section' => 'Products'],
                'Category Tree'        => ['section' => 'Products'],
                'Units'                => ['section' => 'Products'],
                'Customers'            => ['section' => 'Customers'],
                'Customer Dashboard'   => ['section' => 'Customers'],
                'Customer Groups'      => ['section' => 'Customers'],
                'Suppliers'            => ['section' => 'Suppliers'],
                'Pricing Dashboard'    => ['section' => 'Pricing'],
                'Price Lists'          => ['section' => 'Pricing'],
                'Pricing Simulator'    => ['section' => 'Pricing'],
            ],
            'Procurement' => [
                'Purchase Suggestions' => ['section' => 'Purchasing'],
                'Purchase Orders'      => ['section' => 'Purchasing'],
                'Goods Receiving'      => ['section' => 'Purchasing'],
                'Purchase Returns'     => ['section' => 'Purchasing'],
                'Supplier Analytics'   => ['section' => 'Analytics'],
                'Procurement Reports'  => ['section' => 'Analytics'],
            ],
            'Inventory' => [
                'Inventory Dashboard'  => ['section' => 'Stock Operations'],
                'Stock Adjustments'    => ['section' => 'Stock Operations'],
                'Reservations'         => ['section' => 'Stock Operations'],
                'Batch Tracking'       => ['section' => 'Batch Management'],
                'Inventory Valuation'  => ['section' => 'Batch Management'],
            ],
            'Sales' => [
                'Sales Dashboard'      => ['section' => 'Orders'],
                'Sales Orders'         => ['section' => 'Orders'],
                'POS'                  => ['section' => 'POS'],
                'POS Dashboard'        => ['section' => 'POS'],
                'Invoices'             => ['section' => 'POS'],
                'Payments'             => ['section' => 'POS'],
                'Sales Returns'        => ['section' => 'Returns'],
                'Credit Notes'         => ['section' => 'Returns'],
                'Refunds'              => ['section' => 'Returns'],
            ],
            'Finance' => [
                'Profit Analysis'      => ['section' => 'Profitability'],
                'Tax Reports'          => ['section' => 'Taxation'],
                'Payment Reports'      => ['section' => 'Payments'],
            ],
            'Reports & Analytics' => [
                'Sales Reports'        => ['section' => 'Sales Reports'],
                'Inventory Reports'    => ['section' => 'Inventory Reports'],
                'Customer Reports'     => ['section' => 'Customer Reports'],
                'Supplier Reports'     => ['section' => 'Supplier Reports'],
            ],
            'Administration' => [
                'Users'                => ['section' => 'Users & Security'],
                'Groups'               => ['section' => 'Users & Security'],
                'Menus'                => ['section' => 'Users & Security'],
                'Settings'             => ['section' => 'System'],
                'Audit Logs'           => ['section' => 'System'],
                'Scheduled Reports'    => ['section' => 'Reports'],
            ],
        ];

        foreach ($children as $parentName => $items) {
            foreach ($items as $childName => $data) {
                Menu::where('name', $childName)
                    ->where('is_parent', false)
                    ->whereNull('parent_id')
                    ->update([
                        'parent_id' => $parentIds[$parentName],
                        'section' => $data['section'],
                    ]);
            }
        }

        // Hide the old placeholder menus that had route = '#'
        Menu::where('route', '#')->update(['is_visible' => false]);

        // Sync parent menu permissions for all existing groups
        $this->syncParentPermissions($parentIds);

    }

    public function down(): void
    {
        $parentNames = [
            'Dashboard', 'Master Data', 'Procurement', 'Inventory',
            'Sales', 'Finance', 'Reports & Analytics', 'Administration',
        ];

        Menu::whereIn('name', $parentNames)->where('is_parent', true)->delete();

        Menu::whereNotNull('parent_id')
            ->update(['parent_id' => null, 'section' => null]);

        Menu::where('route', '#')->update(['is_visible' => true]);
    }

    private function syncParentPermissions(array $parentIds): void
    {
        $groups = Group::with('menus')->get();

        foreach ($groups as $group) {
            $existingPermissions = $group->menus()
                ->whereIn('menu_id', $parentIds)
                ->pluck('menu_id')
                ->toArray();

            $newPermissions = [];
            foreach ($parentIds as $id) {
                if (!in_array($id, $existingPermissions)) {
                    $newPermissions[$id] = [
                        'can_view' => true,
                        'can_create' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_approve' => true,
                        'can_2fa' => true,
                    ];
                }
            }

            if (!empty($newPermissions)) {
                $group->menus()->syncWithoutDetaching($newPermissions);
            }
        }
    }
};
