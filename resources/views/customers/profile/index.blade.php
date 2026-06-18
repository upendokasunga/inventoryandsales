<x-app-layout>
    <x-slot name="header">{{ $customer->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name, 'url' => route('customers.show', $customer)], ['label' => ucfirst($tab)]]" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary-50 flex items-center justify-center text-primary font-bold text-xl">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $customer->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $customer->code }} &middot; {{ $customer->group?->name ?? 'No Group' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="erp-btn-primary">Edit</a>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex gap-6 -mb-px">
                @php
                    $tabs = [
                        'overview' => ['label' => 'Overview', 'route' => route('customers.show', $customer)],
                        'credit' => ['label' => 'Credit', 'route' => route('customers.profile', [$customer, 'tab' => 'credit'])],
                        'purchases' => ['label' => 'Purchases', 'route' => route('customers.profile', [$customer, 'tab' => 'purchases'])],
                        'payments' => ['label' => 'Payments', 'route' => route('customers.profile', [$customer, 'tab' => 'payments'])],
                        'statements' => ['label' => 'Statements', 'route' => route('customers.profile', [$customer, 'tab' => 'statements'])],
                        'audit-logs' => ['label' => 'Audit Logs', 'route' => route('customers.profile', [$customer, 'tab' => 'audit-logs'])],
                    ];
                @endphp
                @foreach ($tabs as $key => $t)
                    <a href="{{ $t['route'] }}"
                       class="pb-3 px-1 text-sm font-medium border-b-2 transition
                              {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Tab content --}}
        @if ($tab === 'overview')
            @include('customers.profile.tabs.overview')
        @elseif ($tab === 'credit')
            @include('customers.profile.tabs.credit')
        @elseif ($tab === 'statements')
            @include('customers.profile.tabs.statements')
        @elseif ($tab === 'purchases')
            @include('customers.profile.tabs.purchases')
        @elseif ($tab === 'payments')
            @include('customers.profile.tabs.payments')
        @elseif ($tab === 'audit-logs')
            @include('customers.profile.tabs.audit-logs')
        @endif
    </div>
</x-app-layout>
