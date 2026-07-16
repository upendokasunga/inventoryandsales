<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function trialBalance(Request $request): View
    {
        $asOf = $request->input('as_of', now()->toDateString());

        $accounts = Account::where('is_active', true)
            ->where('reportable', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOf) {
                $balance = AccountingHelper::accountBalanceAsOf($account->id, $asOf);
                return [
                    'account' => $account,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            })
            ->filter(fn ($row) => $row['debit'] != 0 || $row['credit'] != 0);

        $totalDebit = $accounts->sum('debit');
        $totalCredit = $accounts->sum('credit');

        return view('financial-reports.trial-balance', compact('accounts', 'asOf', 'totalDebit', 'totalCredit'));
    }

    public function incomeStatement(Request $request): View
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $accounts = Account::where('is_active', true)
            ->where('reportable', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($from, $to) {
                $balance = AccountingHelper::accountBalanceAsOf($account->id, $to);
                $openingBalance = AccountingHelper::accountBalanceAsOf($account->id, Carbon::parse($from)->subDay()->toDateString());
                return [
                    'account' => $account,
                    'amount' => $balance - $openingBalance,
                ];
            });

        $revenue = $accounts->filter(fn ($r) => $r['account']->type === 'income');
        $cogs = $accounts->filter(fn ($r) => $r['account']->type === 'expense' && $r['account']->function_of_expense === 'cogs');
        $adminExpenses = $accounts->filter(fn ($r) => $r['account']->type === 'expense' && $r['account']->function_of_expense === 'admin');
        $sellingExpenses = $accounts->filter(fn ($r) => $r['account']->type === 'expense' && $r['account']->function_of_expense === 'selling');
        $otherIncome = $accounts->filter(fn ($r) => $r['account']->type === 'income' && $r['account']->ifrs_category === 'other_income');

        $totalRevenue = $revenue->sum('amount');
        $totalCOGS = $cogs->sum('amount');
        $grossProfit = $totalRevenue - $totalCOGS;
        $totalAdmin = $adminExpenses->sum('amount');
        $totalSelling = $sellingExpenses->sum('amount');
        $totalOperatingExpenses = $totalAdmin + $totalSelling;
        $operatingIncome = $grossProfit - $totalOperatingExpenses;
        $totalOtherIncome = $otherIncome->sum('amount');
        $netIncome = $operatingIncome + $totalOtherIncome;

        return view('financial-reports.income-statement', compact(
            'from', 'to', 'revenue', 'cogs', 'adminExpenses', 'sellingExpenses', 'otherIncome',
            'totalRevenue', 'totalCOGS', 'grossProfit', 'totalAdmin', 'totalSelling',
            'totalOperatingExpenses', 'operatingIncome', 'totalOtherIncome', 'netIncome'
        ));
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = $request->input('as_of', now()->toDateString());

        $accounts = Account::where('is_active', true)
            ->where('reportable', true)
            ->where('type', '!=', 'income')
            ->where('type', '!=', 'expense')
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOf) {
                $balance = AccountingHelper::accountBalanceAsOf($account->id, $asOf);
                return [
                    'account' => $account,
                    'balance' => $balance,
                ];
            });

        $currentAssets = $accounts->filter(fn ($r) => $r['account']->type === 'asset' && $r['account']->current_noncurrent === 'current');
        $nonCurrentAssets = $accounts->filter(fn ($r) => $r['account']->type === 'asset' && $r['account']->current_noncurrent === 'non_current');
        $currentLiabilities = $accounts->filter(fn ($r) => $r['account']->type === 'liability' && $r['account']->current_noncurrent === 'current');
        $nonCurrentLiabilities = $accounts->filter(fn ($r) => $r['account']->type === 'liability' && $r['account']->current_noncurrent === 'non_current');
        $equity = $accounts->filter(fn ($r) => $r['account']->type === 'equity');

        $totalCurrentAssets = $currentAssets->sum('balance');
        $totalNonCurrentAssets = $nonCurrentAssets->sum('balance');
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $totalCurrentLiabilities = $currentLiabilities->sum('balance');
        $totalNonCurrentLiabilities = $nonCurrentLiabilities->sum('balance');
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        $totalEquity = $equity->sum('balance');
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return view('financial-reports.balance-sheet', compact(
            'asOf', 'currentAssets', 'nonCurrentAssets', 'currentLiabilities', 'nonCurrentLiabilities', 'equity',
            'totalCurrentAssets', 'totalNonCurrentAssets', 'totalAssets',
            'totalCurrentLiabilities', 'totalNonCurrentLiabilities', 'totalLiabilities',
            'totalEquity', 'totalLiabilitiesAndEquity'
        ));
    }

    public function cashFlowStatement(Request $request): View
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $accountId = $request->input('account_id');

        $cashBankAccounts = Account::where('is_active', true)
            ->whereIn('ifrs_category', ['cash', 'bank'])
            ->orderBy('code')
            ->get();

        $account = $accountId
            ? Account::findOrFail($accountId)
            : $cashBankAccounts->first();

        if (!$account) {
            abort(404, 'No active cash or bank accounts found.');
        }

        $openingBalance = AccountingHelper::accountBalanceAsOf($account->id, Carbon::parse($from)->subDay()->toDateString());

        $lines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $account->id)
            ->where('journal_entries.entry_date', '>=', $from)
            ->where('journal_entries.entry_date', '<=', $to)
            ->where(function ($q) {
                $q->where('journal_entries.status', 'posted')
                  ->orWhere('journal_entries.status', 'approved');
            })
            ->whereNull('journal_entries.deleted_at')
            ->select(
                'journal_entries.entry_date',
                'journal_entries.entry_number',
                'journal_entries.description as entry_description',
                'journal_entry_lines.description as line_description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit'
            )
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entry_lines.id')
            ->get();

        $runningBalance = $openingBalance;
        $lines = $lines->map(function ($line) use (&$runningBalance) {
            $runningBalance += $line->debit - $line->credit;
            $line->balance = $runningBalance;
            return $line;
        });

        $closingBalance = $runningBalance;
        $totalCashIn = $lines->sum('debit');
        $totalCashOut = $lines->sum('credit');
        $netCashFlow = $totalCashIn - $totalCashOut;

        return view('financial-reports.cash-flow', compact(
            'from', 'to', 'account', 'cashBankAccounts',
            'openingBalance', 'lines', 'closingBalance',
            'totalCashIn', 'totalCashOut', 'netCashFlow'
        ));
    }

    public function generalLedger(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $accountId = $request->input('account_id');

        $query = JournalEntry::with(['lines.account', 'creator'])
            ->whereIn('status', ['posted', 'approved'])
            ->where('entry_date', '>=', $from)
            ->where('entry_date', '<=', $to)
            ->orderBy('entry_date')
            ->orderBy('id');

        if ($accountId) {
            $query->whereHas('lines', fn ($q) => $q->where('account_id', $accountId));
        }

        $entries = $query->get();
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('financial-reports.general-ledger', compact('entries', 'accounts', 'from', 'to', 'accountId'));
    }

    public function accountStatement(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $accountId = $request->input('account_id');

        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $account = $accountId ? Account::findOrFail($accountId) : $accounts->first();

        if (!$account) {
            abort(404, 'No active accounts found. Please create an account first.');
        }

        $openingBalance = $account ? AccountingHelper::accountBalanceAsOf($account->id, Carbon::parse($from)->subDay()->toDateString()) : 0;

        $lines = $account
            ? DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                ->where('journal_entry_lines.account_id', $account->id)
                ->where('journal_entries.entry_date', '>=', $from)
                ->where('journal_entries.entry_date', '<=', $to)
                ->where(function ($q) {
                    $q->where('journal_entries.status', 'posted')
                      ->orWhere('journal_entries.status', 'approved');
                })
                ->whereNull('journal_entries.deleted_at')
                ->select(
                    'journal_entries.entry_date',
                    'journal_entries.entry_number',
                    'journal_entries.description as entry_description',
                    'journal_entry_lines.description as line_description',
                    'journal_entry_lines.debit',
                    'journal_entry_lines.credit'
                )
                ->orderBy('journal_entries.entry_date')
                ->orderBy('journal_entry_lines.id')
                ->get()
            : collect();

        $runningBalance = $openingBalance;
        $lines = $lines->map(function ($line) use (&$runningBalance) {
            $runningBalance += $line->debit - $line->credit;
            $line->balance = $runningBalance;
            return $line;
        });

        $closingBalance = $runningBalance;

        return view('financial-reports.account-statement', compact(
            'account', 'accounts', 'from', 'to', 'openingBalance', 'lines', 'closingBalance'
        ));
    }
}
