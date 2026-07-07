<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BankAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->account = BankAccount::factory()->create([
            'current_balance' => 1000000,
        ]);
    }

    public function test_bank_account_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('bank-accounts.index'));
        $response->assertStatus(200);
        $response->assertSee($this->account->name);
    }

    public function test_bank_account_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('bank-accounts.store'), [
            'name' => 'Test Bank Account',
            'account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'account_type' => 'checking',
            'opening_balance' => 100000,
        ]);

        $response->assertRedirect(route('bank-accounts.index'));
        $this->assertDatabaseHas('bank_accounts', [
            'name' => 'Test Bank Account',
            'account_number' => '1234567890',
            'current_balance' => 100000,
        ]);
    }

    public function test_bank_account_show_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('bank-accounts.show', $this->account));
        $response->assertStatus(200);
        $response->assertSee($this->account->name);
    }

    public function test_bank_account_can_be_updated(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('bank-accounts.update', $this->account), [
            'name' => 'Updated Account',
            'account_number' => $this->account->account_number,
            'bank_name' => 'Updated Bank',
            'account_type' => 'savings',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'name' => 'Updated Account',
            'account_type' => 'savings',
        ]);
    }

    public function test_deposit_creates_transaction_and_updates_balance(): void
    {
        $response = $this->actingAs($this->admin)->post(route('bank-transactions.store', $this->account), [
            'type' => 'deposit',
            'amount' => 200000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Customer payment deposit',
        ]);

        $response->assertRedirect();
        $this->account->refresh();
        $this->assertEquals(1200000, $this->account->current_balance);

        $this->assertDatabaseHas('bank_transactions', [
            'bank_account_id' => $this->account->id,
            'type' => 'deposit',
            'amount' => 200000,
            'running_balance' => 1200000,
        ]);
    }

    public function test_withdrawal_creates_transaction_and_updates_balance(): void
    {
        $response = $this->actingAs($this->admin)->post(route('bank-transactions.store', $this->account), [
            'type' => 'withdrawal',
            'amount' => 300000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Utility payment',
        ]);

        $response->assertRedirect();
        $this->account->refresh();
        $this->assertEquals(700000, $this->account->current_balance);

        $this->assertDatabaseHas('bank_transactions', [
            'bank_account_id' => $this->account->id,
            'type' => 'withdrawal',
            'amount' => 300000,
            'running_balance' => 700000,
        ]);
    }

    public function test_reconciliation_can_be_created(): void
    {
        $this->actingAs($this->admin)->post(route('bank-transactions.store', $this->account), [
            'type' => 'deposit',
            'amount' => 500000,
            'transaction_date' => now()->subDays(5)->format('Y-m-d'),
            'description' => 'Test deposit',
        ]);

        $this->actingAs($this->admin)->post(route('bank-transactions.store', $this->account), [
            'type' => 'withdrawal',
            'amount' => 200000,
            'transaction_date' => now()->subDays(3)->format('Y-m-d'),
            'description' => 'Test withdrawal',
        ]);

        $response = $this->actingAs($this->admin)->post(route('bank-reconciliations.store'), [
            'bank_account_id' => $this->account->id,
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'closing_balance' => 1300000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bank_reconciliations', [
            'bank_account_id' => $this->account->id,
            'status' => 'draft',
        ]);

        $reconciliation = BankReconciliation::where('bank_account_id', $this->account->id)->first();
        $this->assertNotNull($reconciliation);
        $this->assertCount(2, $reconciliation->items);
    }

    public function test_reconciliation_can_be_completed(): void
    {
        $tx = $this->actingAs($this->admin)->post(route('bank-transactions.store', $this->account), [
            'type' => 'deposit',
            'amount' => 500000,
            'transaction_date' => now()->subDays(5)->format('Y-m-d'),
            'description' => 'Test deposit',
        ]);

        $this->actingAs($this->admin)->post(route('bank-reconciliations.store'), [
            'bank_account_id' => $this->account->id,
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'closing_balance' => 1500000,
        ]);

        $reconciliation = BankReconciliation::where('bank_account_id', $this->account->id)->first();

        $tx = BankTransaction::where('bank_account_id', $this->account->id)->first();
        $this->actingAs($this->admin)->post(route('bank-reconciliations.match', $reconciliation), [
            'transaction_id' => $tx->id,
        ]);

        $this->actingAs($this->admin)->post(route('bank-reconciliations.complete', $reconciliation));
        $reconciliation->refresh();
        $tx->refresh();

        $this->assertEquals('completed', $reconciliation->status);
        $this->assertTrue($tx->reconciled);
    }

    public function test_reconciliation_can_be_cancelled(): void
    {
        $this->actingAs($this->admin)->post(route('bank-reconciliations.store'), [
            'bank_account_id' => $this->account->id,
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'closing_balance' => 1000000,
        ]);

        $reconciliation = BankReconciliation::where('bank_account_id', $this->account->id)->first();
        $this->actingAs($this->admin)->post(route('bank-reconciliations.cancel', $reconciliation));

        $reconciliation->refresh();
        $this->assertEquals('cancelled', $reconciliation->status);
    }

    public function test_reconciliation_list_filters_status(): void
    {
        BankReconciliation::factory()->count(3)->create([
            'bank_account_id' => $this->account->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('bank-reconciliations.index'));
        $response->assertStatus(200);
    }
}
