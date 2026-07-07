<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::with('parent')
            ->whereNull('parent_id')
            ->with('children')
            ->latest()
            ->paginate(20);
        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $parents = Account::where('is_active', true)->orderBy('code')->get();
        return view('accounts.create', compact('parents'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        Account::create($request->validated());
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
        $account->update($request->validated());
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
}
