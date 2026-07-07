<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankReconciliation\StoreBankReconciliationRequest;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Services\BankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    public function __construct(
        protected BankingService $bankingService,
    ) {}

    public function index(): View
    {
        $reconciliations = $this->bankingService->getReconciliationsPaginated();
        $stats = $this->bankingService->getReconciliationStats();
        return view('bank-reconciliations.index', compact('reconciliations', 'stats'));
    }

    public function create(): View
    {
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        return view('bank-reconciliations.create', compact('bankAccounts'));
    }

    public function store(StoreBankReconciliationRequest $request): RedirectResponse
    {
        try {
            $reconciliation = $this->bankingService->createReconciliation($request->validated());
            return redirect()->route('bank-reconciliations.show', $reconciliation)
                ->with('success', 'Reconciliation started successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(BankReconciliation $bankReconciliation): View
    {
        $bankReconciliation->load([
            'bankAccount',
            'creator',
            'transactions' => function ($q) {
                $q->with('creator');
            },
        ]);
        return view('bank-reconciliations.show', compact('bankReconciliation'));
    }

    public function complete(BankReconciliation $bankReconciliation): RedirectResponse
    {
        try {
            $this->bankingService->completeReconciliation($bankReconciliation);
            return redirect()->route('bank-reconciliations.show', $bankReconciliation)
                ->with('success', 'Reconciliation completed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(BankReconciliation $bankReconciliation): RedirectResponse
    {
        try {
            $this->bankingService->cancelReconciliation($bankReconciliation);
            return redirect()->route('bank-reconciliations.index')
                ->with('success', 'Reconciliation cancelled.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function match(Request $request, BankReconciliation $bankReconciliation): RedirectResponse
    {
        $request->validate(['transaction_id' => 'required|exists:bank_transactions,id']);
        $transaction = BankTransaction::findOrFail($request->transaction_id);
        $this->bankingService->matchTransaction($bankReconciliation, $transaction);
        return redirect()->route('bank-reconciliations.show', $bankReconciliation)
            ->with('success', 'Transaction matched.');
    }
}
