<x-app-layout>
    <x-slot name="header">
        {{ __('Settings') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PATCH')
            <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100">
                <div class="p-6">
                    @if ($settings->isEmpty())
                        <p class="text-sm text-slate-500">No settings configured yet.</p>
                    @else
                        <table class="min-w-full divide-y divide-blue-100">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Key</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50">
                                @foreach ($settings as $setting)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 font-mono">{{ $setting->key }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            <input type="hidden" name="settings[{{ $loop->index }}][key]" value="{{ $setting->key }}">
                                            <input type="hidden" name="settings[{{ $loop->index }}][type]" value="{{ $setting->type }}">
                                            @if ($setting->type === 'boolean')
                                                <select name="settings[{{ $loop->index }}][value]" {{ $setting->is_editable ? '' : 'disabled' }}
                                                    class="rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="1" {{ $setting->value === '1' ? 'selected' : '' }}>True</option>
                                                    <option value="0" {{ $setting->value === '0' ? 'selected' : '' }}>False</option>
                                                </select>
                                            @else
                                                <input type="text" name="settings[{{ $loop->index }}][value]" value="{{ $setting->value }}" {{ $setting->is_editable ? '' : 'disabled' }}
                                                    class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 border border-transparent rounded-lg font-semibold text-xs text-white shadow-lg shadow-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">Save Settings</button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
