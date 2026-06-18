<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseSuggestion\StorePurchaseSuggestionRequest;
use App\Models\Product;
use App\Models\PurchaseSuggestion;
use App\Services\PurchaseSuggestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseSuggestionController extends Controller
{
    public function __construct(
        protected PurchaseSuggestionService $suggestionService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'product_id']);
        $suggestions = $this->suggestionService->getAllPaginated(20, $filters);
        $stats = $this->suggestionService->getStats();
        $products = Product::active()->pluck('name', 'id');

        return view('purchasing.suggestions.index', compact('suggestions', 'stats', 'products'));
    }

    public function create(): View
    {
        $products = Product::active()->pluck('name', 'id');
        return view('purchasing.suggestions.create', compact('products'));
    }

    public function store(StorePurchaseSuggestionRequest $request): RedirectResponse
    {
        $this->suggestionService->create($request->validated());

        return redirect()->route('purchasing.suggestions.index')
            ->with('success', 'Purchase suggestion created.');
    }

    public function show(PurchaseSuggestion $suggestion): View
    {
        $suggestion->load(['product', 'supplier', 'creator', 'reviewer']);
        return view('purchasing.suggestions.show', compact('suggestion'));
    }

    public function generate(): RedirectResponse
    {
        $count = count($this->suggestionService->generateSuggestions());

        return redirect()->route('purchasing.suggestions.index')
            ->with('success', "{$count} purchase suggestions generated.");
    }

    public function approve(PurchaseSuggestion $suggestion): RedirectResponse
    {
        $this->suggestionService->approve($suggestion);

        return redirect()->route('purchasing.suggestions.index')
            ->with('success', 'Suggestion approved.');
    }

    public function reject(Request $request, PurchaseSuggestion $suggestion): RedirectResponse
    {
        $data = $request->validate(['notes' => 'nullable|string|max:1000']);
        $this->suggestionService->reject($suggestion, $data['notes'] ?? null);

        return redirect()->route('purchasing.suggestions.index')
            ->with('success', 'Suggestion rejected.');
    }
}
