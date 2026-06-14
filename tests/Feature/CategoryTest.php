<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_category_index_is_accessible(): void
    {
        Category::factory()->create(['name' => 'Electronics']);

        $response = $this->actingAs($this->admin)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Electronics');
    }

    public function test_category_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('categories.create'));

        $response->assertStatus(200);
        $response->assertSee('Parent Category');
    }

    public function test_category_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Beverages',
            'description' => 'All drink items',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Beverages']);
    }

    public function test_category_can_have_parent(): void
    {
        $parent = Category::factory()->create(['name' => 'Food']);

        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Snacks',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Snacks',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_category_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_can_be_updated(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)->patch(route('categories.update', $category), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'New Name']);
    }

    public function test_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted($category);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $parent));

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted($parent);
    }

    public function test_category_search_works(): void
    {
        Category::factory()->create(['name' => 'Electronics']);
        Category::factory()->create(['name' => 'Furniture']);

        $response = $this->actingAs($this->admin)->get(route('categories.index', ['search' => 'Electronics']));

        $response->assertStatus(200);
        $response->assertSee('Electronics');
        $response->assertDontSee('Furniture');
    }

    public function test_category_tree_is_accessible(): void
    {
        Category::factory()->create(['name' => 'Root']);

        $response = $this->actingAs($this->admin)->get(route('categories.tree'));

        $response->assertStatus(200);
        $response->assertSee('Root');
    }
}
