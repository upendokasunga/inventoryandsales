<?php

namespace Tests\Feature;

use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerGroupTest extends TestCase
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

    public function test_customer_group_index_is_accessible(): void
    {
        CustomerGroup::factory()->create(['name' => 'Wholesale']);

        $response = $this->actingAs($this->admin)->get(route('customer-groups.index'));

        $response->assertStatus(200);
        $response->assertSee('Wholesale');
    }

    public function test_customer_group_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customer-groups.create'));

        $response->assertStatus(200);
        $response->assertSee('Credit Limit');
    }

    public function test_customer_group_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customer-groups.store'), [
            'name' => 'Retail',
            'description' => 'Retail customers',
            'default_credit_limit' => 5000,
            'default_payment_terms' => 'Net 30',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('customer-groups.index'));
        $this->assertDatabaseHas('customer_groups', ['name' => 'Retail']);
    }

    public function test_customer_group_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customer-groups.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_customer_group_can_be_updated(): void
    {
        $group = CustomerGroup::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)->patch(route('customer-groups.update', $group), [
            'name' => 'Updated',
            'default_credit_limit' => 10000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('customer-groups.index'));
        $this->assertDatabaseHas('customer_groups', ['name' => 'Updated']);
    }

    public function test_customer_group_can_be_deleted(): void
    {
        $group = CustomerGroup::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('customer-groups.destroy', $group));

        $response->assertRedirect(route('customer-groups.index'));
        $this->assertSoftDeleted('customer_groups', ['id' => $group->id]);
    }
}
