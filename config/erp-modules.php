<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERP Module Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for all ERP modules, sidebar items,
    | and their submenu items. This is the single source of truth
    | for the application's navigation structure.
    |
    | Each module has:
    |   - name: Display name
    |   - icon: Icon identifier (maps to nav-icons.blade.php)
    |   - route: Route name for modules that navigate directly (like Dashboard)
    |   - children: Submenu items (shown in horizontal submenu bar)
    |       - name: Display name
    |       - route: Route name
    |       - section: Optional section grouping
    |
    */

    'modules' => [

        [
            'name' => 'Dashboard',
            'icon' => 'chart-pie',
            'route' => 'dashboard',
            'children' => [],
        ],

        [
            'name' => 'Master Data',
            'icon' => 'folder-tree',
            'route' => null,
            'children' => [
                ['name' => 'Dashboard',          'route' => 'dashboard',                    'section' => null],
                ['name' => 'Products',            'route' => 'products.index',              'section' => 'Products'],
                ['name' => 'Categories',          'route' => 'categories.index',            'section' => 'Products'],
                ['name' => 'Category Tree',       'route' => 'categories.tree',             'section' => 'Products'],
                ['name' => 'Brands',              'route' => null,                          'section' => 'Products'],
                ['name' => 'Units',               'route' => 'units.index',                 'section' => 'Products'],
                ['name' => 'Customers',           'route' => 'customers.index',             'section' => 'Customers'],
                ['name' => 'Customer Dashboard',  'route' => 'customers.dashboard',         'section' => 'Customers'],
                ['name' => 'Customer Groups',     'route' => 'customer-groups.index',       'section' => 'Customers'],
                ['name' => 'Suppliers',           'route' => 'suppliers.index',             'section' => 'Suppliers'],
                ['name' => 'Pricing Dashboard',   'route' => 'price-lists.dashboard',       'section' => 'Pricing'],
                ['name' => 'Price Lists',         'route' => 'price-lists.index',           'section' => 'Pricing'],
                ['name' => 'Pricing Simulator',   'route' => 'price-lists.simulator',       'section' => 'Pricing'],
            ],
        ],

        [
            'name' => 'Procurement',
            'icon' => 'truck',
            'route' => null,
            'children' => [
                ['name' => 'Dashboard',             'route' => 'purchasing.analytics',             'section' => null],
                ['name' => 'Purchase Requisition',  'route' => 'purchasing.suggestions.index',    'section' => 'Purchasing'],
                ['name' => 'Local Purchase Orders', 'route' => 'purchasing.orders.index',         'section' => 'Purchasing'],
                ['name' => 'Goods Receiving',       'route' => 'purchasing.receipts.index',       'section' => 'Purchasing'],
                ['name' => 'Purchase Returns',      'route' => 'purchase-returns.index',          'section' => 'Purchasing'],
                ['name' => 'Supplier Analytics',    'route' => 'purchasing.analytics',            'section' => 'Analytics'],
                ['name' => 'Reports',               'route' => 'reports.procurement',             'section' => 'Analytics'],
            ],
        ],

        [
            'name' => 'Inventory',
            'icon' => 'archive',
            'route' => null,
            'children' => [
                ['name' => 'Dashboard',            'route' => 'inventory.index',             'section' => null],
                ['name' => 'Stock Adjustments',    'route' => 'stock-adjustments.index',     'section' => 'Stock Operations'],
                ['name' => 'Stock Transfers',      'route' => null,                          'section' => 'Stock Operations'],
                ['name' => 'Reservations',         'route' => 'sales.reservations.index',    'section' => 'Stock Operations'],
                ['name' => 'Batch Tracking',       'route' => 'inventory.batches',           'section' => 'Batch Management'],
                ['name' => 'Inventory Valuation',  'route' => 'inventory.valuation',         'section' => 'Batch Management'],
                ['name' => 'Cycle Counts',         'route' => null,                          'section' => 'Stock Operations'],
                ['name' => 'Low Stock',            'route' => 'inventory.index',             'section' => 'Stock Operations'],
            ],
        ],

        [
            'name' => 'Sales',
            'icon' => 'shopping-cart',
            'route' => null,
            'children' => [
                ['name' => 'Dashboard',      'route' => 'sales.dashboard',       'section' => null],
                ['name' => 'Quotations',     'route' => null,                    'section' => 'Orders'],
                ['name' => 'Sales Orders',   'route' => 'sales.orders.index',    'section' => 'Orders'],
                ['name' => 'POS',            'route' => 'pos.index',             'section' => 'POS'],
                ['name' => 'POS Dashboard',  'route' => 'pos.dashboard',         'section' => 'POS'],
                ['name' => 'Invoices',       'route' => 'invoices.index',        'section' => 'POS'],
                ['name' => 'Payments',       'route' => 'payments.index',        'section' => 'POS'],
                ['name' => 'Sales Returns',  'route' => 'sales-returns.index',   'section' => 'Returns'],
                ['name' => 'Credit Notes',   'route' => 'credit-notes.index',    'section' => 'Returns'],
                ['name' => 'Refunds',        'route' => 'refunds.index',         'section' => 'Returns'],
            ],
        ],

        [
            'name' => 'Finance',
            'icon' => 'currency-dollar',
            'route' => null,
            'children' => [
                ['name' => 'Dashboard',              'route' => 'reports.profit',          'section' => null],
                ['name' => 'Accounts Receivable',    'route' => 'customers.statement',     'section' => 'Receivables'],
                ['name' => 'Accounts Payable',       'route' => null,                      'section' => 'Payables'],
                ['name' => 'Banking',                'route' => null,                      'section' => 'Banking'],
                ['name' => 'Tax',                    'route' => 'reports.tax',             'section' => 'Taxation'],
                ['name' => 'Profit Analysis',        'route' => 'reports.profit',          'section' => 'Profitability'],
                ['name' => 'Financial Reports',      'route' => null,                      'section' => 'Reporting'],
            ],
        ],

        [
            'name' => 'Reports & Analytics',
            'icon' => 'chart-bar',
            'route' => null,
            'children' => [
                ['name' => 'Sales Reports',       'route' => 'reports.sales',        'section' => null],
                ['name' => 'Procurement Reports', 'route' => 'reports.procurement',  'section' => null],
                ['name' => 'Inventory Reports',   'route' => 'reports.inventory',    'section' => null],
                ['name' => 'Finance Reports',     'route' => null,                   'section' => null],
                ['name' => 'Customer Reports',    'route' => 'reports.customers',    'section' => null],
                ['name' => 'Supplier Reports',    'route' => 'reports.suppliers',    'section' => null],
                ['name' => 'Executive Dashboard', 'route' => 'reports.analytics',    'section' => null],
            ],
        ],

        [
            'name' => 'Administration',
            'icon' => 'cog',
            'route' => null,
            'children' => [
                ['name' => 'Users',             'route' => 'users.index',              'section' => 'Users & Security'],
                ['name' => 'Roles & Permissions', 'route' => 'groups.index',           'section' => 'Users & Security'],
                ['name' => 'Organization',       'route' => null,                      'section' => 'Organization'],
                ['name' => 'Menu Management',    'route' => 'menus.index',             'section' => 'Users & Security'],
                ['name' => 'System Settings',    'route' => 'settings.index',          'section' => 'System'],
                ['name' => 'Audit Logs',         'route' => 'audit-logs.index',        'section' => 'System'],
                ['name' => 'Scheduled Jobs',     'route' => 'reports.scheduled.index', 'section' => 'System'],
            ],
        ],

    ],

];
