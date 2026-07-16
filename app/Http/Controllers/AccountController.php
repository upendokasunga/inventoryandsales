<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Bank;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::with('parent')
            ->orderBy('code')
            ->paginate(50);
        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $parents = Account::where('is_active', true)->orderBy('code')->get();
        return view('accounts.create', compact('parents'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['bank_name']) && !empty($data['bank_branch'])) {
            Bank::firstOrCreate(
                ['name' => $data['bank_name'], 'branch' => $data['bank_branch'], 'swift_code' => $data['bank_swift_code'] ?? ''],
                ['name' => $data['bank_name'], 'branch' => $data['bank_branch'], 'swift_code' => $data['bank_swift_code'] ?? '']
            );
        }

        Account::create($data);
        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function show(Account $account): View
    {
        $account->load('parent', 'children');
        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $parents = Account::where('is_active', true)->where('id', '!=', $account->id)->orderBy('code')->get();
        return view('accounts.edit', compact('account', 'parents'));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['bank_name']) && !empty($data['bank_branch'])) {
            Bank::firstOrCreate(
                ['name' => $data['bank_name'], 'branch' => $data['bank_branch'], 'swift_code' => $data['bank_swift_code'] ?? ''],
                ['name' => $data['bank_name'], 'branch' => $data['bank_branch'], 'swift_code' => $data['bank_swift_code'] ?? '']
            );
        }

        $account->update($data);
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->children()->exists()) {
            return back()->with('error', 'Cannot delete account with child accounts.');
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }

    public function open(): View
    {
        $accountTypes = AccountType::where('is_active', true)->orderBy('display_order')->get();
        return view('accounts.open', compact('accountTypes'));
    }

    public function openStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_type_key' => 'required|string|exists:account_types,key',
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:100',
            'bank_swift_code' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'branch_id' => 'nullable|integer',
            'allow_overdraft' => 'boolean',
            'overdraft_limit' => 'nullable|numeric|min:0',
        ]);

        $accountType = AccountType::where('key', $validated['account_type_key'])->firstOrFail();
        $isBank = $accountType->key === 'asset_bank';
        $isCash = $accountType->key === 'asset_cash';

        if ($isBank) {
            if (empty($validated['bank_name']) || empty($validated['bank_branch'])) {
                return back()->withErrors(['bank_name' => 'Bank name and branch are required for bank accounts.']);
            }
            if (empty($validated['account_number'])) {
                return back()->withErrors(['account_number' => 'Account number is required for bank accounts.']);
            }
        }

        $code = AccountingHelper::generateAccountCode();
        $ifrsCategory = $isBank ? 'bank' : 'cash';

        $accountNumber = $validated['account_number'] ?? null;
        if ($isCash && !$accountNumber) {
            $branchPart = $validated['branch_id'] ?? 'G';
            $seq = str_pad(
                Account::where('ifrs_category', 'cash')->count() + 1,
                3, '0', STR_PAD_LEFT
            );
            $accountNumber = "CASH-{$branchPart}-{$seq}";
        }

        $payload = [
            'code' => $code,
            'name' => $validated['name'],
            'type' => 'asset',
            'ifrs_category' => $ifrsCategory,
            'category' => $accountType->asset_class === 'current' ? 'current_asset' : 'non_current_asset',
            'current_noncurrent' => $accountType->asset_class,
            'account_number' => $accountNumber,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'current_balance' => $validated['opening_balance'] ?? 0,
            'is_active' => true,
            'reportable' => false,
            'description' => $validated['description'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'allow_overdraft' => !empty($validated['allow_overdraft']),
            'overdraft_limit' => $validated['overdraft_limit'] ?? 0,
        ];

        if ($isBank) {
            $payload['bank_name'] = $validated['bank_name'];
            $payload['bank_branch'] = $validated['bank_branch'];
            $payload['bank_swift_code'] = $validated['bank_swift_code'] ?? null;

            Bank::firstOrCreate(
                ['name' => $validated['bank_name'], 'branch' => $validated['bank_branch'], 'swift_code' => $validated['bank_swift_code'] ?? ''],
                ['name' => $validated['bank_name'], 'branch' => $validated['bank_branch'], 'swift_code' => $validated['bank_swift_code'] ?? '']
            );
        }

        try {
            Account::create($payload);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($isCash) {
                $payload['account_number'] = $accountNumber . '-' . strtoupper(\Illuminate\Support\Str::random(4));
                Account::create($payload);
            } else {
                throw $e;
            }
        }

        return redirect()->route('accounts.index')->with('success', 'Account opened successfully.');
    }

    public function balances(): View
    {
        $accounts = Account::where('is_active', true)
            ->where('reportable', true)
            ->orderBy('code')
            ->get();

        return view('accounts.balances', compact('accounts'));
    }

    public function balanceJson(Account $account)
    {
        $balance = AccountingHelper::accountBalanceAsOf($account->id);
        return response()->json(['balance' => $balance]);
    }

    public function bankStatement(Account $account, Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $lines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $account->id)
            ->where('journal_entries.entry_date', '>=', $from)
            ->where('journal_entries.entry_date', '<=', $to)
            ->where(function ($q) {
                $q->where('journal_entries.status', 'posted')
                  ->orWhere('journal_entries.status', 'approved');
            })
            ->where('journal_entries.deleted_at', null)
            ->select('journal_entries.entry_date', 'journal_entries.entry_number', 'journal_entries.description', 'journal_entry_lines.debit', 'journal_entry_lines.credit')
            ->orderBy('journal_entries.entry_date')
            ->get();

        return view('accounts.statement', compact('account', 'lines', 'from', 'to'));
    }
}
