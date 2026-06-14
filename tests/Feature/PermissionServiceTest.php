<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->permissionService = app(PermissionService::class);
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($user);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasMenuAccess('groups.index', 'can_view'));
        $this->assertTrue($user->hasMenuAccess('menus.index', 'can_view'));
    }

    public function test_user_without_group_has_no_permissions(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->hasMenuAccess('groups.index', 'can_view'));
    }

    public function test_user_permissions_are_merged_across_groups(): void
    {
        $user = User::factory()->create();

        $menu = Menu::where('route', 'groups.index')->first();
        $groupA = Group::factory()->create(['is_super_admin' => false]);
        $groupB = Group::factory()->create(['is_super_admin' => false]);

        $groupA->menus()->attach($menu->id, [
            'can_view' => true, 'can_create' => false,
            'can_edit' => false, 'can_delete' => false,
            'can_approve' => false, 'can_2fa' => false,
        ]);

        $groupB->menus()->attach($menu->id, [
            'can_view' => false, 'can_create' => true,
            'can_edit' => false, 'can_delete' => false,
            'can_approve' => false, 'can_2fa' => false,
        ]);

        $groupA->users()->attach($user);
        $groupB->users()->attach($user);

        $menus = $user->getCachedMenus();
        $dashboardMenu = collect($menus)->firstWhere('route', 'groups.index');

        $this->assertNotNull($dashboardMenu);
        $this->assertTrue($dashboardMenu['can_view']);
        $this->assertTrue($dashboardMenu['can_create']);
    }

    public function test_assign_group_permissions_clears_cache(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['is_super_admin' => false]);
        $group->users()->attach($user);

        $menu = Menu::where('route', 'dashboard')->first();

        $this->permissionService->assignGroupPermissions($group, [
            $menu->id => [
                'can_view' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'can_approve' => false,
                'can_2fa' => false,
            ],
        ]);

        $this->assertTrue($user->hasMenuAccess('dashboard', 'can_view'));
    }
}
