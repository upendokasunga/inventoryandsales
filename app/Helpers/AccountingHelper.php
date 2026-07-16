<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JournalEntryAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountingHelper
{
    /**
     * Post a journal entry to the general ledger.
     *
     * @param array{date: string, description: string|null, lines: array, type: string, is_adjustment: bool, tags: array|null, module: string|null, reference: string|null} $data
     */
    public static function postToGL(array $data): JournalEntry
    {
        $lines = $data['lines'];
        $type = $data['type'] ?? 'general';
        $isAdjustment = $data['is_adjustment'] ?? false;

        $sumDebit = 0;
        $sumCredit = 0;
        $hasAmount = false;

        foreach ($lines as $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'lines' => 'A line cannot have both debit and credit.',
                ]);
            }

            if ($debit > 0 || $credit > 0) {
                $hasAmount = true;
            }

            $sumDebit += $debit;
            $sumCredit += $credit;
        }

        if (!$hasAmount || round($sumDebit, 2) !== round($sumCredit, 2)) {
            throw ValidationException::withMessages([
                'lines' => 'Entry must be balanced (total debit equals total credit).',
            ]);
        }

        $asOf = $data['date'] ?? now()->toDateString();
        self::validateCashBankBalances($lines, $asOf);

        $reference = $data['reference'] ?? 'JE-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));

        $tags = $data['tags'] ?? [];
        if (!empty($data['module'])) {
            $tags[] = $data['module'];
        }

        $entry = DB::transaction(function () use ($data, $lines, $type, $isAdjustment, $reference, $tags, $sumDebit, $sumCredit) {
            $status = $isAdjustment ? 'submitted' : 'posted';

            $entry = JournalEntry::create([
                'entry_number' => $reference,
                'entry_date' => $data['date'] ?? now()->toDateString(),
                'type' => $type,
                'is_adjustment' => $isAdjustment,
                'status' => $status,
                'description' => $data['description'] ?? null,
                'total_debit' => $sumDebit,
                'total_credit' => $sumCredit,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'created_by' => Auth::id(),
                'submitted_by' => $isAdjustment ? Auth::id() : null,
                'tags' => $tags,
            ]);

            JournalLineObserver::bypassBalanceCheck(false);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'balance_sheet_item_id' => $line['balance_sheet_item_id'] ?? null,
                    'project_id' => $line['project_id'] ?? null,
                    'branch_id' => $line['branch_id'] ?? null,
                ]);
            }

            $entry->audits()->create([
                'action' => 'created',
                'performed_by' => Auth::id(),
                'metadata' => json_encode(['reference' => $reference, 'type' => $type]),
            ]);

            if (!$isAdjustment) {
                self::recalculateBalances($entry);
                $entry->audits()->create([
                    'action' => 'posted',
                    'performed_by' => Auth::id(),
                    'metadata' => json_encode(['total_debit' => $sumDebit, 'total_credit' => $sumCredit]),
                ]);
            }

            return $entry;
        });

        return $entry;
    }

    /**
     * Compute account balance as of a specific date from journal lines.
     */
    public static function accountBalanceAsOf(int $accountId, string $asOfDate = null): float
    {
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where(function ($q) {
                $q->where('journal_entries.status', 'posted')
                  ->orWhere('journal_entries.status', 'approved');
            })
            ->where('journal_entries.deleted_at', null);

        if ($asOfDate) {
            $query->where('journal_entries.entry_date', '<=', $asOfDate);
        }

        $result = $query->selectRaw('COALESCE(SUM(debit - credit), 0) as balance')->first();

        return (float) $result->balance;
    }

    /**
     * Get the allowed minimum balance for an account, respecting overdraft settings.
     */
    public static function allowedMinimumBalance(int $accountId): float
    {
        $account = Account::find($accountId);

        if (!$account) {
            return 0;
        }

        if ($account->allow_overdraft) {
            return -$account->overdraft_limit;
        }

        return 0;
    }

    /**
     * Check if an account is a cash or bank account using multi-layer heuristic.
     */
    public static function isCashOrBankAccount(Account $account): bool
    {
        if (in_array($account->ifrs_category, ['cash', 'bank'])) {
            return true;
        }

        if (in_array($account->category, ['cash', 'bank'])) {
            return true;
        }

        $name = strtolower($account->name);
        if (str_contains($name, 'cash') || str_contains($name, 'bank')
            || str_contains($name, 'petty') || str_contains($name, 'till')) {
            return true;
        }

        if ($account->bank_name || $account->bank_branch) {
            return true;
        }

        return false;
    }

    /**
     * Validate that cash/bank accounts have sufficient balance for credit lines.
     */
    public static function validateCashBankBalances(array $lines, string $asOfDate): void
    {
        $creditsByAccount = [];

        foreach ($lines as $line) {
            $credit = (float) ($line['credit'] ?? 0);
            if ($credit > 0) {
                $accountId = (int) $line['account_id'];
                $creditsByAccount[$accountId] = ($creditsByAccount[$accountId] ?? 0) + $credit;
            }
        }

        foreach ($creditsByAccount as $accountId => $amtCredit) {
            $account = Account::find($accountId);
            if (!$account || !self::isCashOrBankAccount($account)) {
                continue;
            }

            $bal = self::accountBalanceAsOf($accountId, $asOfDate);
            $allowedMin = self::allowedMinimumBalance($accountId);

            if (round($bal - $amtCredit, 2) < $allowedMin - 0.0001) {
                throw ValidationException::withMessages([
                    'account' => "Insufficient balance on account '{$account->name}'. " .
                        "Available: " . number_format($bal, 2) . ", Required credit: " . number_format($amtCredit, 2) .
                        ", Minimum allowed: " . number_format($allowedMin, 2),
                ]);
            }
        }
    }

    /**
     * Recalculate and update current_balance for all accounts affected by a journal entry.
     */
    public static function recalculateBalances(JournalEntry $entry): void
    {
        $accountIds = $entry->lines()->pluck('account_id')->unique();

        foreach ($accountIds as $accountId) {
            $balance = self::accountBalanceAsOf($accountId);
            Account::where('id', $accountId)->update(['current_balance' => round($balance, 2)]);
        }
    }

    /**
     * Batch recalculate balances for multiple accounts.
     */
    public static function recalculateBalancesForAccounts(array $accountIds): void
    {
        $balances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('journal_entry_lines.account_id', $accountIds)
            ->where(function ($q) {
                $q->where('journal_entries.status', 'posted')
                  ->orWhere('journal_entries.status', 'approved');
            })
            ->where('journal_entries.deleted_at', null)
            ->select('account_id', DB::raw('ROUND(COALESCE(SUM(debit - credit), 0), 2) as bal'))
            ->groupBy('account_id')
            ->pluck('bal', 'account_id');

        foreach ($accountIds as $accountId) {
            Account::where('id', $accountId)->update([
                'current_balance' => $balances->get($accountId, 0),
            ]);
        }
    }

    /**
     * Generate next auto-increment account code.
     */
    public static function generateAccountCode(): string
    {
        $maxCode = Account::selectRaw('MAX(CAST(code AS UNSIGNED)) as max_code')->value('max_code');

        return (string) (($maxCode ?? 999) + 1);
    }
}
