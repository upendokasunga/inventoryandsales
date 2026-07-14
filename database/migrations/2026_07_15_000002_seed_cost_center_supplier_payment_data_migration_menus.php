<?php

use App\Models\Group;
use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $entries = [
            [
                'name'       => 'Cost Centres',
                'route'      => 'cost-centers.index',
                'parent_id'  => 2,   // Master Data
                'section'    => 'Organization',
                'sort_order' => 43,
            ],
            [
                'name'       => 'Supplier Payments',
                'route'      => 'supplier-payments.index',
                'parent_id'  => 6,   // Finance
                'section'    => 'Payments',
                'sort_order' => 30,
            ],
            [
                'name'       => 'Data Migration',
                'route'      => 'data-migration.index',
                'parent_id'  => null,
                'section'    => null,
                'sort_order' => 65,
            ],
        ];

        foreach ($entries as $entry) {
            if (Menu::where('route', $entry['route'])->exists()) {
                continue;
            }

            $menu = Menu::create([
                'name'       => $entry['name'],
                'route'      => $entry['route'],
                'icon'       => null,
                'module'     => $entry['name'],
                'parent_id'  => $entry['parent_id'],
                'section'    => $entry['section'],
                'sort_order' => $entry['sort_order'],
                'is_parent'  => false,
                'is_visible' => true,
                'is_active'  => true,
            ]);

            // Sync permissions for all active groups
            $groups = Group::all();
            foreach ($groups as $group) {
                $group->menus()->syncWithoutDetaching([
                    $menu->id => [
                        'can_view'    => true,
                        'can_create'  => true,
                        'can_edit'    => true,
                        'can_delete'  => true,
                        'can_approve' => true,
                        'can_2fa'     => false,
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        Menu::whereIn('route', [
            'cost-centers.index',
            'supplier-payments.index',
            'data-migration.index',
        ])->delete();
    }
};
