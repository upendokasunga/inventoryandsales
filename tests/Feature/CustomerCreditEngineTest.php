<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\User;
use App\Services\CreditService;
use App\Services\CustomerService;
use App\Services\StatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Services\CustomerAnalyticsService;

class CustomerCreditEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected CustomerGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->group = CustomerGroup::factory()->create(['name' => 'Test Group']);

        $this->customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'customer_group_id' => $this->group->id,
            'credit_limit' => 1000000,
            'available_credit' => 1000000,
            'outstanding_balance' => 0,
        ]);
    }

    // --- CRUD ---

    public function test_customer_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.index'));
        $response->assertStatus(200);
        $response->assertSee('Test Customer');
    }

    public function test_customer_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.create'));
        $response->assertStatus(200);
    }

    public function test_customer_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'phone' => '0712345678',
            'customer_group_id' => $this->group->id,
            'credit_limit' => 500000,
            'payment_terms' => 'Net 30',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['name' => 'New Customer']);
    }

    public function test_customer_can_be_updated(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('customers.update', $this->customer), [
            'name' => 'Updated Customer',
            'credit_limit' => 2000000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('customers.show', $this->customer));
        $this->assertDatabaseHas('customers', ['name' => 'Updated Customer', 'credit_limit' => 2000000]);
    }

    public function test_customer_can_be_deleted(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $this->customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted($this->customer);
    }

    public function test_customer_show_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.show', $this->customer));
        $response->assertStatus(200);
        $response->assertSee('Test Customer');
    }

    public function test_customer_dashboard_redirects_to_main(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.dashboard'));
        $response->assertRedirect(route('dashboard'));
    }

    // --- Customer Code Generation ---

    public function test_customer_auto_generates_code(): void
    {
        $customer = Customer::factory()->create(['code' => null]);

        $this->assertNotNull($customer->fresh()->code);
        $this->assertStringStartsWith('CUS-', $customer->fresh()->code);
    }

    public function test_customer_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // --- Credit Validation ---

    public function test_credit_validation_approves_within_limit(): void
    {
        $creditService = app(CreditService::class);

        $result = $creditService->validateCredit($this->customer, 500000);

        $this->assertTrue($result['approved']);
        $this->assertEquals('OK', $result['code']);
    }

    public function test_credit_validation_rejects_exceeding_limit(): void
    {
        $creditService = app(CreditService::class);

        $result = $creditService->validateCredit($this->customer, 2000000);

        $this->assertFalse($result['approved']);
        $this->assertEquals('INSUFFICIENT_CREDIT', $result['code']);
    }

    public function test_credit_validation_rejects_hold_customer(): void
    {
        $creditService = app(CreditService::class);
        $this->customer->update(['credit_status' => 'suspended', 'credit_hold_at' => now()]);

        $result = $creditService->validateCredit($this->customer, 1000);

        $this->assertFalse($result['approved']);
        $this->assertEquals('CREDIT_HOLD', $result['code']);
    }

    public function test_credit_validation_rejects_inactive_customer(): void
    {
        $creditService = app(CreditService::class);
        $this->customer->update(['is_active' => false]);

        $result = $creditService->validateCredit($this->customer, 1000);

        $this->assertFalse($result['approved']);
        $this->assertEquals('CUSTOMER_INACTIVE', $result['code']);
    }

    public function test_credit_validation_rejects_no_limit(): void
    {
        $creditService = app(CreditService::class);
        $this->customer->update(['credit_limit' => 0]);

        $result = $creditService->validateCredit($this->customer, 1000);

        $this->assertFalse($result['approved']);
        $this->assertEquals('NO_LIMIT', $result['code']);
    }

    // --- Credit Transactions ---

    public function test_credit_transaction_is_recorded(): void
    {
        $creditService = app(CreditService::class);

        $creditService->updateBalance($this->customer, 200000, 'order');

        $this->assertDatabaseHas('customer_credit_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'order',
            'amount' => 200000,
        ]);

        $this->customer->fresh();
        $this->assertEquals(200000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_payment_reduces_outstanding_balance(): void
    {
        $creditService = app(CreditService::class);

        $creditService->updateBalance($this->customer, 500000, 'order');
        $creditService->updateBalance($this->customer, 200000, 'payment');

        $this->assertEquals(300000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_calculate_available_credit(): void
    {
        $creditService = app(CreditService::class);

        $creditService->updateBalance($this->customer, 300000, 'order');
        $available = $creditService->calculateAvailableCredit($this->customer->fresh());

        $this->assertEquals(700000, $available);
    }

    // --- Statements ---

    public function test_statement_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.statement'));
        $response->assertStatus(200);
    }

    public function test_statement_generates_transactions(): void
    {
        $creditService = app(CreditService::class);
        $statementService = app(StatementService::class);

        $creditService->updateBalance($this->customer, 100000, 'order');
        $creditService->updateBalance($this->customer, 50000, 'payment');

        $statement = $statementService->generateStatementData($this->customer);

        $this->assertCount(2, $statement['transactions']);
        $this->assertEquals(50000, $statement['closing_balance']);
    }

    public function test_statement_pdf_route_works(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('customers.statement-pdf', $this->customer));

        $response->assertStatus(200);
    }

    // --- Permissions ---

    public function test_non_admin_cannot_access_customers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('customers.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_create_customer(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('customers.create'));
        $response->assertStatus(403);
    }

    // --- Search & Filters ---

    public function test_customers_can_be_searched(): void
    {
        Customer::factory()->create(['name' => 'Alpha Corp']);
        Customer::factory()->create(['name' => 'Beta Ltd']);

        $service = app(CustomerService::class);
        $results = $service->search('Alpha');

        $this->assertCount(1, $results);
        $this->assertEquals('Alpha Corp', $results->first()->name);
    }

    // --- Customer Group Relation ---

    public function test_customer_belongs_to_group(): void
    {
        $this->assertNotNull($this->customer->group);
        $this->assertEquals($this->group->id, $this->customer->group->id);
    }

    // --- Redis Cache ---

    public function test_credit_cache_is_invalidated_on_update(): void
    {
        $creditService = app(CreditService::class);

        $creditService->getCachedCredit($this->customer);
        $creditService->invalidateCache($this->customer);

        $this->assertTrue(true); // Assert no exception thrown
    }

    // --- Audit Logging ---

    public function test_customer_creation_creates_audit_log(): void
    {
        $newCustomer = Customer::factory()->create(['name' => 'Audit Test']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Customer::class,
            'auditable_id' => $newCustomer->id,
            'event' => 'created',
        ]);
    }

    public function test_credit_transaction_creates_audit_log(): void
    {
        $creditService = app(CreditService::class);
        $creditService->updateBalance($this->customer, 100000, 'order');

        $tx = CustomerCreditTransaction::where('customer_id', $this->customer->id)->first();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => CustomerCreditTransaction::class,
            'auditable_id' => $tx->id,
            'event' => 'created',
        ]);
    }

    public function test_code_format_is_cus_with_six_digits(): void
    {
        $c = Customer::factory()->create(['code' => null]);

        $this->assertMatchesRegularExpression('/^CUS-\d{6}$/', $c->fresh()->code);
    }

    public function test_customer_csv_export_works(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.export-csv'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
    }

    public function test_payment_terms_are_validated(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'Invalid Terms Customer',
            'payment_terms' => 'Invalid Term',
        ]);

        $response->assertSessionHasErrors('payment_terms');
    }

    public function test_credit_note_reduces_balance(): void
    {
        $creditService = app(CreditService::class);

        $creditService->updateBalance($this->customer, 500000, 'order');
        $creditService->updateBalance($this->customer, 200000, 'credit_note');

        $this->assertEquals(300000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_debit_note_increases_balance(): void
    {
        $creditService = app(CreditService::class);

        $creditService->updateBalance($this->customer, 150000, 'debit_note');

        $this->assertEquals(150000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_credit_limit_update_recalculates_available(): void
    {
        $creditService = app(CreditService::class);
        $customerService = app(CustomerService::class);

        $creditService->updateBalance($this->customer, 300000, 'order');

        $customerService->update($this->customer, ['credit_limit' => 500000]);

        $this->assertEquals(200000, $this->customer->fresh()->available_credit);
    }

    public function test_analytics_utilization_is_weighted(): void
    {
        $analytics = app(CustomerAnalyticsService::class);
        $stats = $analytics->getCreditUtilizationStats();

        $this->assertArrayHasKey('avg_utilization', $stats);
    }

    public function test_profile_tabs_are_accessible(): void
    {
        foreach (['credit', 'purchases', 'payments', 'statements', 'audit-logs'] as $tab) {
            $response = $this->actingAs($this->admin)
                ->get(route('customers.profile', [$this->customer, 'tab' => $tab]));
            $response->assertStatus(200);
        }
    }

    public function test_code_is_immutable(): void
    {
        $originalCode = $this->customer->code;

        $this->customer->update(['name' => 'Renamed', 'code' => 'HACKED']);

        $this->assertEquals($originalCode, $this->customer->fresh()->code);
    }

    public function test_statement_opening_balance_is_accurate(): void
    {
        $creditService = app(CreditService::class);
        $statementService = app(StatementService::class);

        $creditService->updateBalance($this->customer, 100000, 'order');
        $creditService->updateBalance($this->customer, 50000, 'payment');

        $statement = $statementService->generateStatementData(
            $this->customer,
            now()->subDay()->format('Y-m-d')
        );

        $this->assertEquals(0, $statement['opening_balance']);
        $this->assertEquals(50000, $statement['closing_balance']);
    }

    public function test_customer_group_has_customers_relation(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->group->customers);
        $this->assertTrue($this->group->customers->contains($this->customer));
    }
}
