<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;

class BankingService
{
    public function getAccountsPaginated(int $perPage = 20): mixed
    {
        return BankAccount::with(['coaAccount', 'creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function getAccountStats(): array
    {
        return [
            'total_accounts' => BankAccount::count(),
            'active_accounts' => BankAccount::where('is_active', true)->count(),
            'total_balance' => BankAccount::where('is_active', true)->sum('current_balance'),
        ];
    }

    public function createAccount(array $data): BankAccount
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            $data['current_balance'] = $data['opening_balance'] ?? 0;
            return BankAccount::create($data);
        });
    }

    public function updateAccount(BankAccount $account, array $data): BankAccount
    {
        $account->update($data);
        return $account->fresh();
    }

    public function deleteAccount(BankAccount $account): void
    {
        if ($account->transactions()->exists()) {
            throw new \InvalidArgumentException('Cannot delete a bank account with transactions.');
        }
        $account->delete();
    }

    public function getTransactionsPaginated(BankAccount $account, int $perPage = 20, ?array $filters = []): mixed
    {
        $query = $account->transactions()->with('creator');

        if ($type = $filters['type'] ?? null) {
            if ($type === 'deposits') {
                $query->whereIn('type', ['deposit', 'transfer_in']);
            } elseif ($type === 'withdrawals') {
                $query->whereIn('type', ['withdrawal', 'transfer_out']);
            } else {
                $query->where('type', $type);
            }
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        return $query->latest('transaction_date')->paginate($perPage);
    }

    public function recordTransaction(BankAccount $account, array $data): BankTransaction
    {
        return DB::transaction(function () use ($account, $data) {
            $lastTx = $account->transactions()->latest('id')->first();
            $runningBalance = $lastTx ? $lastTx->running_balance : $account->current_balance;

            if (in_array($data['type'], ['deposit', 'transfer_in'])) {
                $runningBalance += $data['amount'];
            } else {
                $runningBalance -= $data['amount'];
            }

            $data['bank_account_id'] = $account->id;
            $data['running_balance'] = $runningBalance;
            $data['created_by'] = auth()->id();

            $tx = BankTransaction::create($data);

            $account->update(['current_balance' => $runningBalance]);

            return $tx;
        });
    }

    public function transfer(BankAccount $fromAccount, BankAccount $toAccount, float $amount, string $description = null): array
    {
        return DB::transaction(function () use ($fromAccount, $toAccount, $amount, $description) {
            $ref = 'TRF-' . strtoupper(\Illuminate\Support\Str::random(8));
            $desc = $description ?? "Transfer to {$toAccount->name}";

            $outflow = $this->recordTransaction($fromAccount, [
                'transaction_date' => now()->format('Y-m-d'),
                'description' => $desc,
                'reference_number' => $ref,
                'type' => 'transfer_out',
                'amount' => $amount,
            ]);

            $inflow = $this->recordTransaction($toAccount, [
                'transaction_date' => now()->format('Y-m-d'),
                'description' => "Transfer from {$fromAccount->name}",
                'reference_number' => $ref,
                'type' => 'transfer_in',
                'amount' => $amount,
            ]);

            return [$outflow, $inflow];
        });
    }

    public function getReconciliationsPaginated(int $perPage = 20): mixed
    {
        return BankReconciliation::with(['bankAccount', 'creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function createReconciliation(array $data): BankReconciliation
    {
        return DB::transaction(function () use ($data) {
            $account = BankAccount::findOrFail($data['bank_account_id']);

            $data['created_by'] = auth()->id();
            $data['opening_balance'] = $account->current_balance;

            $rec = BankReconciliation::create($data);

            $transactions = $account->transactions()
                ->whereBetween('transaction_date', [$data['start_date'], $data['end_date']])
                ->where('reconciled', false)
                ->get();

            $reconciledAmount = 0;
            foreach ($transactions as $tx) {
                BankReconciliationItem::create([
                    'bank_reconciliation_id' => $rec->id,
                    'bank_transaction_id' => $tx->id,
                    'status' => 'unmatched',
                ]);
                $reconciledAmount += $tx->amount;
            }

            $difference = $data['closing_balance'] - $data['opening_balance'] - ($reconciledAmount * ($reconciledAmount >= 0 ? 1 : -1));

            $rec->update(['difference' => $difference]);

            return $rec;
        });
    }

    public function completeReconciliation(BankReconciliation $reconciliation): void
    {
        DB::transaction(function () use ($reconciliation) {
            if ($reconciliation->status !== 'draft') {
                throw new \InvalidArgumentException('Only draft reconciliations can be completed.');
            }

            $items = $reconciliation->items()->where('status', 'matched')->get();

            foreach ($items as $item) {
                BankTransaction::where('id', $item->bank_transaction_id)->update([
                    'reconciled' => true,
                ]);
            }

            $reconciliation->update([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);
        });
    }

    public function cancelReconciliation(BankReconciliation $reconciliation): void
    {
        DB::transaction(function () use ($reconciliation) {
            if ($reconciliation->status !== 'draft') {
                throw new \InvalidArgumentException('Only draft reconciliations can be cancelled.');
            }

            $reconciliation->items()->delete();
            $reconciliation->update(['status' => 'cancelled']);
        });
    }

    public function matchTransaction(BankReconciliation $reconciliation, BankTransaction $transaction): void
    {
        DB::transaction(function () use ($reconciliation, $transaction) {
            $item = BankReconciliationItem::where('bank_reconciliation_id', $reconciliation->id)
                ->where('bank_transaction_id', $transaction->id)
                ->first();

            if ($item) {
                $item->update(['status' => 'matched']);
            }
        });
    }

    public function getReconciliationStats(): array
    {
        return [
            'total_reconciliations' => BankReconciliation::count(),
            'pending_reconciliations' => BankReconciliation::where('status', 'draft')->count(),
            'completed_reconciliations' => BankReconciliation::where('status', 'completed')->count(),
        ];
    }
}
