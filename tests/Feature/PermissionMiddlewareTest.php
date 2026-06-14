<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_non_super_admin_without_permission_gets_403(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['is_super_admin' => false]);
        $group->users()->attach($user);

        $response = $this->actingAs($user)->get(route('groups.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_all_routes(): void
    {
        $user = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($user);

        $response = $this->actingAs($user)->get(route('groups.index'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('groups.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
    }
}
