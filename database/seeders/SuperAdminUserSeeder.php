<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserSeeder extends Seeder
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

        $group->menus()->syncWithoutDetaching($permissions);

        $user = User::firstOrCreate(
            ['email' => 'upendokasunga@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (!$user->groups()->where('group_id', $group->id)->exists()) {
            $user->groups()->attach($group->id);
        }
    }
}
