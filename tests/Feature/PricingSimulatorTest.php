<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PricingSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Cache::flush();

        $this->admin = User::factory()->create();
        $superGroup = Group::where("is_super_admin", true)->first();
        $superGroup->users()->attach($this->admin);

        $this->product = Product::factory()->create();
        $this->unit = Unit::factory()->create(["name" => "Piece", "short_code" => "PCS"]);
    }

    public function test_simulator_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route("price-lists.simulator"));

        $response->assertStatus(200);
        $response->assertSee("Pricing Simulator");
    }

    public function test_simulator_returns_price_for_exact_match(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Standard Prices", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "max_quantity" => null,
            "price" => 1000,
        ]);

        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "quantity" => 1,
        ]);

        $response->assertStatus(200);
        $response->assertSee("Standard Prices");
        $response->assertSee("1,000.00");
    }

    public function test_simulator_returns_price_for_tier_match(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Tiered Pricing", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "max_quantity" => 9,
            "price" => 100,
        ]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 10,
            "max_quantity" => null,
            "price" => 80,
        ]);

        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "quantity" => 10,
        ]);

        $response->assertStatus(200);
        $response->assertSee("10+");
    }

    public function test_simulator_returns_null_for_no_match(): void
    {
        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "quantity" => 1,
        ]);

        $response->assertStatus(200);
        $response->assertSee("No applicable price list found");
    }

    public function test_simulator_calculates_total_correctly(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Bulk Pricing", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "max_quantity" => null,
            "price" => 500,
        ]);

        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "quantity" => 5,
        ]);

        $response->assertStatus(200);
        $response->assertSee("2,500.00");
    }

    public function test_simulator_requires_product_and_quantity(): void
    {
        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "product_id" => "",
            "unit_id" => "",
            "quantity" => "",
        ]);

        $response->assertSessionHasErrors(["product_id", "unit_id", "quantity"]);
    }

    public function test_simulator_respects_customer_group_pricing(): void
    {
        $group = \App\Models\CustomerGroup::factory()->create(["name" => "VIP"]);
        $generalList = PriceList::factory()->create(["name" => "General", "is_active" => true]);
        $generalList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 200,
        ]);

        $vipList = PriceList::factory()->create(["name" => "VIP Pricing", "is_active" => true, "customer_group_id" => $group->id]);
        $vipList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 150,
        ]);

        $response = $this->actingAs($this->admin)->post(route("price-lists.simulate"), [
            "customer_group_id" => $group->id,
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "quantity" => 1,
        ]);

        $response->assertStatus(200);
        $response->assertSee("VIP Pricing");
        $response->assertSee("150");
    }
}