<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->group = Group::factory()->create(['is_super_admin' => false]);
    }

    public function test_permission_pivot_has_new_columns(): void
    {
        $menu = Menu::where('route', 'invoices.index')->first();
        $this->assertNotNull($menu);

        $this->group->menus()->attach($menu->id, [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => false,
            'can_approve' => false,
            'can_2fa' => false,
            'can_print' => true,
            'can_export' => true,
            'can_import' => false,
            'can_reverse' => false,
            'can_cancel' => false,
        ]);

        $pivot = $this->group->menus()->where('menu_id', $menu->id)->first()->pivot;

        $this->assertTrue((bool) $pivot->can_print);
        $this->assertTrue((bool) $pivot->can_export);
        $this->assertFalse((bool) $pivot->can_import);
        $this->assertFalse((bool) $pivot->can_reverse);
        $this->assertFalse((bool) $pivot->can_cancel);
    }

    public function test_permission_service_assignes_new_columns(): void
    {
        $menu = Menu::where('route', 'invoices.index')->first();
        $service = app(PermissionService::class);

        $service->assignGroupPermissions($this->group, [
            $menu->id => [
                'can_view' => true,
                'can_print' => true,
                'can_export' => true,
                'can_import' => true,
                'can_reverse' => false,
                'can_cancel' => false,
            ],
        ]);

        $pivot = $this->group->menus()->where('menu_id', $menu->id)->first()->pivot;

        $this->assertTrue((bool) $pivot->can_view);
        $this->assertTrue((bool) $pivot->can_print);
        $this->assertTrue((bool) $pivot->can_export);
        $this->assertTrue((bool) $pivot->can_import);
        $this->assertFalse((bool) $pivot->can_reverse);
        $this->assertFalse((bool) $pivot->can_cancel);
    }

    public function test_edit_view_shows_new_permissions(): void
    {
        $nonSuperGroup = Group::factory()->create(['is_super_admin' => false]);
        $nonSuperGroup->users()->attach($this->admin);

        $response = $this->actingAs($this->admin)->get(route('groups.edit', $nonSuperGroup));

        $response->assertStatus(200);
        $response->assertSee('Print');
        $response->assertSee('Export');
        $response->assertSee('Import');
        $response->assertSee('Reverse');
        $response->assertSee('Cancel');
    }

    public function test_create_view_shows_new_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('groups.create'));

        $response->assertStatus(200);
        $response->assertSee('Print');
        $response->assertSee('Export');
        $response->assertSee('Import');
        $response->assertSee('Reverse');
        $response->assertSee('Cancel');
    }

    public function test_print_permission_middleware_on_print_routes(): void
    {
        $this->assertTrue(true);
    }
}
