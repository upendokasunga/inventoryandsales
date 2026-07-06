<x-app-layout>
    <x-slot name="header">{{ __('Customer Dashboard') }}</x-slot>
    <x-slot name="headerDescription">Track customer metrics, credit utilization, and recent activity.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('customers.index') }}" class="erp-btn-secondary">View All Customers</a>
        <a href="{{ route('customers.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Customer
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stats-card
                title="Total Customers"
                :value="number_format($stats['total'])"
                color="primary"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>'
            />
            <x-stats-card
                title="Total Credit Limit"
                :value="number_format($stats['total_credit_limit'], 0)"
                color="success"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
            <x-stats-card
                title="Outstanding"
                :value="number_format($stats['total_outstanding'], 0)"
                color="warning"
                subtitle="Avg utilization: {{ number_format($utilization['avg_utilization'], 1) }}%"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
            <x-stats-card
                title="Available Credit"
                :value="number_format($stats['total_available'], 0)"
                color="info"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
            />
        </div>

        <div class="flex gap-4">
            <a href="{{ route('customers.index') }}" class="erp-btn-primary">View All Customers</a>
            <a href="{{ route('customers.create') }}" class="erp-btn-success">Add Customer</a>
            <a href="{{ route('customers.statement') }}" class="erp-btn-secondary">Generate Statement</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="erp-card">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Recent Customers</h3>
                @forelse ($recentCustomers as $c)
                    <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                        <div>
                            <a href="{{ route('customers.show', $c) }}" class="text-sm font-medium text-primary hover:text-primary/80 transition">{{ $c->name }}</a>
                            <p class="text-xs text-slate-400">{{ $c->code }} &middot; {{ $c->group?->name ?? 'No Group' }}</p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $c->credit_status === 'good' ? 'bg-success-50 text-success-700' : 'bg-warning-50 text-warning-700' }}">{{ ucfirst($c->credit_status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No customers yet.</p>
                @endforelse
            </div>

            <div class="erp-card">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Recent Credit Transactions</h3>
                @forelse ($recentTransactions as $tx)
                    <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $tx['description'] ?? ucfirst($tx['type']) }}</p>
                            <p class="text-xs text-slate-400">{{ $tx['customer']['name'] ?? 'Unknown' }} &middot; {{ \Carbon\Carbon::parse($tx['created_at'])->diffForHumans() }}</p>
                        </div>
                        <span class="text-sm font-medium {{ $tx['amount'] > 0 ? 'text-danger-600' : 'text-success-600' }}">
                            {{ number_format($tx['amount'], 0) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
