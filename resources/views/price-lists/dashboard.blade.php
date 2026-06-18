<x-app-layout>
    <x-slot name="header">{{ __('Pricing Dashboard') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Pricing Dashboard']]" />

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Price Lists</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-success-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Expired</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ $stats['expired'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-warning-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Customer Group Based</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ $stats['with_group'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-4 mb-8">
            <a href="{{ route('price-lists.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Price List</a>
            <a href="{{ route('price-lists.index') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">View All Lists</a>
            <a href="{{ route('price-lists.simulator') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">Open Simulator</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Recent Price Lists</h2>
            </div>
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Currency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Valid Until</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentLists as $list)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('price-lists.show', $list) }}" class="text-blue-600 hover:text-blue-500">{{ $list->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $list->customerGroup?->name ?? 'General' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $list->currency }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($list->isExpired())
                                        <span class="erp-badge-inactive">Expired</span>
                                    @elseif ($list->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $list->valid_until?->format('Y-m-d') ?? '∞' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No price lists yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
