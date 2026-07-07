<x-app-layout>
    <x-slot name="header">{{ __('Dashboard') }}</x-slot>
    <x-slot name="headerDescription">Welcome back! Here's what's happening with your business today.</x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if(in_array('total_products', $enabledCards ?? []))
        <x-stats-card
            title="Total Products"
            :value="$stats['total_products'] ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
        />
        @endif
        @if(in_array('today_sales', $enabledCards ?? []))
        <x-stats-card
            title="Today's Sales"
            :value="'TSh ' . number_format($stats['today_sales'] ?? 0)"
            color="success"
            :trend="$stats['sales_change'] ?? null"
            trendLabel="from yesterday"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        @endif
        @if(in_array('monthly_revenue', $enabledCards ?? []))
        <x-stats-card
            title="Monthly Revenue"
            :value="'TSh ' . number_format($stats['monthly_revenue'] ?? 0)"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
        />
        @endif
        @if(in_array('low_stock', $enabledCards ?? []))
        <x-stats-card
            title="Low Stock Items"
            :value="$stats['low_stock'] ?? 0"
            color="danger"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>'
        />
        @endif
    </div>

    {{-- Analytics Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 erp-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-800">Sales Analytics</h3>
                <select class="erp-input text-xs py-1 pr-8 w-auto">
                    <option>This Week</option>
                    <option selected>This Month</option>
                    <option>This Year</option>
                </select>
            </div>
            <div class="relative" style="height: 280px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="erp-card">
            <h3 class="text-lg font-semibold text-slate-800 mb-6">Product Categories</h3>
            <div class="relative" style="height: 240px;">
                <canvas id="categoriesChart"></canvas>
            </div>
            <div class="mt-4 space-y-2" id="categoryLegend"></div>
        </div>
    </div>

    {{-- Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="erp-card">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Recent Activity
            </h3>
            <div class="space-y-3">
                @forelse ($recentActivities ?? [] as $log)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-1.5
                            {{ $log->action === 'created' ? 'bg-success' : '' }}
                            {{ $log->action === 'updated' ? 'bg-primary' : '' }}
                            {{ $log->action === 'deleted' ? 'bg-danger' : '' }}
                            {{ !in_array($log->action, ['created','updated','deleted']) ? 'bg-slate-300' : '' }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-slate-600 truncate">
                                <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                                {{ $log->action }}
                                <span class="text-slate-400">{{ class_basename($log->auditable_type) }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">No recent activity</p>
                @endforelse
            </div>
        </div>

        <div class="erp-card">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Inventory Health
            </h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600">Stock Level</span>
                        <span class="text-slate-400">{{ $stats['stock_health'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-success rounded-full transition-all" style="width: {{ $stats['stock_health'] ?? 65 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600">Categories</span>
                        <span class="text-slate-400">{{ $stats['total_categories'] ?? 0 }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all" style="width: {{ min(($stats['total_categories'] ?? 0) * 10, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="erp-card">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Credit Exposure
            </h3>
            <p class="text-[32px] font-bold text-slate-800 mb-1">TSh {{ number_format($stats['credit_exposure'] ?? 0) }}</p>
            <p class="text-xs text-slate-400">Total outstanding customer credit</p>
            <p class="text-xs text-slate-500 mt-2">{{ $stats['credit_customers'] ?? 0 }} active credit customers</p>
        </div>

        <div class="erp-card">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Purchase Insights
            </h3>
            <p class="text-[32px] font-bold text-slate-800 mb-1">{{ $stats['pending_purchases'] ?? 0 }}</p>
            <p class="text-xs text-slate-400">Pending purchase orders</p>
            <p class="text-xs text-slate-500 mt-2">{{ $stats['active_suppliers'] ?? 0 }} active suppliers</p>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const primary = '#4F46E5';
            const success = '#22C55E';

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels ?? ['Week 1', 'Week 2', 'Week 3', 'Week 4']) !!},
                    datasets: [{
                        label: 'Sales',
                        data: {!! json_encode($chartSales ?? [65, 78, 55, 90]) !!},
                        borderColor: primary,
                        backgroundColor: 'rgba(79, 70, 229, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: primary,
                        pointRadius: 4,
                    }, {
                        label: 'Revenue',
                        data: {!! json_encode($chartRevenue ?? [45, 62, 48, 75]) !!},
                        borderColor: success,
                        backgroundColor: 'rgba(34, 197, 94, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: success,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });

            const catCtx = document.getElementById('categoriesChart').getContext('2d');
            const catNames = {!! json_encode($categoryNames ?? ['General']) !!};
            const catCounts = {!! json_encode($categoryCounts ?? [1]) !!};
            const colors = ['#4F46E5', '#22C55E', '#F59E0B', '#EF4444', '#7C3AED', '#EC4899', '#14B8A6', '#F97316'];

            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: catNames,
                    datasets: [{
                        data: catCounts,
                        backgroundColor: colors.slice(0, catNames.length),
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });

            const legend = document.getElementById('categoryLegend');
            catNames.forEach((name, i) => {
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between text-xs';
                item.innerHTML = `
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:${colors[i]}"></span>
                        <span class="text-slate-600">${name}</span>
                    </span>
                    <span class="text-slate-400 font-medium">${catCounts[i]}</span>
                `;
                legend.appendChild(item);
            });
        });
    </script>
    @endpush
</x-app-layout>
