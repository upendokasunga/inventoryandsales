<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
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

    public function test_unit_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('units.index'));

        $response->assertStatus(200);
        $response->assertSee('Piece');
    }

    public function test_unit_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('units.create'));

        $response->assertStatus(200);
        $response->assertSee('Short Code');
    }

    public function test_unit_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('units.store'), [
            'name' => 'Dozen',
            'short_code' => 'dz',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', ['name' => 'Dozen']);
    }

    public function test_unit_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('units.store'), [
            'name' => '',
            'short_code' => 'dz',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_unit_can_be_updated(): void
    {
        $unit = Unit::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)->patch(route('units.update', $unit), [
            'name' => 'Updated',
            'short_code' => 'upd',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', ['name' => 'Updated']);
    }

    public function test_unit_can_be_deleted(): void
    {
        $unit = Unit::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('units.destroy', $unit));

        $response->assertRedirect(route('units.index'));
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }
}
