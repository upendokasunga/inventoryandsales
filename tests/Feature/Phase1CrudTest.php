<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApprovalConfiguration;
use App\Models\ApprovalLevel;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GoodsReceipt;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StoreRequest;
use App\Models\StoreRequestItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1CrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Group $superGroup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $this->superGroup = Group::where('is_super_admin', true)->first();
        $this->superGroup->users()->attach($this->admin);
    }

    // ─── Account (Chart of Accounts) ───────────────

    public function test_account_index_is_accessible(): void
    {
        Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset']);

        $response = $this->actingAs($this->admin)->get(route('accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Cash');
    }

    public function test_account_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accounts.create'));

        $response->assertStatus(200);
        $response->assertSee('Account Details');
    }

    public function test_account_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('accounts.store'), [
            'name' => 'Accounts Receivable',
            'code' => '1100',
            'type' => 'asset',
            'category' => 'current',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', ['name' => 'Accounts Receivable']);
    }

    public function test_account_requires_code(): void
    {
        $response = $this->actingAs($this->admin)->post(route('accounts.store'), [
            'name' => 'Test',
            'code' => '',
            'type' => 'asset',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_account_can_be_updated(): void
    {
        $account = Account::create(['name' => 'Old', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);

        $response = $this->actingAs($this->admin)->patch(route('accounts.update', $account), [
            'name' => 'Updated',
            'code' => '1000',
            'type' => 'asset',
            'category' => 'current',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', ['name' => 'Updated']);
    }

    public function test_account_can_be_deleted(): void
    {
        $account = Account::create(['name' => 'Temp', 'code' => '9999', 'type' => 'asset', 'category' => 'current']);

        $response = $this->actingAs($this->admin)->delete(route('accounts.destroy', $account));

        $response->assertRedirect(route('accounts.index'));
        $this->assertSoftDeleted('accounts', ['id' => $account->id]);
    }

    // ─── Warehouse ─────────────────────────────────

    public function test_warehouse_index_is_accessible(): void
    {
        $branch = Branch::factory()->create();
        Warehouse::create(['name' => 'Main Warehouse', 'code' => 'WH-MAIN', 'type' => 'goods', 'branch_id' => $branch->id]);

        $response = $this->actingAs($this->admin)->get(route('warehouses.index'));

        $response->assertStatus(200);
        $response->assertSee('Main Warehouse');
    }

    public function test_warehouse_can_be_created(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('warehouses.store'), [
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'type' => 'goods',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('warehouses.index'));
        $this->assertDatabaseHas('warehouses', ['name' => 'Main Warehouse']);
    }

    // ─── Branch ────────────────────────────────────

    public function test_branch_index_is_accessible(): void
    {
        Branch::create(['name' => 'Head Office', 'code' => 'HQ']);

        $response = $this->actingAs($this->admin)->get(route('branches.index'));

        $response->assertStatus(200);
        $response->assertSee('Head Office');
    }

    public function test_branch_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('branches.store'), [
            'name' => 'Downtown Branch',
            'code' => 'DT',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', ['code' => 'DT']);
    }

    // ─── Store Request ─────────────────────────────

    public function test_store_request_index_is_accessible(): void
    {
        $branch = Branch::factory()->create();
        $warehouse1 = Warehouse::factory()->create(['branch_id' => $branch->id]);
        $warehouse2 = Warehouse::factory()->create(['branch_id' => $branch->id]);
        StoreRequest::create([
            'request_number' => 'SR-001',
            'source_warehouse_id' => $warehouse1->id,
            'destination_warehouse_id' => $warehouse2->id,
            'created_by' => $this->admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('store-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('SR-001');
    }

    public function test_store_request_can_be_created(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_default_sale' => true,
            'is_default_purchase' => true,
        ]);
        $branch = Branch::factory()->create();
        $warehouse1 = Warehouse::factory()->create(['branch_id' => $branch->id]);
        $warehouse2 = Warehouse::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($this->admin)->post(route('store-requests.store'), [
            'source_warehouse_id' => $warehouse1->id,
            'destination_warehouse_id' => $warehouse2->id,
            'reason' => 'Urgent',
            'items' => [
                ['product_id' => $product->id, 'quantity_requested' => 5],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('store_requests', ['reason' => 'Urgent']);
    }

    // ─── Stock Transfer ────────────────────────────

    public function test_stock_transfer_index_is_accessible(): void
    {
        $branch = Branch::factory()->create();
        $from = Warehouse::factory()->create(['branch_id' => $branch->id]);
        $to = Warehouse::factory()->create(['branch_id' => $branch->id]);
        StockTransfer::create([
            'transfer_number' => 'ST-001',
            'source_warehouse_id' => $from->id,
            'destination_warehouse_id' => $to->id,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('stock-transfers.index'));

        $response->assertStatus(200);
        $response->assertSee('ST-001');
    }

    // ─── Expense ───────────────────────────────────

    public function test_expense_index_is_accessible(): void
    {
        $category = ExpenseCategory::create(['name' => 'Utilities']);
        Expense::create([
            'expense_number' => 'EXP-001',
            'expense_category_id' => $category->id,
            'amount' => 150000,
            'description' => 'Electricity bill',
            'expense_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertSee('EXP-001');
    }

    public function test_expense_can_be_created(): void
    {
        $category = ExpenseCategory::create(['name' => 'Transport']);

        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'amount' => 50000,
            'description' => 'Fuel',
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['description' => 'Fuel']);
    }

    // ─── Expense Category ──────────────────────────

    public function test_expense_category_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expense-categories.store'), [
            'name' => 'Office Supplies',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('expense-categories.index'));
        $this->assertDatabaseHas('expense_categories', ['name' => 'Office Supplies']);
    }

    // ─── Approval Configuration ────────────────────

    public function test_approval_configuration_index_is_accessible(): void
    {
        ApprovalConfiguration::firstOrCreate(
            ['module_key' => 'test_module'],
            ['module_name' => 'Test Module', 'approval_level' => 1]
        );

        $response = $this->actingAs($this->admin)->get(route('approval-configurations.index'));

        $response->assertStatus(200);
    }

    public function test_approval_configuration_can_be_created_with_levels(): void
    {
        $group = Group::first();

        $response = $this->actingAs($this->admin)->post(route('approval-configurations.store'), [
            'module_key' => 'expense_test',
            'module_name' => 'Expenses',
            'approval_level' => 1,
            'is_active' => true,
            'levels' => [
                ['level' => 1, 'name' => 'Manager', 'group_id' => $group->id, 'sort_order' => 0],
            ],
        ]);

        $response->assertRedirect(route('approval-configurations.index'));
        $this->assertDatabaseHas('approval_configurations', ['module_key' => 'expense_test']);
        $this->assertDatabaseHas('approval_levels', ['name' => 'Manager']);
    }

    // ─── Journal Entry ─────────────────────────────

    public function test_journal_entry_index_is_accessible(): void
    {
        $je = JournalEntry::create([
            'entry_number' => 'JE-001',
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Opening balance',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('journal-entries.index'));

        $response->assertStatus(200);
        $response->assertSee('JE-001');
    }

    public function test_journal_entry_can_be_created_with_balanced_lines(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);
        $equity = Account::create(['name' => 'Capital', 'code' => '3000', 'type' => 'equity', 'category' => 'owner']);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.store'), [
            'description' => 'Opening entry',
            'entry_date' => now()->format('Y-m-d'),
            'lines' => [
                ['account_id' => $asset->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $equity->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('journal_entries', ['description' => 'Opening entry', 'status' => 'draft']);
    }

    public function test_journal_entry_validates_balanced_books(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);
        $equity = Account::create(['name' => 'Capital', 'code' => '3000', 'type' => 'equity', 'category' => 'owner']);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.store'), [
            'description' => 'Unbalanced entry',
            'entry_date' => now()->format('Y-m-d'),
            'lines' => [
                ['account_id' => $asset->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $equity->id, 'debit' => 0, 'credit' => 50000],
            ],
        ]);

        $response->assertSessionHas('error');
    }

    public function test_journal_entry_validates_debit_credit_exclusivity(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);
        $equity = Account::create(['name' => 'Capital', 'code' => '3000', 'type' => 'equity', 'category' => 'owner']);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.store'), [
            'description' => 'Bad entry',
            'entry_date' => now()->format('Y-m-d'),
            'lines' => [
                ['account_id' => $asset->id, 'debit' => 0, 'credit' => 0],
                ['account_id' => $equity->id, 'debit' => 100000, 'credit' => 100000],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_journal_entry_tabs_filter_correctly(): void
    {
        JournalEntry::create([
            'entry_number' => 'JE-DRAFT',
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Draft entry',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        JournalEntry::create([
            'entry_number' => 'JE-POSTED',
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Posted entry',
            'status' => 'posted',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('journal-entries.index', ['tab' => 'posted']));

        $response->assertStatus(200);
        $response->assertSee('JE-POSTED');
        $response->assertDontSee('JE-DRAFT');
    }

    public function test_journal_entry_can_be_approved(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);
        $equity = Account::create(['name' => 'Capital', 'code' => '3000', 'type' => 'equity', 'category' => 'owner']);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.store'), [
            'description' => 'To approve',
            'entry_date' => now()->format('Y-m-d'),
            'lines' => [
                ['account_id' => $asset->id, 'debit' => 50000, 'credit' => 0],
                ['account_id' => $equity->id, 'debit' => 0, 'credit' => 50000],
            ],
        ]);

        $entry = JournalEntry::where('description', 'To approve')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('draft', $entry->status);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.approve', $entry));
        $response->assertRedirect();
        $entry->refresh();
        $this->assertEquals('posted', $entry->status);
        $this->assertNotNull($entry->approved_by);
        $this->assertNotNull($entry->approved_at);
    }

    public function test_journal_entry_can_be_reversed(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);
        $equity = Account::create(['name' => 'Capital', 'code' => '3000', 'type' => 'equity', 'category' => 'owner']);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-REV',
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'To reverse',
            'status' => 'posted',
            'created_by' => $this->admin->id,
        ]);
        $entry->lines()->create(['account_id' => $asset->id, 'debit' => 50000, 'credit' => 0]);
        $entry->lines()->create(['account_id' => $equity->id, 'debit' => 0, 'credit' => 50000]);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.reverse', $entry));
        $response->assertRedirect();
        $entry->refresh();
        $this->assertEquals('reversed', $entry->status);
        $this->assertNotNull($entry->reversed_by);
        $this->assertNotNull($entry->reversed_at);
    }

    public function test_journal_entry_cannot_approve_unbalanced(): void
    {
        $asset = Account::create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset', 'category' => 'current']);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-UB',
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Unbalanced',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        $entry->lines()->create(['account_id' => $asset->id, 'debit' => 100000, 'credit' => 0]);

        $response = $this->actingAs($this->admin)->post(route('journal-entries.approve', $entry));
        $response->assertSessionHas('error');
        $this->assertEquals('draft', $entry->fresh()->status);
    }

    // ─── Status Tabs (Legacy Pages) ────────────────

    public function test_stock_adjustment_tabs_filter_correctly(): void
    {
        \App\Models\StockAdjustment::create([
            'adjustment_number' => 'ADJ-DRAFT',
            'type' => 'positive',
            'reason' => 'test',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        \App\Models\StockAdjustment::create([
            'adjustment_number' => 'ADJ-COMPLETED',
            'type' => 'positive',
            'reason' => 'test',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('stock-adjustments.index', ['tab' => 'completed']));

        $response->assertStatus(200);
        $response->assertSee('ADJ-COMPLETED');
        $response->assertDontSee('ADJ-DRAFT');
    }

    public function test_purchase_order_tabs_filter_correctly(): void
    {
        $supplier = Supplier::factory()->create();
        \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-DRAFT',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'order_date' => now(),
            'created_by' => $this->admin->id,
        ]);
        \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-SENT',
            'supplier_id' => $supplier->id,
            'status' => 'sent',
            'order_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.index', ['tab' => 'sent']));

        $response->assertStatus(200);
        $response->assertSee('PO-SENT');
        $response->assertDontSee('PO-DRAFT');
    }

    public function test_goods_receipt_tabs_filter_correctly(): void
    {
        $supplier = Supplier::factory()->create();
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-TEST',
            'supplier_id' => $supplier->id,
            'status' => 'sent',
            'order_date' => now(),
            'created_by' => $this->admin->id,
        ]);
        \App\Models\GoodsReceipt::create([
            'receipt_number' => 'GR-DRAFT',
            'purchase_order_id' => $po->id,
            'status' => 'draft',
            'receipt_date' => now(),
            'created_by' => $this->admin->id,
        ]);
        \App\Models\GoodsReceipt::create([
            'receipt_number' => 'GR-DONE',
            'purchase_order_id' => $po->id,
            'status' => 'completed',
            'receipt_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.receipts.index', ['tab' => 'completed']));

        $response->assertStatus(200);
        $response->assertSee('GR-DONE');
        $response->assertDontSee('GR-DRAFT');
    }

    // ─── Product Variants ──────────────────────────

    public function test_product_variant_columns_work(): void
    {
        $parent = Product::factory()->create(['has_variants' => true, 'variant_attributes' => ['size' => 'L', 'color' => 'Red']]);

        $this->assertTrue($parent->has_variants);
        $this->assertEquals(['size' => 'L', 'color' => 'Red'], $parent->variant_attributes);
    }

    public function test_product_variant_scope_filters_parents(): void
    {
        Product::factory()->create(['name' => 'Parent Product', 'parent_product_id' => null]);
        $child = Product::factory()->create(['name' => 'Child Variant']);
        $child->parent_product_id = Product::first()->id;
        $child->save();

        $parents = Product::whereNull('parent_product_id')->get();

        $this->assertCount(1, $parents);
    }

    // ─── Invoice Status Tabs ───────────────────────

    public function test_invoice_tabs_filter_correctly(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        \App\Models\Invoice::create([
            'invoice_number' => 'INV-DRAFT',
            'customer_id' => $customer->id,
            'total' => 1000,
            'status' => 'draft',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'created_by' => $this->admin->id,
        ]);
        \App\Models\Invoice::create([
            'invoice_number' => 'INV-POSTED',
            'customer_id' => $customer->id,
            'total' => 2000,
            'status' => 'posted',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('invoices.index', ['tab' => 'posted']));

        $response->assertStatus(200);
        $response->assertSee('INV-POSTED');
        $response->assertDontSee('INV-DRAFT');
    }

    // ─── Print Routes ──────────────────────────────

    public function test_purchase_order_print_returns_pdf(): void
    {
        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.print', $po));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_journal_entry_print_returns_pdf(): void
    {
        $account = Account::create(['name' => 'Test', 'code' => '9999', 'type' => 'asset', 'category' => 'current']);
        $je = JournalEntry::create([
            'entry_number' => 'JE-001',
            'entry_date' => now(),
            'type' => 'general',
            'description' => 'Test',
            'total_debit' => 1000,
            'total_credit' => 1000,
            'created_by' => $this->admin->id,
        ]);
        $je->lines()->create(['account_id' => $account->id, 'debit' => 1000, 'credit' => 0]);
        $je->lines()->create(['account_id' => $account->id, 'debit' => 0, 'credit' => 1000]);

        $response = $this->actingAs($this->admin)->get(route('journal-entries.print', $je));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_invoice_pdf_returns_pdf(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $invoice = Invoice::create([
            'invoice_number' => 'INV-PDF',
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 1000,
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('invoices.pdf', $invoice));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_sales_order_print_returns_pdf(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $so = SalesOrder::create([
            'so_number' => 'SO-PRINT',
            'customer_id' => $customer->id,
            'order_date' => now(),
            'status' => 'draft',
            'subtotal' => 1000,
            'total' => 1000,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.orders.print', $so));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
