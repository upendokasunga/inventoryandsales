<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsService $settingsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settingsService = app(SettingsService::class);
    }

    public function test_can_set_and_get_setting(): void
    {
        $this->settingsService->set('app_name', 'My ERP', 'string', 'Application name');

        $this->assertEquals('My ERP', $this->settingsService->get('app_name'));
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $this->assertEquals('default', $this->settingsService->get('non_existent', 'default'));
    }

    public function test_has_checks_existence(): void
    {
        $this->settingsService->set('test_key', 'test_value');

        $this->assertTrue($this->settingsService->has('test_key'));
        $this->assertFalse($this->settingsService->has('non_existent'));
    }

    public function test_can_remove_setting(): void
    {
        $this->settingsService->set('removable', 'value');

        $this->assertTrue($this->settingsService->has('removable'));

        $this->settingsService->remove('removable');

        $this->assertFalse($this->settingsService->has('removable'));
    }

    public function test_cache_is_flushed_on_set(): void
    {
        $this->settingsService->set('cached_key', 'original');
        $this->assertEquals('original', $this->settingsService->get('cached_key'));

        $this->settingsService->set('cached_key', 'updated');
        $this->assertEquals('updated', $this->settingsService->get('cached_key'));
    }

    public function test_boolean_casting(): void
    {
        $this->settingsService->set('feature_enabled', '1', 'boolean');
        $this->assertTrue($this->settingsService->get('feature_enabled'));

        $this->settingsService->set('feature_enabled', '0', 'boolean');
        $this->assertFalse($this->settingsService->get('feature_enabled'));
    }

    public function test_integer_casting(): void
    {
        $this->settingsService->set('pagination', '25', 'integer');

        $this->assertSame(25, $this->settingsService->get('pagination'));
        $this->assertIsInt($this->settingsService->get('pagination'));
    }
}
