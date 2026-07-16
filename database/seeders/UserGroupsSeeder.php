<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class UserGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSalesPerson();
        $this->createManagers();
        $this->createDirectors();
    }

    private function perm(array $perms = []): array
    {
        return array_merge([
            'can_view'    => false,
            'can_create'  => false,
            'can_edit'    => false,
            'can_delete'  => false,
            'can_approve' => false,
            'can_2fa'     => false,
            'can_print'   => false,
            'can_export'  => false,
            'can_import'  => false,
            'can_reverse' => false,
            'can_cancel'  => false,
        ], $perms);
    }

    private function view(): array
    {
        return $this->perm(['can_view' => true]);
    }

    private function full(): array
    {
        return $this->perm([
            'can_view' => true, 'can_create' => true, 'can_edit' => true,
            'can_delete' => true, 'can_print' => true, 'can_export' => true,
        ]);
    }

    private function syncGroup(Group $group, array $routePermissions): void
    {
        $menus = Menu::whereIn('route', array_keys($routePermissions))->get();
        $permissions = [];
        foreach ($menus as $menu) {
            $permissions[$menu->id] = $routePermissions[$menu['route']];
        }
        $group->menus()->sync($permissions);
    }

    private function createSalesPerson(): void
    {
        $group = Group::firstOrCreate(
            ['name' => 'SalesPerson'],
            [
                'description'       => 'Sales staff — customer registration and sales activity only. No returns, no purchases, no finance.',
                'is_super_admin'    => false,
                'is_active'         => true,
            ]
        );

        $this->syncGroup($group, [
            // Dashboard
            'dashboard'                => $this->view(),

            // Master Data — Customers
            'customers.index'          => $this->perm(['can_view' => true, 'can_create' => true]),
            'customers.dashboard'      => $this->view(),

            // Sales — POS
            'pos.index'                => $this->perm(['can_view' => true, 'can_create' => true]),
            'pos.dashboard'            => $this->view(),
            'sales.new'                => $this->perm(['can_view' => true, 'can_create' => true]),
            'invoices.index'           => $this->view(),
            'payments.index'           => $this->view(),
            'sales.index'              => $this->view(),
        ]);
    }

    private function createManagers(): void
    {
        $group = Group::firstOrCreate(
            ['name' => 'Managers'],
            [
                'description'       => 'Managers — full sales, returns, purchases, basic reports. Purchase orders created here are approved by Directors.',
                'is_super_admin'    => false,
                'is_active'         => true,
            ]
        );

        $this->syncGroup($group, [
            // Dashboard
            'dashboard'                => $this->view(),
            'reports.analytics'        => $this->view(),

            // Master Data — Products
            'products.index'           => $this->view(),
            'categories.index'         => $this->view(),
            'units.index'              => $this->view(),
            'warehouses.index'         => $this->view(),

            // Master Data — Customers
            'customers.index'          => $this->full(),
            'customers.dashboard'      => $this->view(),
            'customer-groups.index'    => $this->view(),

            // Master Data — Suppliers
            'suppliers.index'          => $this->full(),

            // Master Data — Pricing
            'price-lists.dashboard'    => $this->view(),
            'price-lists.index'        => $this->view(),

            // Procurement
            'purchasing.orders.index'  => $this->perm(['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_print' => true]),
            'purchasing.receipts.index' => $this->perm(['can_view' => true, 'can_create' => true]),
            'purchase-returns.index'   => $this->perm(['can_view' => true, 'can_create' => true]),
            'purchasing.analytics'     => $this->view(),

            // Inventory
            'inventory.index'          => $this->view(),
            'stock-adjustments.index'  => $this->perm(['can_view' => true, 'can_create' => true]),
            'store-requests.index'     => $this->perm(['can_view' => true, 'can_create' => true]),

            // Sales — Orders
            'sales.dashboard'          => $this->view(),
            'sales.orders.index'       => $this->perm(['can_view' => true, 'can_create' => true, 'can_edit' => true]),

            // Sales — POS
            'pos.index'                => $this->perm(['can_view' => true, 'can_create' => true]),
            'pos.dashboard'            => $this->view(),
            'sales.new'                => $this->perm(['can_view' => true, 'can_create' => true]),
            'invoices.index'           => $this->perm(['can_view' => true, 'can_print' => true]),
            'payments.index'           => $this->view(),
            'sales.index'              => $this->view(),
            'customer-advances.index'  => $this->perm(['can_view' => true, 'can_create' => true]),

            // Sales — Returns
            'sales-returns.index'      => $this->perm(['can_view' => true, 'can_create' => true]),
            'credit-notes.index'       => $this->perm(['can_view' => true]),
            'refunds.index'            => $this->perm(['can_view' => true]),

            // Finance — Accounting
            'accounts.index'           => $this->view(),
            'journal-entries.index'    => $this->view(),
            'expenses.index'           => $this->perm(['can_view' => true, 'can_create' => true]),
            'expense-categories.index' => $this->view(),

            // Finance — Payments
            'supplier-payments.index'  => $this->perm(['can_view' => true, 'can_create' => true]),
            'bank-reconciliations.index' => $this->view(),

            // Banking
            'bank-accounts.index'      => $this->view(),

            // Reports — Basic
            'reports.sales'            => $this->view(),
            'reports.inventory'        => $this->view(),
            'reports.customers'        => $this->view(),
            'reports.suppliers'        => $this->view(),
        ]);
    }

    private function createDirectors(): void
    {
        $group = Group::firstOrCreate(
            ['name' => 'Directors'],
            [
                'description'       => 'Directors — full system access except admin-managed pages (Users, Groups, Menus, Settings).',
                'is_super_admin'    => false,
                'is_active'         => true,
            ]
        );

        // Directors get ALL menus except Administration section
        $adminRoutes = [
            'users.index',
            'groups.index',
            'menus.index',
            'approval-configurations.index',
            'settings.index',
            'settings.dashboard-cards.index',
            'settings.document-numbering.index',
            'audit-logs.index',
            'reports.scheduled.index',
        ];

        $allMenus = Menu::where('is_active', true)->whereNotNull('route')->get();
        $permissions = [];

        foreach ($allMenus as $menu) {
            if (in_array($menu->route, $adminRoutes)) {
                continue;
            }
            $permissions[$menu->id] = [
                'can_view'    => true,
                'can_create'  => true,
                'can_edit'    => true,
                'can_delete'  => true,
                'can_approve' => true,
                'can_print'   => true,
                'can_export'  => true,
                'can_import'  => true,
                'can_reverse' => true,
                'can_cancel'  => true,
            ];
        }

        $group->menus()->sync($permissions);
    }
}
