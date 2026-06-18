<x-app-layout>
    <x-slot name="header">
        {{ __('Settings') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PATCH')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60">
                <div class="p-6">
                    @if ($settings->isEmpty())
                        <p class="text-sm text-slate-500">No settings configured yet.</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Key</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($settings as $setting)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 font-mono">{{ $setting->key }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            <input type="hidden" name="settings[{{ $loop->index }}][key]" value="{{ $setting->key }}">
                                            <input type="hidden" name="settings[{{ $loop->index }}][type]" value="{{ $setting->type }}">
                                            @if ($setting->type === 'boolean')
                                                <select name="settings[{{ $loop->index }}][value]" {{ $setting->is_editable ? '' : 'disabled' }}
                                                    class="erp-input">
                                                    <option value="1" {{ $setting->value === '1' ? 'selected' : '' }}>True</option>
                                                    <option value="0" {{ $setting->value === '0' ? 'selected' : '' }}>False</option>
                                                </select>
                                            @else
                                                <input type="text" name="settings[{{ $loop->index }}][value]" value="{{ $setting->value }}" {{ $setting->is_editable ? '' : 'disabled' }}
                                                    class="w-full erp-input">
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $setting->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $setting->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            @if ($settings->isNotEmpty())
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="erp-btn-primary">Save Settings</button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
