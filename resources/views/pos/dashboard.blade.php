<x-app-layout>
    <x-slot name="header">POS Dashboard</x-slot>

    <x-breadcrumbs :items="[['label' => 'POS'], ['label' => 'Dashboard']]" />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Today's Sales</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['today_sales'], 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Invoices Today</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['invoices_issued'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center text-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Payments Received</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['payments_received'], 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center text-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Outstanding Receivables</p>
                    <p class="text-2xl font-bold text-danger mt-1">{{ number_format($stats['outstanding_receivables'], 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-danger/10 flex items-center justify-center text-danger">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('pos.index') }}" class="px-4 py-3 bg-primary text-white text-sm font-medium rounded-lg text-center hover:bg-primary-600 transition">Open POS</a>
                <a href="{{ route('invoices.create') }}" class="px-4 py-3 bg-success text-white text-sm font-medium rounded-lg text-center hover:bg-success-600 transition">New Invoice</a>
                <a href="{{ route('invoices.index') }}" class="px-4 py-3 border border-slate-200 text-slate-700 text-sm font-medium rounded-lg text-center hover:bg-slate-50 transition">View Invoices</a>
                <a href="{{ route('payments.index') }}" class="px-4 py-3 border border-slate-200 text-slate-700 text-sm font-medium rounded-lg text-center hover:bg-slate-50 transition">Payments</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Sales Trends</h3>
            <canvas id="salesTrendChart" height="150"></canvas>
        </div>
    </div>

    @push("scripts")
    <script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4" crossorigin="anonymous"></script>
    <script>
        new Chart(document.getElementById("salesTrendChart"), {
            type: "line",
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    label: "Daily Sales",
                    data: @json($chartData ?? []),
                    borderColor: "#1E4A92",
                    backgroundColor: "rgba(30, 74, 146, 0.1)",
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    </script>
    @endpush
</x-app-layout>
