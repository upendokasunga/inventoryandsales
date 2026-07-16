<?php

namespace App\Observers;

use App\Helpers\AccountingHelper;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class JournalEntryLineObserver
{
    protected static bool $bypassBalanceCheck = false;

    public static function bypassBalanceCheck(bool $bypass): void
    {
        static::$bypassBalanceCheck = $bypass;
    }

    /**
     * On create: add debit-credit to account balance, check overdraft.
     */
    public function created(JournalEntryLine $line): void
    {
        $entry = $line->journalEntry;

        if (!$entry || !in_array($entry->status, ['posted', 'approved'])) {
            return;
        }

        $delta = (float) $line->debit - (float) $line->credit;

        if ($delta < 0 && !static::$bypassBalanceCheck) {
            $accountId = $line->account_id;
            $account = Account::find($accountId);

            if ($account && AccountingHelper::isCashOrBankAccount($account)) {
                $currentBalance = AccountingHelper::accountBalanceAsOf($accountId);
                $allowedMin = AccountingHelper::allowedMinimumBalance($accountId);
                $newBalance = $currentBalance + $delta;

                if (round($newBalance, 2) < round($allowedMin, 2) - 0.0001) {
                    throw new \RuntimeException(
                        "Insufficient balance on account '{$account->name}'. " .
                        "Current: " . number_format($currentBalance, 2) .
                        ", Trying to credit: " . number_format(abs($delta), 2) .
                        ", Minimum allowed: " . number_format($allowedMin, 2)
                    );
                }
            }
        }

        Account::where('id', $line->account_id)
            ->increment('current_balance', $delta);

        $this->refreshBalanceSheetItem($line);
    }

    /**
     * On update: reverse old delta, apply new delta.
     */
    public function updated(JournalEntryLine $line): void
    {
        $entry = $line->journalEntry;

        if (!$entry || !in_array($entry->status, ['posted', 'approved'])) {
            return;
        }

        $oldDelta = (float) $line->getOriginal('debit') - (float) $line->getOriginal('credit');
        $newDelta = (float) $line->debit - (float) $line->credit;
        $diff = $newDelta - $oldDelta;

        if ($diff != 0) {
            Account::where('id', $line->account_id)
                ->increment('current_balance', $diff);
        }

        $this->refreshBalanceSheetItem($line);
    }

    /**
     * On delete: subtract the delta from account balance.
     */
    public function deleted(JournalEntryLine $line): void
    {
        $entry = $line->journalEntry;

        if (!$entry || !in_array($entry->status, ['posted', 'approved'])) {
            return;
        }

        $delta = (float) $line->debit - (float) $line->credit;

        if ($delta != 0) {
            Account::where('id', $line->account_id)
                ->decrement('current_balance', $delta);
        }
    }

    protected function refreshBalanceSheetItem(JournalEntryLine $line): void
    {
        if (!$line->balance_sheet_item_id) {
            return;
        }

        $newBalance = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.balance_sheet_item_id', $line->balance_sheet_item_id)
            ->where(function ($q) {
                $q->where('journal_entries.status', 'posted')
                  ->orWhere('journal_entries.status', 'approved');
            })
            ->where('journal_entries.deleted_at', null)
            ->sum(DB::raw('debit - credit'));

        DB::table('balance_sheet_items')
            ->where('id', $line->balance_sheet_item_id)
            ->update(['current_balance' => round($newBalance, 2)]);
    }
}
