<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_many_groups(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $user->groups()->attach($group);

        $this->assertTrue($user->groups()->where('group_id', $group->id)->exists());
        $this->assertTrue($group->users()->where('user_id', $user->id)->exists());
    }

    public function test_user_has_uuid_after_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->uuid);
        $this->assertTrue(strlen($user->uuid) === 36);
    }

    public function test_user_is_active_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
    }

    public function test_is_super_admin_returns_true_for_super_admin_group_members(): void
    {
        $user = User::factory()->create();
        $superGroup = Group::factory()->create(['is_super_admin' => true]);
        $user->groups()->attach($superGroup);

        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_is_super_admin_returns_false_for_regular_group_members(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['is_super_admin' => false]);
        $user->groups()->attach($group);

        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_active_scope(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);

        $this->assertCount(2, User::all());
        $this->assertCount(1, User::active()->get());
    }
}
