<?php

return [

    'modules' => [

        [
            'name' => 'Dashboard',
            'icon' => 'chart-pie',
            'route' => 'dashboard',
            'children' => [],
        ],

        [
            'name' => 'Stakeholders',
            'icon' => 'folder-tree',
            'route' => null,
            'children' => [
                ['name' => 'Products',            'route' => 'products.index',              'section' => 'Products'],
                ['name' => 'Categories',          'route' => 'categories.index',            'section' => 'Products'],
                ['name' => 'Brands',              'route' => null,                          'section' => 'Products'],
                ['name' => 'Units',               'route' => 'units.index',                 'section' => 'Products'],
                ['name' => 'Warehouses',          'route' => 'warehouses.index',            'section' => 'Inventory'],
                ['name' => 'Customers',           'route' => 'customers.index',             'section' => 'Relations'],
                ['name' => 'Customer Groups',     'route' => 'customer-groups.index',       'section' => 'Relations'],
                ['name' => 'Suppliers',           'route' => 'suppliers.index',             'section' => 'Relations'],
                ['name' => 'Price Lists',         'route' => 'price-lists.index',           'section' => 'Pricing'],
                ['name' => 'Pricing Simulator',   'route' => 'price-lists.simulator',       'section' => 'Pricing'],
                ['name' => 'Cost Centres',        'route' => 'cost-centers.index',          'section' => 'Organization'],
            ],
        ],

        [
            'name' => 'Procurement',
            'icon' => 'truck',
            'route' => null,
            'children' => [
                ['name' => 'Purchase Orders',        'route' => 'purchasing.orders.index',      'section' => 'Orders'],
                ['name' => 'Goods Receiving',        'route' => 'purchasing.receipts.index',    'section' => 'Orders'],
                ['name' => 'Purchase Returns',       'route' => 'purchase-returns.index',       'section' => 'Returns'],
                ['name' => 'Supplier Payments',      'route' => 'supplier-payments.index',      'section' => 'Payments'],
                ['name' => 'Supplier Performance',   'route' => 'purchasing.analytics',         'section' => 'Analytics'],
                ['name' => 'Reports',                'route' => 'reports.procurement',          'section' => 'Analytics'],
            ],
        ],

        [
            'name' => 'Inventory',
            'icon' => 'archive',
            'route' => null,
            'children' => [
                ['name' => 'Available Stock',     'route' => 'inventory.available-stock', 'section' => 'Operations'],
                ['name' => 'Stock Adjustments',    'route' => 'stock-adjustments.index',     'section' => 'Operations'],
                ['name' => 'Store Requests',       'route' => 'store-requests.index',        'section' => 'Operations'],
                ['name' => 'Stock Transfers',      'route' => 'stock-transfers.index',       'section' => 'Operations'],
                ['name' => 'Batch Tracking',       'route' => 'inventory.batches',           'section' => 'Tracking'],
                ['name' => 'Inventory Valuation',  'route' => 'inventory.valuation',         'section' => 'Tracking'],
                ['name' => 'Low Stock',            'route' => 'inventory.index',             'section' => 'Tracking'],
            ],
        ],

        [
            'name' => 'Sales',
            'icon' => 'shopping-cart',
            'route' => null,
            'children' => [
                ['name' => 'Proforma Invoices', 'route' => 'sales.orders.index',    'section' => 'Orders'],
                ['name' => 'Invoices',         'route' => 'invoices.index',        'section' => 'Orders'],
                ['name' => 'POS',              'route' => 'sales.new',             'section' => 'POS'],
                ['name' => 'Payments',         'route' => 'payments.index',        'section' => 'Payments'],
                ['name' => 'Customer Advances','route' => 'customer-advances.index','section' => 'Payments'],
                ['name' => 'Sales Returns',    'route' => 'sales-returns.index',   'section' => 'Returns'],
                ['name' => 'Credit Notes',     'route' => 'credit-notes.index',    'section' => 'Returns'],
                ['name' => 'Refunds',          'route' => 'refunds.index',         'section' => 'Returns'],
            ],
        ],

        [
            'name' => 'Finance',
            'icon' => 'currency-dollar',
            'route' => null,
            'children' => [
                ['name' => 'Chart of Accounts',   'route' => 'accounts.index',            'section' => 'Accounting'],
                ['name' => 'Journal Entries',     'route' => 'journal-entries.index',      'section' => 'Accounting'],
                ['name' => 'Expenses',            'route' => 'expenses.index',             'section' => 'Accounting'],
                ['name' => 'Accounts Receivable', 'route' => 'customers.statement',        'section' => 'Receivables'],
                ['name' => 'Accounts Payable',    'route' => null,                         'section' => 'Payables'],
                ['name' => 'Reconciliations',    'route' => 'bank-reconciliations.index',  'section' => 'Banking'],
                ['name' => 'Tax',                 'route' => 'reports.tax',                'section' => 'Taxation'],
                ['name' => 'Profit Analysis',     'route' => 'reports.profit',             'section' => 'Profitability'],
                ['name' => 'Financial Statements','route' => null,                         'section' => 'Reporting'],
            ],
        ],

        [
            'name' => 'Banking',
            'icon' => 'building-library',
            'route' => null,
            'children' => [
                ['name' => 'Bank Institutions', 'route' => 'banks.index',           'section' => 'Registration'],
                ['name' => 'Bank Accounts',     'route' => 'bank-accounts.index',   'section' => 'Registration'],
            ],
        ],

        [
            'name' => 'Data Migration',
            'icon' => 'arrow-up-tray',
            'route' => 'data-migration.index',
            'children' => [
                ['name' => 'Import Products',  'route' => 'data-migration.products.upload',  'section' => 'Import'],
                ['name' => 'Import Customers', 'route' => 'data-migration.customers.upload', 'section' => 'Import'],
                ['name' => 'Import Sales',     'route' => 'data-migration.sales.upload',     'section' => 'Import'],
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
                ['name' => 'Users',                'route' => 'users.index',              'section' => 'Access'],
                ['name' => 'Roles & Permissions',  'route' => 'groups.index',             'section' => 'Access'],
                ['name' => 'Menu Management',      'route' => 'menus.index',              'section' => 'Access'],
                ['name' => 'Approval Levels',      'route' => 'approval-configurations.index', 'section' => 'Access'],
                ['name' => 'Branches',             'route' => 'branches.index',           'section' => 'Organization'],
                ['name' => 'System Settings',      'route' => 'settings.index',           'section' => 'System'],
                ['name' => 'Dashboard Cards',      'route' => 'settings.dashboard-cards.index', 'section' => 'System'],
                ['name' => 'Document Numbering',  'route' => 'settings.document-numbering.index', 'section' => 'System'],
                ['name' => 'Audit Logs',           'route' => 'audit-logs.index',         'section' => 'System'],
                ['name' => 'Scheduled Jobs',       'route' => 'reports.scheduled.index',  'section' => 'System'],
            ],
        ],

    ],

];
