<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class SystemMenusSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Dashboard
            ['name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'chart-pie', 'module' => 'Dashboard', 'sort_order' => 1],

            // Authentication module
            ['name' => 'Users', 'route' => 'users.index', 'icon' => 'users', 'module' => 'Authentication', 'sort_order' => 10],
            ['name' => 'Groups', 'route' => 'groups.index', 'icon' => 'user-group', 'module' => 'Authentication', 'sort_order' => 11],
            ['name' => 'Menus', 'route' => 'menus.index', 'icon' => 'menu', 'module' => 'Authentication', 'sort_order' => 12],

            // System module
            ['name' => 'Settings', 'route' => 'settings.index', 'icon' => 'cog', 'module' => 'System', 'sort_order' => 20],
            ['name' => 'Audit Logs', 'route' => 'audit-logs.index', 'icon' => 'clipboard-list', 'module' => 'System', 'sort_order' => 21],

            // Master Data (Phase 2)
            ['name' => 'Categories', 'route' => 'categories.index', 'icon' => 'folder-tree', 'module' => 'Master Data', 'sort_order' => 30],
            ['name' => 'Category Tree', 'route' => 'categories.tree', 'icon' => 'folder-tree', 'module' => 'Master Data', 'sort_order' => 31],
            ['name' => 'Units', 'route' => 'units.index', 'icon' => 'ruler', 'module' => 'Master Data', 'sort_order' => 32],
            ['name' => 'Customer Groups', 'route' => 'customer-groups.index', 'icon' => 'users', 'module' => 'Master Data', 'sort_order' => 33],
            ['name' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'truck', 'module' => 'Master Data', 'sort_order' => 34],

            // Inventory module (Phase 3/7)
            ['name' => 'Products', 'route' => 'products.index', 'icon' => 'cube', 'module' => 'Inventory', 'sort_order' => 40],
            ['name' => 'Inventory Dashboard', 'route' => 'inventory.index', 'icon' => 'archive', 'module' => 'Inventory', 'sort_order' => 41],
            ['name' => 'Stock Adjustments', 'route' => 'stock-adjustments.index', 'icon' => 'adjustments', 'module' => 'Inventory', 'sort_order' => 42],
            ['name' => 'Batch Tracking', 'route' => 'inventory.batches', 'icon' => 'cube', 'module' => 'Inventory', 'sort_order' => 43],
            ['name' => 'Inventory Valuation', 'route' => 'inventory.valuation', 'icon' => 'currency-dollar', 'module' => 'Inventory', 'sort_order' => 44],

            // Pricing module (Phase 4)
            ['name' => 'Pricing Dashboard', 'route' => 'price-lists.dashboard', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 42],
            ['name' => 'Price Lists', 'route' => 'price-lists.index', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 43],
            ['name' => 'Pricing Simulator', 'route' => 'price-lists.simulator', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 44],

            ['name' => 'Customer Dashboard', 'route' => 'customers.dashboard', 'icon' => 'shopping-cart', 'module' => 'Sales', 'sort_order' => 49],
            ['name' => 'Customers', 'route' => 'customers.index', 'icon' => 'shopping-cart', 'module' => 'Sales', 'sort_order' => 50],
            ['name' => 'Sales Dashboard', 'route' => 'sales.dashboard', 'icon' => 'cash', 'module' => 'Sales', 'sort_order' => 51],
            ['name' => 'Sales Orders', 'route' => 'sales.orders.index', 'icon' => 'document-text', 'module' => 'Sales', 'sort_order' => 52],
            ['name' => 'Reservations', 'route' => 'sales.reservations.index', 'icon' => 'lock-closed', 'module' => 'Sales', 'sort_order' => 53],
            ['name' => 'Purchasing Dashboard', 'route' => '#', 'icon' => 'truck', 'module' => 'Purchasing', 'sort_order' => 59],
            ['name' => 'Purchase Suggestions', 'route' => 'purchasing.suggestions.index', 'icon' => 'clipboard-list', 'module' => 'Purchasing', 'sort_order' => 60],
            ['name' => 'Purchase Orders', 'route' => 'purchasing.orders.index', 'icon' => 'shopping-cart', 'module' => 'Purchasing', 'sort_order' => 61],
            ['name' => 'Goods Receiving', 'route' => 'purchasing.receipts.index', 'icon' => 'archive', 'module' => 'Purchasing', 'sort_order' => 62],
            ['name' => 'Supplier Analytics', 'route' => 'purchasing.analytics', 'icon' => 'chart-bar', 'module' => 'Purchasing', 'sort_order' => 63],
            ['name' => 'Reports', 'route' => '#', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 70],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['name' => $menu['name']],
                $menu
            );
        }
    }
}
