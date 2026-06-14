<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::orderBy('module')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('menus.index', compact('menus'));
    }

    public function create(): View
    {
        $modules = Menu::distinct()->pluck('module')->sort()->values();
        return view('menus.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'route' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:50',
            'module' => 'required|string|max:50',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        Menu::create([
            'name' => $validated['name'],
            'route' => $validated['route'] ?? '#',
            'icon' => $validated['icon'] ?? 'circle',
            'module' => $validated['module'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('menus.index')
            ->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu): View
    {
        $modules = Menu::distinct()->pluck('module')->sort()->values();
        return view('menus.edit', compact('menu', 'modules'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'route' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:50',
            'module' => 'required|string|max:50',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $menu->update([
            'name' => $validated['name'],
            'route' => $validated['route'] ?? '#',
            'icon' => $validated['icon'] ?? 'circle',
            'module' => $validated['module'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', $menu->is_active),
        ]);

        return redirect()->route('menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('menus.index')
            ->with('success', 'Menu deleted successfully.');
    }
}
