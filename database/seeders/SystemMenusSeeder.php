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
            ['name' => 'Pricing Dashboard', 'route' => 'price-lists.dashboard', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 45],
            ['name' => 'Price Lists', 'route' => 'price-lists.index', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 46],
            ['name' => 'Pricing Simulator', 'route' => 'price-lists.simulator', 'icon' => 'currency-dollar', 'module' => 'Pricing', 'sort_order' => 47],

            // Point of Sale (Phase 9)
            ['name' => 'POS', 'route' => 'pos.index', 'icon' => 'shopping-cart', 'module' => 'Point of Sale', 'sort_order' => 1],
            ['name' => 'POS Dashboard', 'route' => 'pos.dashboard', 'icon' => 'chart-pie', 'module' => 'Point of Sale', 'sort_order' => 2],

            // Sales (Phase 9/10)
            ['name' => 'Invoices', 'route' => 'invoices.index', 'icon' => 'document-text', 'module' => 'Sales', 'sort_order' => 54],
            ['name' => 'Payments', 'route' => 'payments.index', 'icon' => 'cash', 'module' => 'Sales', 'sort_order' => 55],
            ['name' => 'Sales Returns', 'route' => 'sales-returns.index', 'icon' => 'refresh', 'module' => 'Sales', 'sort_order' => 56],
            ['name' => 'Credit Notes', 'route' => 'credit-notes.index', 'icon' => 'document-text', 'module' => 'Sales', 'sort_order' => 57],
            ['name' => 'Refunds', 'route' => 'refunds.index', 'icon' => 'currency-dollar', 'module' => 'Sales', 'sort_order' => 58],

            // Purchasing (Phase 10)
            ['name' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'icon' => 'refresh', 'module' => 'Purchasing', 'sort_order' => 64],

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
            ['name' => 'Sales Reports', 'route' => 'reports.sales', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 71],
            ['name' => 'Profit Analysis', 'route' => 'reports.profit', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 72],
            ['name' => 'Inventory Reports', 'route' => 'reports.inventory', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 73],
            ['name' => 'Customer Reports', 'route' => 'reports.customers', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 74],
            ['name' => 'Supplier Reports', 'route' => 'reports.suppliers', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 75],
            ['name' => 'Procurement Reports', 'route' => 'reports.procurement', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 76],
            ['name' => 'Tax Reports', 'route' => 'reports.tax', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 77],
            ['name' => 'Payment Reports', 'route' => 'reports.payments', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 78],
            ['name' => 'Analytics Dashboard', 'route' => 'reports.analytics', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 79],
            ['name' => 'KPI Dashboard', 'route' => 'reports.kpi', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 80],
            ['name' => 'Scheduled Reports', 'route' => 'reports.scheduled.index', 'icon' => 'chart-bar', 'module' => 'Reporting', 'sort_order' => 81],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['name' => $menu['name']],
                $menu
            );
        }
    }
}
