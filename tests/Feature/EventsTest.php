<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_successful_login_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'event' => 'login',
        ]);
    }

    public function test_successful_login_updates_last_login_at(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_failed_login_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'auth',
            'event' => 'failed_login',
        ]);
    }

    public function test_failed_login_logs_attempted_email(): void
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $log = AuditLog::where('event', 'failed_login')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('nonexistent@example.com', json_encode($log->new_values));
    }

    public function test_group_permissions_updated_dispatch_clears_cache(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['is_super_admin' => false]);
        $group->users()->attach($user);

        $this->assertFalse($user->hasMenuAccess('dashboard', 'can_view'));

        $menu = \App\Models\Menu::where('route', 'dashboard')->first();
        app(\App\Services\PermissionService::class)->assignGroupPermissions($group, [
            $menu->id => [
                'can_view' => true, 'can_create' => false,
                'can_edit' => false, 'can_delete' => false,
                'can_approve' => false, 'can_2fa' => false,
            ],
        ]);

        $this->assertTrue($user->fresh()->hasMenuAccess('dashboard', 'can_view'));
    }
}
