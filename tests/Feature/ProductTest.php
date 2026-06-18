<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Group;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->category = Category::factory()->create(['name' => 'Beverages']);
        $this->unit = Unit::factory()->create(['name' => 'Bottle', 'short_code' => 'BTL']);
    }

    public function test_product_index_is_accessible(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Mineral Water',
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Mineral Water');
    }

    public function test_product_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertSee('Basic Information');
        $response->assertSee('Add Unit');
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => 'Spring Water',
            'description' => 'Pure spring water 500ml',
            'tax_rate' => 0,
            'tax_inclusive' => true,
            'is_active' => true,
            'track_stock' => true,
            'reorder_level' => 10,
            'weight' => 0.5,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                    'purchase_price' => 0.50,
                    'selling_price' => 1.00,
                    'wholesale_price' => 0.80,
                    'bulk_price' => 0.70,
                    'is_default_sale' => true,
                    'is_default_purchase' => true,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Spring Water']);
        $this->assertDatabaseHas('product_units', [
            'conversion_factor' => 1,
            'is_default_sale' => true,
        ]);
    }

    public function test_product_auto_generates_sku(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => 'Juice Pack',
            'tax_rate' => 0,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ]);

        $product = Product::where('name', 'Juice Pack')->first();
        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('B-', $product->sku);
    }

    public function test_product_auto_generates_barcode(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => 'Soda Can',
            'tax_rate' => 0,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ]);

        $product = Product::where('name', 'Soda Can')->first();
        $this->assertNotNull($product->barcode);
        $this->assertNotNull($product->barcode_image);
    }

    public function test_product_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => '',
            'tax_rate' => 0,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_product_requires_at_least_one_unit(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => 'No Unit Product',
            'tax_rate' => 0,
            'units' => [],
        ]);

        $response->assertSessionHasErrors('units');
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Old Product',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('products.update', $product), [
            'category_id' => $this->category->id,
            'name' => 'Updated Product',
            'tax_rate' => 10,
            'tax_inclusive' => true,
            'is_active' => true,
            'track_stock' => true,
            'reorder_level' => 5,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                    'is_default_sale' => true,
                    'is_default_purchase' => true,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Updated Product']);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted($product);
    }

    public function test_product_show_page_is_accessible(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Energy Drink',
        ]);

        ProductUnit::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'selling_price' => 2.50,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Energy Drink');
        $response->assertSee('2.50');
    }

    public function test_product_search_works(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Coffee Beans',
            'sku' => 'CB-000001',
        ]);
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Tea Leaves',
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index', ['search' => 'Coffee']));

        $response->assertStatus(200);
        $response->assertSee('Coffee Beans');
        $response->assertDontSee('Tea Leaves');
    }

    public function test_product_can_be_filtered_by_status(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Active Product',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Inactive Product',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index', ['status' => 'inactive']));

        $response->assertStatus(200);
        $response->assertSee('Inactive Product');
    }

    public function test_product_export_csv(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Export Test',
            'sku' => 'EXP-000001',
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.export-csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_barcode_print_page_is_accessible(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Label Test',
            'barcode' => '20012345678901',
        ]);

        ProductUnit::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'selling_price' => 5.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.print-barcode', $product));

        $response->assertStatus(200);
        $response->assertSee('Label Test');
    }

    public function test_product_audit_log_is_created(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'name' => 'Audited Product',
            'tax_rate' => 0,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ]);

        $product = Product::where('name', 'Audited Product')->first();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'event' => 'created',
        ]);
    }

    public function test_product_update_audit_log_is_created(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Pre Update',
        ]);

        $this->actingAs($this->admin)->patch(route('products.update', $product), [
            'category_id' => $this->category->id,
            'name' => 'Post Update',
            'tax_rate' => 5,
            'tax_inclusive' => true,
            'is_active' => true,
            'track_stock' => true,
            'reorder_level' => 3,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                    'is_default_sale' => true,
                    'is_default_purchase' => true,
                ],
            ],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'event' => 'updated',
        ]);
    }

    public function test_non_admin_cannot_access_products(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertStatus(403);
    }

    public function test_product_units_can_be_updated(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $unit2 = Unit::factory()->create(['name' => 'Carton', 'short_code' => 'CTN']);

        $pu = ProductUnit::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'selling_price' => 1.00,
        ]);

        $this->actingAs($this->admin)->patch(route('products.update', $product), [
            'category_id' => $this->category->id,
            'name' => $product->name,
            'tax_rate' => 0,
            'tax_inclusive' => true,
            'is_active' => true,
            'track_stock' => true,
            'reorder_level' => 0,
            'units' => [
                [
                    'id' => $pu->id,
                    'unit_id' => $this->unit->id,
                    'conversion_factor' => 1,
                    'selling_price' => 2.00,
                    'is_default_sale' => true,
                ],
                [
                    'unit_id' => $unit2->id,
                    'conversion_factor' => 24,
                    'selling_price' => 20.00,
                    'is_default_sale' => false,
                    'is_default_purchase' => true,
                ],
            ],
        ]);

        $this->assertDatabaseHas('product_units', [
            'product_id' => $product->id,
            'unit_id' => $unit2->id,
            'conversion_factor' => 24,
        ]);

        $this->assertDatabaseHas('product_units', [
            'id' => $pu->id,
            'selling_price' => 2.00,
        ]);
    }
}
