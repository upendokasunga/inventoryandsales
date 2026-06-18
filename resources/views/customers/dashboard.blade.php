<x-app-layout>
    <x-slot name="header">{{ __('Customer Dashboard') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customer Dashboard']]" />

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Customers</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ number_format($stats['total']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex gap-3 text-xs text-slate-500">
                    <span>Active: <strong class="text-success-600">{{ number_format($stats['active']) }}</strong></span>
                    <span>On Hold: <strong class="text-warning-600">{{ number_format($stats['on_hold']) }}</strong></span>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Credit Limit</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ number_format($stats['total_credit_limit'], 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-success-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Outstanding</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ number_format($stats['total_outstanding'], 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-warning-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-xs text-slate-500">Avg utilization: <strong>{{ number_format($utilization['avg_utilization'], 1) }}%</strong></div>
                <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-warning-500 h-1.5 rounded-full" style="width: {{ min(100, $utilization['avg_utilization']) }}%"></div>
                </div>
            </div>
            <div class="erp-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Available Credit</p>
                        <p class="mt-1 text-[32px] font-bold text-slate-800">{{ number_format($stats['total_available'], 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('customers.index') }}" class="erp-btn-primary">View All Customers</a>
            <a href="{{ route('customers.create') }}" class="erp-btn-success">Add Customer</a>
            <a href="{{ route('customers.statement') }}" class="erp-btn-secondary">Generate Statement</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Recent Customers</h2>
                </div>
                <div class="p-6">
                    @forelse ($recentCustomers as $c)
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <div>
                                <a href="{{ route('customers.show', $c) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">{{ $c->name }}</a>
                                <p class="text-xs text-slate-500">{{ $c->code }} &middot; {{ $c->group?->name ?? 'No Group' }}</p>
                            </div>
                            <span class="text-xs {{ $c->credit_status === 'good' ? 'text-success-600' : 'text-warning-600' }}">{{ ucfirst($c->credit_status) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No customers yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Recent Credit Transactions</h2>
                </div>
                <div class="p-6">
                    @forelse ($recentTransactions as $tx)
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $tx['description'] ?? ucfirst($tx['type']) }}</p>
                                <p class="text-xs text-slate-500">{{ $tx['customer']['name'] ?? 'Unknown' }} &middot; {{ \Carbon\Carbon::parse($tx['created_at'])->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-medium {{ $tx['amount'] > 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ number_format($tx['amount'], 0) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No transactions yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
