<?php

namespace Tests\Feature;

use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceListTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected Unit $unit;
    protected CustomerGroup $customerGroup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where("is_super_admin", true)->first();
        $superGroup->users()->attach($this->admin);

        $this->product = Product::factory()->create();
        $this->unit = Unit::factory()->create(["name" => "Piece", "short_code" => "PCS"]);
        $this->customerGroup = CustomerGroup::factory()->create(["name" => "Wholesale"]);
    }

    public function test_price_list_dashboard_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route("price-lists.dashboard"));
        $response->assertStatus(200);
        $response->assertSee("Pricing Dashboard");
    }

    public function test_price_list_index_is_accessible(): void
    {
        PriceList::factory()->create(["name" => "Summer Sale"]);
        $response = $this->actingAs($this->admin)->get(route("price-lists.index"));
        $response->assertStatus(200);
        $response->assertSee("Summer Sale");
    }

    public function test_price_list_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route("price-lists.create"));
        $response->assertStatus(200);
        $response->assertSee("Create Price List");
    }

    public function test_price_list_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route("price-lists.store"), [
            "name" => "Wholesale Pricing 2026",
            "description" => "Standard wholesale prices",
            "customer_group_id" => $this->customerGroup->id,
            "currency" => "TZS",
            "is_active" => true,
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "max_quantity" => null,
                    "price" => 15000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas("price_lists", ["name" => "Wholesale Pricing 2026"]);
        $this->assertDatabaseHas("price_list_items", ["price" => 15000]);
    }

    public function test_price_list_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route("price-lists.store"), [
            "name" => "",
            "currency" => "TZS",
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "price" => 100,
                ],
            ],
        ]);

        $response->assertSessionHasErrors("name");
    }

    public function test_price_list_requires_at_least_one_item(): void
    {
        $response = $this->actingAs($this->admin)->post(route("price-lists.store"), [
            "name" => "Empty List",
            "currency" => "TZS",
            "items" => [],
        ]);

        $response->assertSessionHasErrors("items");
    }

    public function test_price_list_can_be_updated(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Old Pricing"]);

        $response = $this->actingAs($this->admin)->patch(route("price-lists.update", $priceList), [
            "name" => "Updated Pricing",
            "currency" => "TZS",
            "is_active" => true,
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "price" => 20000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas("price_lists", ["name" => "Updated Pricing"]);
    }

    public function test_price_list_can_be_deleted(): void
    {
        $priceList = PriceList::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route("price-lists.destroy", $priceList));

        $response->assertRedirect(route("price-lists.index"));
        $this->assertSoftDeleted($priceList);
    }

    public function test_price_list_show_is_accessible(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Premium List"]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 5000,
        ]);

        $response = $this->actingAs($this->admin)->get(route("price-lists.show", $priceList));

        $response->assertStatus(200);
        $response->assertSee("Premium List");
    }

    public function test_price_list_filters_by_status(): void
    {
        PriceList::factory()->create(["name" => "Active List", "is_active" => true]);
        PriceList::factory()->create(["name" => "Inactive List", "is_active" => false]);

        $response = $this->actingAs($this->admin)->get(route("price-lists.index", ["status" => "inactive"]));

        $response->assertStatus(200);
        $response->assertSee("Inactive List");
    }

    public function test_price_list_filters_by_customer_group(): void
    {
        $priceList = PriceList::factory()->create([
            "name" => "Group Specific",
            "customer_group_id" => $this->customerGroup->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route("price-lists.index", [
            "customer_group_id" => $this->customerGroup->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee("Group Specific");
    }

    public function test_price_list_audit_log_is_created(): void
    {
        $this->actingAs($this->admin)->post(route("price-lists.store"), [
            "name" => "Audited List",
            "currency" => "TZS",
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "price" => 100,
                ],
            ],
        ]);

        $priceList = PriceList::where("name", "Audited List")->first();

        $this->assertDatabaseHas("audit_logs", [
            "auditable_type" => PriceList::class,
            "auditable_id" => $priceList->id,
            "event" => "created",
        ]);
    }

    public function test_non_admin_cannot_access_price_lists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route("price-lists.index"));

        $response->assertStatus(403);
    }

    public function test_price_list_rejects_overlapping_tiers(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Overlap Test"]);

        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "max_quantity" => 10,
            "price" => 100,
        ]);

        $response = $this->actingAs($this->admin)->patch(route("price-lists.update", $priceList), [
            "name" => "Overlap Test Updated",
            "currency" => "TZS",
            "is_active" => true,
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 5,
                    "max_quantity" => 15,
                    "price" => 80,
                ],
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "max_quantity" => 10,
                    "price" => 100,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
    }

    public function test_price_list_accepts_non_overlapping_tiers(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Non Overlap Test"]);

        $response = $this->actingAs($this->admin)->patch(route("price-lists.update", $priceList), [
            "name" => "Non Overlap",
            "currency" => "TZS",
            "is_active" => true,
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "max_quantity" => 9,
                    "price" => 100,
                ],
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 10,
                    "max_quantity" => null,
                    "price" => 80,
                ],
            ],
        ]);

        $response->assertRedirect();
    }

    public function test_price_list_cache_invalidated_on_create(): void
    {
        $versionBefore = (int) \Illuminate\Support\Facades\Cache::get("pricing.cache_version", 1);

        $this->actingAs($this->admin)->post(route("price-lists.store"), [
            "name" => "Cache Test",
            "currency" => "TZS",
            "items" => [
                [
                    "product_id" => $this->product->id,
                    "unit_id" => $this->unit->id,
                    "min_quantity" => 1,
                    "price" => 100,
                ],
            ],
        ]);

        $versionAfter = (int) \Illuminate\Support\Facades\Cache::get("pricing.cache_version", 1);

        $this->assertGreaterThan($versionBefore, $versionAfter);
    }
}
