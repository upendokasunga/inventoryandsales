<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankTransaction\StoreBankTransactionRequest;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\BankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankTransactionController extends Controller
{
    public function __construct(
        protected BankingService $bankingService,
    ) {}

    public function index(Request $request, BankAccount $bankAccount): View
    {
        $transactions = $this->bankingService->getTransactionsPaginated($bankAccount, 20, $request->only(['type', 'date_from', 'date_to']));
        return view('bank-transactions.index', compact('bankAccount', 'transactions'));
    }

    public function create(BankAccount $bankAccount): View
    {
        return view('bank-transactions.create', compact('bankAccount'));
    }

    public function store(StoreBankTransactionRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->bankingService->recordTransaction($bankAccount, $request->validated());
        return redirect()->route('bank-accounts.show', $bankAccount)
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(BankTransaction $bankTransaction): View
    {
        $bankTransaction->load(['bankAccount', 'creator']);
        return view('bank-transactions.show', compact('bankTransaction'));
    }
}
