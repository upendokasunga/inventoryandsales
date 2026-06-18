<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PricingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected Unit $unit;
    protected PricingService $pricingService;

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
        $this->pricingService = $this->app->make(PricingService::class);
    }

    public function test_pricing_engine_picks_cheapest_price_list(): void
    {
        $expensive = PriceList::factory()->create(["name" => "Expensive", "is_active" => true]);
        $expensive->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 200,
        ]);

        $cheap = PriceList::factory()->create(["name" => "Cheap", "is_active" => true]);
        $cheap->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 100,
        ]);

        $result = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);

        $this->assertNotNull($result);
        $this->assertEquals(100, $result["price"]);
        $this->assertEquals("Cheap", $result["price_list_name"]);
    }

    public function test_pricing_engine_matches_correct_quantity_tier(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Tiered", "is_active" => true]);
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

        $smallQty = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);
        $this->assertEquals(100, $smallQty["price"]);

        $bulkQty = $this->pricingService->getPrice($this->product->id, $this->unit->id, 10);
        $this->assertEquals(80, $bulkQty["price"]);
    }

    public function test_pricing_engine_ignores_inactive_lists(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Inactive Pricing", "is_active" => false]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 50,
        ]);

        $result = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);

        $this->assertNull($result);
    }

    public function test_pricing_engine_ignores_expired_lists(): void
    {
        $priceList = PriceList::factory()->create([
            "name" => "Expired Pricing",
            "is_active" => true,
            "valid_from" => now()->subDays(10),
            "valid_until" => now()->subDays(1),
        ]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 50,
        ]);

        $result = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);

        $this->assertNull($result);
    }

    public function test_pricing_engine_cache_hit_returns_cached_value(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Cached Price", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 300,
        ]);

        $result1 = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);
        $this->assertEquals(300, $result1["price"]);

        $priceList->items()->first()->updateQuietly(["price" => 999]);

        $result2 = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);
        $this->assertEquals(300, $result2["price"]);
    }

    public function test_pricing_engine_cache_invalidated_on_list_change(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Will Change", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 400,
        ]);

        $result1 = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);
        $this->assertEquals(400, $result1["price"]);

        $priceList->items()->first()->update(["price" => 500]);

        $this->pricingService->invalidateCache();

        $result2 = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1);
        $this->assertEquals(500, $result2["price"]);
    }

    public function test_pricing_engine_uses_version_key(): void
    {
        $version = (int) Cache::get("pricing.cache_version", 1);

        $this->pricingService->invalidateCache();

        $newVersion = (int) Cache::get("pricing.cache_version", 1);

        $this->assertGreaterThan($version, $newVersion);
    }

    public function test_pricing_engine_returns_null_for_no_match(): void
    {
        $result = $this->pricingService->getPrice(99999, 99999, 1);

        $this->assertNull($result);
    }

    public function test_pricing_engine_get_or_fail_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->pricingService->getOrFail(99999, 99999, 1);
    }

    public function test_pricing_engine_handles_large_quantities(): void
    {
        $priceList = PriceList::factory()->create(["name" => "Bulk Tier", "is_active" => true]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "max_quantity" => 999,
            "price" => 100,
        ]);
        $priceList->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1000,
            "max_quantity" => null,
            "price" => 50,
        ]);

        $result = $this->pricingService->getPrice($this->product->id, $this->unit->id, 100000);
        $this->assertNotNull($result);
        $this->assertEquals(50, $result["price"]);
    }

    public function test_pricing_engine_falls_back_to_general_when_no_group_match(): void
    {
        $group = \App\Models\CustomerGroup::factory()->create(["name" => "Test Group"]);
        $general = PriceList::factory()->create(["name" => "General Price", "is_active" => true]);
        $general->items()->create([
            "product_id" => $this->product->id,
            "unit_id" => $this->unit->id,
            "min_quantity" => 1,
            "price" => 100,
        ]);

        $result = $this->pricingService->getPrice($this->product->id, $this->unit->id, 1, $group->id);
        $this->assertNotNull($result);
        $this->assertEquals(100, $result["price"]);
        $this->assertEquals("General Price", $result["price_list_name"]);
    }
}