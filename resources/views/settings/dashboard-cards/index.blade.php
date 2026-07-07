<x-app-layout>
    <x-slot name="header">{{ __('Dashboard Cards') }}</x-slot>
    <x-slot name="headerDescription">Configure which KPI cards appear on the dashboard.</x-slot>
    <x-slot name="headerActions">
        <form action="{{ route('settings.dashboard-cards.reset') }}" method="POST" class="inline" onsubmit="return confirm('Reset all cards to default settings?');">
            @csrf
            <button type="submit" class="erp-btn-secondary">Reset to Defaults</button>
        </form>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <form action="{{ route('settings.dashboard-cards.reorder') }}" method="POST" id="reorder-form">
                    @csrf
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Card</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Order</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">Enabled</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($cards as $card)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $card->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ ucfirst($card->section) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        <input type="number" name="order[{{ $card->key }}]" value="{{ $card->sort_order }}"
                                            class="erp-input w-20 text-center" min="0" max="99">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <form action="{{ route('settings.dashboard-cards.toggle') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="key" value="{{ $card->key }}">
                                            <button type="submit" class="px-3 py-1 rounded-full text-xs font-medium transition
                                                {{ $card->is_enabled ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                                {{ $card->is_enabled ? 'Enabled' : 'Disabled' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-xs text-slate-400 font-mono">{{ $card->key }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No cards configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        @if ($cards->isNotEmpty())
            <div class="mt-6 flex justify-end">
                <button type="submit" form="reorder-form" class="erp-btn-primary">Update Order</button>
            </div>
        @endif
    </div>
</x-app-layout>
