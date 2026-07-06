<x-app-layout>
    <x-slot name="header">{{ __('Settings') }}</x-slot>
    <x-slot name="headerDescription">Configure system-wide settings and preferences.</x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PATCH')
            <x-form-section title="System Configuration" description="Manage application settings and feature toggles.">
                @if ($settings->isEmpty())
                    <p class="text-sm text-slate-400">No settings configured yet.</p>
                @else
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Key</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($settings as $setting)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 font-mono">{{ $setting->key }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        <input type="hidden" name="settings[{{ $loop->index }}][key]" value="{{ $setting->key }}">
                                        <input type="hidden" name="settings[{{ $loop->index }}][type]" value="{{ $setting->type }}">
                                        @if ($setting->type === 'boolean')
                                            <select name="settings[{{ $loop->index }}][value]" {{ $setting->is_editable ? '' : 'disabled' }} class="erp-input">
                                                <option value="1" {{ $setting->value === '1' ? 'selected' : '' }}>True</option>
                                                <option value="0" {{ $setting->value === '0' ? 'selected' : '' }}>False</option>
                                            </select>
                                        @else
                                            <input type="text" name="settings[{{ $loop->index }}][value]" value="{{ $setting->value }}" {{ $setting->is_editable ? '' : 'disabled' }} class="erp-input">
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $setting->type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $setting->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-form-section>

            @if ($settings->isNotEmpty())
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="erp-btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Save Settings
                    </button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
