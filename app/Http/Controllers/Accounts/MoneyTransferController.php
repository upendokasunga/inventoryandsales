<?php

namespace App\Http\Controllers\Accounts;

use App\Helpers\AccountingHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\MoneyTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MoneyTransferController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $transfers = MoneyTransfer::with(['fromAccount', 'toAccount', 'creator', 'approver'])
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return view('accounts.money-transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $accounts = Account::where('type', 'asset')->where('is_active', true)->orderBy('name')->get();
        return view('accounts.money-transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $fromAccount = Account::findOrFail($validated['from_account_id']);
        $toAccount = Account::findOrFail($validated['to_account_id']);

        if (!AccountingHelper::isCashOrBankAccount($fromAccount) || !AccountingHelper::isCashOrBankAccount($toAccount)) {
            return back()->withErrors(['from_account_id' => 'Both accounts must be cash or bank accounts.']);
        }

        $transfer = MoneyTransfer::create([
            ...$validated,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'reference' => 'MT-' . now()->format('Ymd') . '-' . str_pad(MoneyTransfer::max('id') + 1, 5, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('money-transfers.show', $transfer)->with('success', 'Transfer created. Pending approval.');
    }

    public function show(MoneyTransfer $moneyTransfer): View
    {
        $moneyTransfer->load(['fromAccount', 'toAccount', 'creator', 'approver', 'journalEntry']);
        return view('accounts.money-transfers.show', ['transfer' => $moneyTransfer]);
    }

    public function approve(MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status !== 'pending') {
            return back()->with('error', 'Transfer is not pending.');
        }

        $amount = $moneyTransfer->amount;

        DB::transaction(function () use ($moneyTransfer, $amount) {
            $entry = AccountingHelper::postToGL([
                'date' => now()->toDateString(),
                'description' => "Money transfer from {$moneyTransfer->fromAccount->name} to {$moneyTransfer->toAccount->name}",
                'type' => 'payment',
                'lines' => [
                    ['account_id' => $moneyTransfer->to_account_id, 'debit' => $amount, 'credit' => 0],
                    ['account_id' => $moneyTransfer->from_account_id, 'debit' => 0, 'credit' => $amount],
                ],
                'module' => 'banking',
            ]);

            $moneyTransfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'journal_entry_id' => $entry->id,
            ]);
        });

        return back()->with('success', 'Transfer approved and posted.');
    }

    public function reject(MoneyTransfer $moneyTransfer)
    {
        $moneyTransfer->update(['status' => 'rejected']);
        return back()->with('success', 'Transfer rejected.');
    }

    public function reverse(MoneyTransfer $moneyTransfer)
    {
        $moneyTransfer->update(['status' => 'pending', 'approved_by' => null, 'approved_at' => null]);
        return back()->with('success', 'Transfer moved back to pending.');
    }
}
