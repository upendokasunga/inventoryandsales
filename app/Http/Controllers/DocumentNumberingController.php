<?php

namespace App\Http\Controllers;

use App\Services\DocumentNumberingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentNumberingController extends Controller
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function index(): View
    {
        $configs = $this->numberingService->getAllConfigs();
        return view('settings.document-numbering.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.document_type' => 'required|string|max:50',
            'configs.*.prefix' => 'required|string|max:20',
            'configs.*.separator' => 'required|string|max:5',
            'configs.*.padding' => 'required|integer|min:2|max:10',
            'configs.*.is_active' => 'boolean',
        ]);

        foreach ($validated['configs'] as $config) {
            $this->numberingService->updateConfig(
                $config['document_type'],
                [
                    'prefix' => $config['prefix'],
                    'separator' => $config['separator'],
                    'padding' => (int) $config['padding'],
                    'is_active' => $config['is_active'] ?? true,
                ]
            );
        }

        return redirect()->route('settings.document-numbering.index')
            ->with('success', 'Document numbering updated.');
    }
}
