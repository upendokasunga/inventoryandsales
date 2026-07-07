<?php

namespace Tests\Feature;

use App\Models\DocumentNumberingConfig;
use App\Models\Group;
use App\Models\User;
use App\Services\DocumentNumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberingConfigTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get(route('settings.document-numbering.index'));

        $response->assertStatus(200);
        $response->assertSee('Document Numbering');
    }

    public function test_index_shows_configs(): void
    {
        DocumentNumberingConfig::create([
            'document_type' => 'test_doc',
            'prefix' => 'TD',
        ]);

        $response = $this->actingAs($this->admin)->get(route('settings.document-numbering.index'));

        $response->assertStatus(200);
        $response->assertSee('TD');
    }

    public function test_update_configs(): void
    {
        $config = DocumentNumberingConfig::where('document_type', 'purchase_order')->first();
        $this->assertNotNull($config);

        $response = $this->actingAs($this->admin)->patch(route('settings.document-numbering.update'), [
            'configs' => [
                [
                    'document_type' => 'purchase_order',
                    'prefix' => 'PO',
                    'separator' => '/',
                    'padding' => 5,
                    'is_active' => true,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $config->refresh();
        $this->assertEquals('/', $config->separator);
        $this->assertEquals(5, $config->padding);
    }

    public function test_generate_number_uses_config(): void
    {
        DocumentNumberingConfig::where('document_type', 'purchase_order')->update([
            'prefix' => 'PO',
            'separator' => '/',
            'padding' => 5,
        ]);

        $service = app(DocumentNumberingService::class);
        $number = $service->generateNumber('purchase_order');

        $this->assertStringStartsWith('PO/', $number);
        $this->assertMatchesRegularExpression('/^PO\/\d{4}\/\d{5}$/', $number);
    }

    public function test_generate_number_fallback_when_no_config(): void
    {
        DocumentNumberingConfig::where('document_type', 'purchase_order')->delete();

        $service = app(DocumentNumberingService::class);
        $number = $service->generateNumber('purchase_order');

        $this->assertStringStartsWith('PO-', $number);
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{6}$/', $number);
    }

    public function test_numbering_is_incremental(): void
    {
        $service = app(DocumentNumberingService::class);

        $num1 = $service->generateNumber('invoice');
        $num2 = $service->generateNumber('invoice');

        preg_match('/(\d+)$/', $num1, $m1);
        preg_match('/(\d+)$/', $num2, $m2);

        $this->assertEquals((int) $m1[1] + 1, (int) $m2[1]);
    }

    public function test_update_config_creates_if_missing(): void
    {
        DocumentNumberingConfig::where('document_type', 'purchase_order')->delete();

        $this->actingAs($this->admin)->patch(route('settings.document-numbering.update'), [
            'configs' => [
                [
                    'document_type' => 'purchase_order',
                    'prefix' => 'PO',
                    'separator' => '-',
                    'padding' => 6,
                    'is_active' => true,
                ],
            ],
        ]);

        $this->assertDatabaseHas('document_numbering_configs', [
            'document_type' => 'purchase_order',
            'prefix' => 'PO',
        ]);
    }
}
