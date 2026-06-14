<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);
    }

    public function test_dashboard_caches_stats_for_super_admin(): void
    {
        Cache::forget('dashboard.stats.admin');

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Users');
        $response->assertSee('Groups');

        $this->assertNotNull(Cache::get('dashboard.stats.admin'));
    }

    public function test_dashboard_stats_match_database(): void
    {
        $stats = [
            'users' => User::count(),
            'groups' => Group::count(),
            'audit_logs' => AuditLog::count(),
        ];

        $cached = Cache::remember('dashboard.stats.admin', 3600, fn() => $stats);

        $this->assertEquals($stats['users'], $cached['users']);
        $this->assertEquals($stats['groups'], $cached['groups']);
    }

    public function test_non_admin_sees_no_stats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Users');
    }
}
