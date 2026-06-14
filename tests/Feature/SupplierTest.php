<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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

    public function test_supplier_index_is_accessible(): void
    {
        Supplier::factory()->create(['name' => 'Acme Corp']);

        $response = $this->actingAs($this->admin)->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Acme Corp');
    }

    public function test_supplier_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('suppliers.create'));

        $response->assertStatus(200);
        $response->assertSee('Contact Person');
    }

    public function test_supplier_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name' => 'Global Supplies',
            'contact_person' => 'John Doe',
            'email' => 'john@example.com',
            'phone1' => '123456789',
            'city' => 'New York',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Global Supplies']);
    }

    public function test_supplier_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)->patch(route('suppliers.update', $supplier), [
            'name' => 'Updated Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Updated Name']);
    }

    public function test_supplier_can_be_deleted(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted($supplier);
    }

    public function test_supplier_profile_is_accessible(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('suppliers.show', $supplier));

        $response->assertStatus(200);
        $response->assertSee($supplier->name);
    }

    public function test_supplier_search_works(): void
    {
        Supplier::factory()->create(['name' => 'Alpha Corp']);
        Supplier::factory()->create(['name' => 'Beta Inc']);

        $response = $this->actingAs($this->admin)->get(route('suppliers.index', ['search' => 'Alpha']));

        $response->assertStatus(200);
        $response->assertSee('Alpha Corp');
        $response->assertDontSee('Beta Inc');
    }
}
