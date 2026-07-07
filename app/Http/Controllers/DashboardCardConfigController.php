<?php

namespace App\Http\Controllers;

use App\Services\DashboardCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardCardConfigController extends Controller
{
    public function __construct(
        protected DashboardCardService $cardService
    ) {}

    public function index(): View
    {
        $cards = $this->cardService->getAll();
        return view('settings.dashboard-cards.index', compact('cards'));
    }

    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|exists:dashboard_card_configs,key',
        ]);

        $this->cardService->toggle($validated['key']);

        return redirect()->route('settings.dashboard-cards.index')
            ->with('success', 'Card visibility toggled.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'string|exists:dashboard_card_configs,key',
        ]);

        $this->cardService->updateOrder($validated['order']);

        return redirect()->route('settings.dashboard-cards.index')
            ->with('success', 'Cards reordered.');
    }

    public function reset(): RedirectResponse
    {
        $this->cardService->resetDefaults();

        return redirect()->route('settings.dashboard-cards.index')
            ->with('success', 'Dashboard cards reset to defaults.');
    }
}
