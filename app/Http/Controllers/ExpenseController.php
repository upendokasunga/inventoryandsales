<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $query = Expense::with('category', 'creator', 'approver');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $expenses = $query->latest()->paginate(20);
        return view('expenses.index', compact('expenses', 'tab'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->where('ifrs_category', 'expense')->orderBy('code')->get();
        return view('expenses.create', compact('categories', 'accounts'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['expense_number'] = 'EXP-' . strtoupper(\Illuminate\Support\Str::random(8));
        $data['created_by'] = auth()->id();

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense): View
    {
        $expense->load('category', 'account', 'creator', 'approver', 'payer');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->where('ifrs_category', 'expense')->orderBy('code')->get();
        return view('expenses.edit', compact('expense', 'categories', 'accounts'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());
        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
