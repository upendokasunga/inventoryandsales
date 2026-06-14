<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class SuperAdminGroupSeeder extends Seeder
{
    public function run(): void
    {
        $group = Group::firstOrCreate(
            ['name' => 'Super Administrators'],
            [
                'description' => 'Full system access - cannot be deleted',
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $menus = Menu::all();
        $permissions = [];

        foreach ($menus as $menu) {
            $permissions[$menu->id] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_approve' => true,
                'can_2fa' => true,
            ];
        }

        $group->menus()->sync($permissions);
    }
}
