<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    public function test_audit_log_index_is_accessible(): void
    {
        AuditLog::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('created');
    }

    public function test_audit_log_can_be_filtered_by_event(): void
    {
        AuditLog::factory()->create([
            'event' => 'created',
            'user_id' => $this->admin->id,
        ]);
        AuditLog::factory()->create([
            'event' => 'deleted',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.index', ['event' => 'deleted']));

        $response->assertStatus(200);
        $response->assertSee('deleted');
    }

    public function test_audit_logs_are_created_on_model_events(): void
    {
        $this->actingAs($this->admin);

        $group = Group::factory()->create(['name' => 'Audited Group']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Group::class,
            'auditable_id' => $group->id,
            'event' => 'created',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_audit_logs_track_old_and_new_values(): void
    {
        $this->actingAs($this->admin);

        $group = Group::factory()->create(['name' => 'Original']);

        $group->update(['name' => 'Updated']);

        $log = AuditLog::where('auditable_id', $group->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Original', json_encode($log->old_values));
        $this->assertStringContainsString('Updated', json_encode($log->new_values));
    }
}
