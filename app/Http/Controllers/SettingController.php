<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index(): View
    {
        $settings = $this->settingsService->all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:100',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => 'nullable|string|in:string,boolean,integer,float,json',
        ]);

        foreach ($validated['settings'] as $setting) {
            $this->settingsService->set(
                $setting['key'],
                $setting['value'] ?? '',
                $setting['type'] ?? 'string'
            );
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
