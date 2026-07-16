<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccount\StoreBankAccountRequest;
use App\Http\Requests\BankAccount\UpdateBankAccountRequest;
use App\Models\AccountType;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Services\BankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(
        protected BankingService $bankingService,
    ) {}

    public function index(): View
    {
        $accounts = $this->bankingService->getAccountsPaginated();
        $stats = $this->bankingService->getAccountStats();
        return view('bank-accounts.index', compact('accounts', 'stats'));
    }

    public function create(): View
    {
        $banks = Bank::orderBy('name')->get();
        $accountTypes = AccountType::active()->ordered()->get();
        return view('bank-accounts.create', compact('banks', 'accountTypes'));
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $bankAccount = $this->bankingService->createAccount($request->validated());

        if ($request->ajax()) {
            return response()->json(['id' => $bankAccount->id, 'name' => $bankAccount->name]);
        }

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    public function show(BankAccount $bankAccount): View
    {
        $bankAccount->load(['coaAccount', 'bank', 'accountType', 'creator', 'transactions' => function ($q) {
            $q->latest('transaction_date')->limit(10);
        }]);
        return view('bank-accounts.show', compact('bankAccount'));
    }

    public function edit(BankAccount $bankAccount): View
    {
        $banks = Bank::orderBy('name')->get();
        $accountTypes = AccountType::active()->ordered()->get();
        return view('bank-accounts.edit', compact('bankAccount', 'banks', 'accountTypes'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->bankingService->updateAccount($bankAccount, $request->validated());
        return redirect()->route('bank-accounts.show', $bankAccount)
            ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        try {
            $this->bankingService->deleteAccount($bankAccount);
            return redirect()->route('bank-accounts.index')
                ->with('success', 'Bank account deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
