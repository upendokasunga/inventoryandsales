<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class SystemMenusSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 1. PARENT MENUS (expandable containers, no route)
        // ──────────────────────────────────────────────
        $parents = [
            ['name' => 'Dashboard',           'route' => null, 'icon' => 'chart-pie',       'module' => 'Dashboard',      'sort_order' => 1,  'is_parent' => true],
            ['name' => 'Master Data',         'route' => null, 'icon' => 'folder-tree',     'module' => 'Master Data',    'sort_order' => 10, 'is_parent' => true],
            ['name' => 'Procurement',         'route' => null, 'icon' => 'truck',           'module' => 'Purchasing',     'sort_order' => 20, 'is_parent' => true],
            ['name' => 'Inventory',           'route' => null, 'icon' => 'archive',         'module' => 'Inventory',      'sort_order' => 30, 'is_parent' => true],
            ['name' => 'Sales',               'route' => null, 'icon' => 'shopping-cart',    'module' => 'Sales',         'sort_order' => 40, 'is_parent' => true],
            ['name' => 'Finance',             'route' => null, 'icon' => 'currency-dollar',  'module' => 'Sales',         'sort_order' => 50, 'is_parent' => true],
            ['name' => 'Reports & Analytics', 'route' => null, 'icon' => 'chart-bar',       'module' => 'Reporting',     'sort_order' => 60, 'is_parent' => true],
            ['name' => 'Administration',      'route' => null, 'icon' => 'cog',             'module' => 'Authentication', 'sort_order' => 70, 'is_parent' => true],
        ];

        foreach ($parents as $menu) {
            Menu::updateOrCreate(
                ['name' => $menu['name']],
                $menu
            );
        }

        // Fetch parent IDs for child assignment
        $parentIds = [];
        foreach ($parents as $p) {
            $parentIds[$p['name']] = Menu::where('name', $p['name'])->value('id');
        }

        // ──────────────────────────────────────────────
        // 2. CHILD MENUS
        // ──────────────────────────────────────────────
        $children = [
            // ── Dashboard ──
            ['name' => 'Overview',              'route' => 'dashboard',                'icon' => 'chart-pie',      'module' => 'Dashboard',      'sort_order' => 1,  'parent' => 'Dashboard',        'section' => 'Overview'],
            ['name' => 'Analytics Dashboard',  'route' => 'reports.analytics',        'icon' => 'chart-bar',     'module' => 'Reporting',      'sort_order' => 2,  'parent' => 'Dashboard',        'section' => 'Analytics'],
            ['name' => 'KPI Dashboard',        'route' => 'reports.kpi',              'icon' => 'chart-bar',     'module' => 'Reporting',      'sort_order' => 3,  'parent' => 'Dashboard',        'section' => 'Analytics'],

            // ── Master Data → Products ──
            ['name' => 'Products',             'route' => 'products.index',           'icon' => 'cube',          'module' => 'Inventory',      'sort_order' => 10, 'parent' => 'Master Data',      'section' => 'Products'],
            ['name' => 'Categories',           'route' => 'categories.index',         'icon' => 'folder-tree',   'module' => 'Master Data',    'sort_order' => 11, 'parent' => 'Master Data',      'section' => 'Products'],
            ['name' => 'Category Tree',        'route' => 'categories.tree',          'icon' => 'folder-tree',   'module' => 'Master Data',    'sort_order' => 12, 'parent' => 'Master Data',      'section' => 'Products'],
            ['name' => 'Units',                'route' => 'units.index',              'icon' => 'ruler',         'module' => 'Master Data',    'sort_order' => 13, 'parent' => 'Master Data',      'section' => 'Products'],

            // ── Master Data → Customers ──
            ['name' => 'Customers',            'route' => 'customers.index',          'icon' => 'shopping-cart', 'module' => 'Sales',         'sort_order' => 20, 'parent' => 'Master Data',      'section' => 'Customers'],
            ['name' => 'Customer Dashboard',   'route' => 'customers.dashboard',     'icon' => 'shopping-cart', 'module' => 'Sales',         'sort_order' => 21, 'parent' => 'Master Data',      'section' => 'Customers'],
            ['name' => 'Customer Groups',      'route' => 'customer-groups.index',    'icon' => 'users',         'module' => 'Master Data',    'sort_order' => 22, 'parent' => 'Master Data',      'section' => 'Customers'],

            // ── Master Data → Suppliers ──
            ['name' => 'Suppliers',            'route' => 'suppliers.index',          'icon' => 'truck',         'module' => 'Master Data',    'sort_order' => 30, 'parent' => 'Master Data',      'section' => 'Suppliers'],

            // ── Master Data → Pricing ──
            ['name' => 'Pricing Dashboard',    'route' => 'price-lists.dashboard',   'icon' => 'currency-dollar', 'module' => 'Pricing',      'sort_order' => 40, 'parent' => 'Master Data',      'section' => 'Pricing'],
            ['name' => 'Price Lists',          'route' => 'price-lists.index',        'icon' => 'currency-dollar', 'module' => 'Pricing',      'sort_order' => 41, 'parent' => 'Master Data',      'section' => 'Pricing'],
            ['name' => 'Pricing Simulator',    'route' => 'price-lists.simulator',    'icon' => 'currency-dollar', 'module' => 'Pricing',      'sort_order' => 42, 'parent' => 'Master Data',      'section' => 'Pricing'],

            // ── Procurement → Purchasing ──
            ['name' => 'Purchase Suggestions', 'route' => 'purchasing.suggestions.index', 'icon' => 'clipboard-list', 'module' => 'Purchasing', 'sort_order' => 10, 'parent' => 'Procurement', 'section' => 'Purchasing'],
            ['name' => 'Purchase Orders',      'route' => 'purchasing.orders.index',      'icon' => 'shopping-cart',   'module' => 'Purchasing', 'sort_order' => 11, 'parent' => 'Procurement', 'section' => 'Purchasing'],
            ['name' => 'Goods Receiving',      'route' => 'purchasing.receipts.index',    'icon' => 'archive',         'module' => 'Purchasing', 'sort_order' => 12, 'parent' => 'Procurement', 'section' => 'Purchasing'],
            ['name' => 'Purchase Returns',     'route' => 'purchase-returns.index',       'icon' => 'refresh',         'module' => 'Purchasing', 'sort_order' => 13, 'parent' => 'Procurement', 'section' => 'Purchasing'],

            // ── Procurement → Analytics ──
            ['name' => 'Supplier Analytics',   'route' => 'purchasing.analytics',         'icon' => 'chart-bar',       'module' => 'Purchasing', 'sort_order' => 20, 'parent' => 'Procurement', 'section' => 'Analytics'],
            ['name' => 'Procurement Reports',  'route' => 'reports.procurement',          'icon' => 'chart-bar',       'module' => 'Reporting',  'sort_order' => 21, 'parent' => 'Procurement', 'section' => 'Analytics'],

            // ── Inventory → Stock Operations ──
            ['name' => 'Inventory Dashboard',  'route' => 'inventory.index',         'icon' => 'archive',        'module' => 'Inventory', 'sort_order' => 10, 'parent' => 'Inventory', 'section' => 'Stock Operations'],
            ['name' => 'Stock Adjustments',    'route' => 'stock-adjustments.index',  'icon' => 'adjustments',    'module' => 'Inventory', 'sort_order' => 11, 'parent' => 'Inventory', 'section' => 'Stock Operations'],
            ['name' => 'Reservations',         'route' => 'sales.reservations.index', 'icon' => 'lock-closed',    'module' => 'Sales',     'sort_order' => 12, 'parent' => 'Inventory', 'section' => 'Stock Operations'],

            // ── Inventory → Batch Management ──
            ['name' => 'Batch Tracking',       'route' => 'inventory.batches',        'icon' => 'cube',           'module' => 'Inventory', 'sort_order' => 20, 'parent' => 'Inventory', 'section' => 'Batch Management'],
            ['name' => 'Inventory Valuation',  'route' => 'inventory.valuation',      'icon' => 'currency-dollar', 'module' => 'Inventory', 'sort_order' => 21, 'parent' => 'Inventory', 'section' => 'Batch Management'],

            // ── Sales → Orders ──
            ['name' => 'Sales Dashboard',      'route' => 'sales.dashboard',          'icon' => 'cash',           'module' => 'Sales', 'sort_order' => 10, 'parent' => 'Sales', 'section' => 'Orders'],
            ['name' => 'Sales Orders',         'route' => 'sales.orders.index',       'icon' => 'document-text',  'module' => 'Sales', 'sort_order' => 11, 'parent' => 'Sales', 'section' => 'Orders'],

            // ── Sales → POS ──
            ['name' => 'POS',                  'route' => 'pos.index',                'icon' => 'shopping-cart',  'module' => 'Point of Sale', 'sort_order' => 20, 'parent' => 'Sales', 'section' => 'POS'],
            ['name' => 'POS Dashboard',        'route' => 'pos.dashboard',            'icon' => 'chart-pie',      'module' => 'Point of Sale', 'sort_order' => 21, 'parent' => 'Sales', 'section' => 'POS'],
            ['name' => 'Invoices',             'route' => 'invoices.index',           'icon' => 'document-text',  'module' => 'Sales',         'sort_order' => 22, 'parent' => 'Sales', 'section' => 'POS'],
            ['name' => 'Payments',             'route' => 'payments.index',           'icon' => 'cash',           'module' => 'Sales',         'sort_order' => 23, 'parent' => 'Sales', 'section' => 'POS'],

            // ── Sales → Returns ──
            ['name' => 'Sales Returns',        'route' => 'sales-returns.index',      'icon' => 'refresh',        'module' => 'Sales', 'sort_order' => 30, 'parent' => 'Sales', 'section' => 'Returns'],
            ['name' => 'Credit Notes',         'route' => 'credit-notes.index',       'icon' => 'document-text',  'module' => 'Sales', 'sort_order' => 31, 'parent' => 'Sales', 'section' => 'Returns'],
            ['name' => 'Refunds',              'route' => 'refunds.index',            'icon' => 'currency-dollar','module' => 'Sales', 'sort_order' => 32, 'parent' => 'Sales', 'section' => 'Returns'],

            // ── Finance → Profitability ──
            ['name' => 'Profit Analysis',      'route' => 'reports.profit',           'icon' => 'chart-bar',      'module' => 'Reporting', 'sort_order' => 10, 'parent' => 'Finance', 'section' => 'Profitability'],

            // ── Finance → Taxation ──
            ['name' => 'Tax Reports',          'route' => 'reports.tax',              'icon' => 'chart-bar',      'module' => 'Reporting', 'sort_order' => 20, 'parent' => 'Finance', 'section' => 'Taxation'],

            // ── Finance → Payments ──
            ['name' => 'Payment Reports',      'route' => 'reports.payments',         'icon' => 'chart-bar',      'module' => 'Reporting', 'sort_order' => 30, 'parent' => 'Finance', 'section' => 'Payments'],

            // ── Reports & Analytics ──
            ['name' => 'Sales Reports',        'route' => 'reports.sales',            'icon' => 'chart-bar',  'module' => 'Reporting', 'sort_order' => 10, 'parent' => 'Reports & Analytics', 'section' => 'Sales Reports'],
            ['name' => 'Inventory Reports',    'route' => 'reports.inventory',        'icon' => 'chart-bar',  'module' => 'Reporting', 'sort_order' => 11, 'parent' => 'Reports & Analytics', 'section' => 'Inventory Reports'],
            ['name' => 'Customer Reports',     'route' => 'reports.customers',        'icon' => 'chart-bar',  'module' => 'Reporting', 'sort_order' => 12, 'parent' => 'Reports & Analytics', 'section' => 'Customer Reports'],
            ['name' => 'Supplier Reports',     'route' => 'reports.suppliers',        'icon' => 'chart-bar',  'module' => 'Reporting', 'sort_order' => 13, 'parent' => 'Reports & Analytics', 'section' => 'Supplier Reports'],

            // ── Administration → Users & Security ──
            ['name' => 'Users',                'route' => 'users.index',              'icon' => 'users',       'module' => 'Authentication', 'sort_order' => 10, 'parent' => 'Administration', 'section' => 'Users & Security'],
            ['name' => 'Groups',               'route' => 'groups.index',             'icon' => 'user-group',  'module' => 'Authentication', 'sort_order' => 11, 'parent' => 'Administration', 'section' => 'Users & Security'],
            ['name' => 'Menus',                'route' => 'menus.index',              'icon' => 'menu',        'module' => 'Authentication', 'sort_order' => 12, 'parent' => 'Administration', 'section' => 'Users & Security'],

            // ── Administration → System ──
            ['name' => 'Settings',             'route' => 'settings.index',           'icon' => 'cog',         'module' => 'System',         'sort_order' => 20, 'parent' => 'Administration', 'section' => 'System'],
            ['name' => 'Audit Logs',           'route' => 'audit-logs.index',         'icon' => 'clipboard-list', 'module' => 'System',      'sort_order' => 21, 'parent' => 'Administration', 'section' => 'System'],

            // ── Administration → Reports ──
            ['name' => 'Scheduled Reports',    'route' => 'reports.scheduled.index',  'icon' => 'chart-bar',   'module' => 'Reporting',      'sort_order' => 30, 'parent' => 'Administration', 'section' => 'Reports'],
        ];

        foreach ($children as $menu) {
            $parentId = $parentIds[$menu['parent']] ?? null;

            Menu::updateOrCreate(
                ['route' => $menu['route']],
                [
                    'name' => $menu['name'],
                    'icon' => $menu['icon'],
                    'module' => $menu['module'],
                    'sort_order' => $menu['sort_order'],
                    'parent_id' => $parentId,
                    'section' => $menu['section'],
                    'is_parent' => false,
                    'is_visible' => true,
                ]
            );
        }
    }
}
