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
            ['name' => 'Units', 'route' => 'units.index', 'icon' => 'ruler', 'module' => 'Master Data', 'sort_order' => 31],
            ['name' => 'Customer Groups', 'route' => 'customer-groups.index', 'icon' => 'users', 'module' => 'Master Data', 'sort_order' => 32],
            ['name' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'truck', 'module' => 'Master Data', 'sort_order' => 33],

            // Placeholder for future phases
            ['name' => 'Products', 'route' => '#', 'icon' => 'cube', 'module' => 'Inventory', 'sort_order' => 40],
            ['name' => 'Stock', 'route' => '#', 'icon' => 'archive', 'module' => 'Inventory', 'sort_order' => 41],
            ['name' => 'Customers', 'route' => '#', 'icon' => 'shopping-cart', 'module' => 'Sales', 'sort_order' => 50],
            ['name' => 'Sales', 'route' => '#', 'icon' => 'cash', 'module' => 'Sales', 'sort_order' => 51],
            ['name' => 'Purchases', 'route' => '#', 'icon' => 'truck', 'module' => 'Purchasing', 'sort_order' => 60],
            ['name' => 'Reports', 'route' => '#', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 70],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name']],
                $menu
            );
        }
    }
}
