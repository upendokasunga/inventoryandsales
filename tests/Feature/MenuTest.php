<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
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

    public function test_menu_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('menus.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_menu_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('menus.store'), [
            'name' => 'Test Menu',
            'route' => 'test.route',
            'icon' => 'circle',
            'module' => 'Testing',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Test Menu', 'route' => 'test.route']);
    }

    public function test_menu_can_be_updated(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->actingAs($this->admin)->patch(route('menus.update', $menu), [
            'name' => 'Updated Menu',
            'module' => 'Updated',
            'sort_order' => 5,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Updated Menu', 'is_active' => false]);
    }

    public function test_menu_can_be_deleted(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('menus.destroy', $menu));

        $response->assertRedirect(route('menus.index'));
        $this->assertSoftDeleted($menu);
    }

    public function test_menu_requires_name_and_module(): void
    {
        $response = $this->actingAs($this->admin)->post(route('menus.store'), [
            'name' => '',
            'module' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'module']);
    }
}
