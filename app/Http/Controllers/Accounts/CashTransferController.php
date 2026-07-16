<?php

namespace App\Http\Controllers\Accounts;

use App\Helpers\AccountingHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashTransferController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $transfers = CashTransfer::with(['sourceAccount', 'destinationAccount', 'creator', 'approver'])
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return view('accounts.cash-transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $accounts = Account::where('type', 'asset')->where('is_active', true)->orderBy('name')->get();
        return view('accounts.cash-transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_account_id' => 'required|exists:accounts,id',
            'destination_account_id' => 'required|exists:accounts,id|different:source_account_id',
            'amount' => 'required|numeric|min:0.01',
            'exchange_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $source = Account::findOrFail($validated['source_account_id']);
        $dest = Account::findOrFail($validated['destination_account_id']);

        if (!AccountingHelper::isCashOrBankAccount($source) || !AccountingHelper::isCashOrBankAccount($dest)) {
            return back()->withErrors(['source_account_id' => 'Both accounts must be cash or bank asset accounts.']);
        }

        $rate = $validated['exchange_rate'] ?? 1;
        $convertedAmount = $validated['amount'] * $rate;

        $transfer = CashTransfer::create([
            ...$validated,
            'exchange_rate' => $rate,
            'converted_amount' => $convertedAmount,
            'source_currency' => $source->currency_code,
            'destination_currency' => $dest->currency_code,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'reference' => 'CT-' . now()->format('Ymd') . '-' . str_pad(CashTransfer::max('id') + 1, 5, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('cash-transfers.show', $transfer)->with('success', 'Cash transfer created. Pending approval.');
    }

    public function show(CashTransfer $cashTransfer): View
    {
        $cashTransfer->load(['sourceAccount', 'destinationAccount', 'creator', 'approver', 'journalEntry']);
        return view('accounts.cash-transfers.show', ['transfer' => $cashTransfer]);
    }

    public function approve(CashTransfer $cashTransfer)
    {
        if ($cashTransfer->status !== 'pending') {
            return back()->with('error', 'Transfer is not pending.');
        }

        $source = Account::findOrFail($cashTransfer->source_account_id);
        $dest = Account::findOrFail($cashTransfer->destination_account_id);

        $sourceAmount = $cashTransfer->amount;
        $destAmount = $cashTransfer->converted_amount ?? $cashTransfer->amount;
        $hasFxDiff = abs($sourceAmount - $destAmount) > 0.01;

        DB::transaction(function () use ($cashTransfer, $sourceAmount, $destAmount, $hasFxDiff) {
            $lines = [
                ['account_id' => $cashTransfer->destination_account_id, 'debit' => $destAmount, 'credit' => 0],
                ['account_id' => $cashTransfer->source_account_id, 'debit' => 0, 'credit' => $sourceAmount],
            ];

            if ($hasFxDiff) {
                $fxAccount = Account::where('code', '5800')->first();
                if ($fxAccount) {
                    if ($destAmount > $sourceAmount) {
                        $lines[] = ['account_id' => $fxAccount->id, 'debit' => 0, 'credit' => $destAmount - $sourceAmount];
                    } else {
                        $lines[] = ['account_id' => $fxAccount->id, 'debit' => $sourceAmount - $destAmount, 'credit' => 0];
                    }
                }
            }

            $entry = AccountingHelper::postToGL([
                'date' => now()->toDateString(),
                'description' => "Cash transfer: {$cashTransfer->sourceAccount->name} → {$cashTransfer->destinationAccount->name}",
                'type' => 'payment',
                'lines' => $lines,
                'module' => 'banking',
            ]);

            $cashTransfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'journal_entry_id' => $entry->id,
            ]);
        });

        return back()->with('success', 'Cash transfer approved and posted.');
    }

    public function reject(CashTransfer $cashTransfer)
    {
        $cashTransfer->update(['status' => 'rejected']);
        return back()->with('success', 'Transfer rejected.');
    }
}
