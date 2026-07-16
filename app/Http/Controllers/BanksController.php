<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BanksController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $banks = Bank::query()
            ->when($search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('branch', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('banks.index', compact('banks'));
    }

    public function create(): View
    {
        return view('banks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'currency_code' => 'nullable|string|max:10',
        ]);

        Bank::create($validated);

        return redirect()->route('banks.index')->with('success', 'Bank saved successfully.');
    }

    public function edit(Bank $bank): View
    {
        return view('banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'currency_code' => 'nullable|string|max:10',
        ]);

        $bank->update($validated);

        return redirect()->route('banks.index')->with('success', 'Bank updated.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')->with('success', 'Bank deleted.');
    }
}
