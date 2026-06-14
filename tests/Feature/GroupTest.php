<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::factory()->create();
        $superAdminGroup = Group::where('is_super_admin', true)->first();
        $superAdminGroup->users()->attach($this->admin);
    }

    public function test_group_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('groups.index'));

        $response->assertStatus(200);
        $response->assertSee('Super Administrators');
    }

    public function test_group_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('groups.create'));

        $response->assertStatus(200);
        $response->assertSee('Menu Permissions');
    }

    public function test_group_can_be_created(): void
    {
        $menu = Menu::first();

        $response = $this->actingAs($this->admin)->post(route('groups.store'), [
            'name' => 'Managers',
            'description' => 'Management team',
            'is_active' => true,
            'permissions' => [
                $menu->id => [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_approve' => false,
                    'can_2fa' => false,
                ],
            ],
        ]);

        $response->assertRedirect(route('groups.index'));
        $this->assertDatabaseHas('groups', ['name' => 'Managers']);

        $group = Group::where('name', 'Managers')->first();
        $this->assertTrue((bool) $group->menus()->where('menu_id', $menu->id)->first()->pivot->can_view);
    }

    public function test_group_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('groups.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_super_admin_group_cannot_be_modified(): void
    {
        $superGroup = Group::where('is_super_admin', true)->first();

        $response = $this->actingAs($this->admin)->patch(route('groups.update', $superGroup), [
            'name' => 'Modified Super Admin',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_super_admin_group_cannot_be_deleted(): void
    {
        $superGroup = Group::where('is_super_admin', true)->first();

        $response = $this->actingAs($this->admin)->delete(route('groups.destroy', $superGroup));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('groups', ['id' => $superGroup->id]);
    }

    public function test_group_can_be_deleted(): void
    {
        $group = Group::factory()->create(['is_super_admin' => false]);

        $response = $this->actingAs($this->admin)->delete(route('groups.destroy', $group));

        $response->assertRedirect(route('groups.index'));
        $this->assertSoftDeleted($group);
    }

    public function test_users_can_be_assigned_to_group(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('groups.assign-users', $group), [
            'user_ids' => [$user->id],
        ]);

        $response->assertRedirect(route('groups.index'));
        $this->assertTrue($group->users()->where('user_id', $user->id)->exists());
    }
}
