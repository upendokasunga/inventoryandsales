<?php

namespace Tests\Feature;

use App\Models\DashboardCardConfig;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCardConfigTest extends TestCase
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

    public function test_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.dashboard-cards.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Cards');
    }

    public function test_index_shows_cards(): void
    {
        DashboardCardConfig::factory()->create(['key' => 'test_card', 'title' => 'Test Card']);

        $response = $this->actingAs($this->admin)->get(route('settings.dashboard-cards.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Card');
    }

    public function test_toggle_card_visibility(): void
    {
        $card = DashboardCardConfig::factory()->create([
            'key' => 'test_toggle',
            'is_enabled' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('settings.dashboard-cards.toggle'), [
            'key' => $card->key,
        ]);

        $response->assertRedirect();
        $this->assertFalse($card->fresh()->is_enabled);
    }

    public function test_reorder_cards(): void
    {
        $card1 = DashboardCardConfig::factory()->create(['key' => 'card_a', 'sort_order' => 0]);
        $card2 = DashboardCardConfig::factory()->create(['key' => 'card_b', 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)->post(route('settings.dashboard-cards.reorder'), [
            'order' => ['card_b', 'card_a'],
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $card2->fresh()->sort_order);
        $this->assertEquals(1, $card1->fresh()->sort_order);
    }

    public function test_reset_to_defaults(): void
    {
        $card = DashboardCardConfig::where('key', 'total_products')->first();
        $card->is_enabled = false;
        $card->save();

        $this->assertFalse($card->fresh()->is_enabled);

        $this->actingAs($this->admin)->post(route('settings.dashboard-cards.reset'));

        $this->assertTrue($card->fresh()->is_enabled);
    }

    public function test_dashboard_respects_card_config(): void
    {
        DashboardCardConfig::where('key', 'total_products')->update(['is_enabled' => false]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Total Products');
    }
}
