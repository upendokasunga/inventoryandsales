<x-app-layout>
    <x-slot name="header">Inventory Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Inventory Reports']]" />

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Products in Stock</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($currentStock['total_products'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Quantity</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($currentStock['total_quantity'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Stock Value</p>
            <p class="text-2xl font-bold text-success">{{ number_format($valuation['total_value'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Low Stock Items</p>
            <p class="text-2xl font-bold text-danger">{{ count($lowStock) }}</p>
        </div>
    </div>

    {{-- Fast/Slow/Dead --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-success mb-3">Fast Moving</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Issued</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($fastMoving as $p)
                        <tr class="hover:bg-slate-50"><td class="py-2.5 text-slate-700">{{ $p['name'] }}</td><td class="py-2.5 text-right text-slate-700">{{ number_format($p['total_issued'] ?? 0) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-warning mb-3">Slow Moving</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Stock</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($slowMoving as $p)
                        <tr class="hover:bg-slate-50"><td class="py-2.5 text-slate-700">{{ $p['name'] }}</td><td class="py-2.5 text-right text-slate-700">{{ number_format($p['current_stock']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-danger mb-3">Dead Stock</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Stock</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($deadStock as $p)
                        <tr class="hover:bg-slate-50"><td class="py-2.5 text-slate-700">{{ $p['name'] }}</td><td class="py-2.5 text-right text-slate-700">{{ number_format($p['current_stock']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Expiry + Low Stock --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Expiry Report</h3>
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div class="p-3 bg-danger-50 rounded-lg text-center"><p class="text-xs text-slate-500">Expired</p><p class="text-lg font-bold text-danger">{{ $expiry['summary']['already_expired'] ?? 0 }}</p></div>
                <div class="p-3 bg-warning-50 rounded-lg text-center"><p class="text-xs text-slate-500">30 Days</p><p class="text-lg font-bold text-warning">{{ $expiry['summary']['expiring_within_30_days'] ?? 0 }}</p></div>
                <div class="p-3 bg-blue-50 rounded-lg text-center"><p class="text-xs text-slate-500">90 Days</p><p class="text-lg font-bold text-blue-600">{{ $expiry['summary']['expiring_within_90_days'] ?? 0 }}</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Qty</th><th class="py-2 font-medium text-right">Expires</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($expiry['batches'] ?? []) as $b)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $b['product']['name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($b['quantity_remaining'] ?? 0) }}</td>
                            <td class="py-2.5 text-right text-{{ (isset($b['expiry_date']) && $b['expiry_date'] < now()->format('Y-m-d')) ? 'danger' : 'warning' }}">{{ $b['expiry_date'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Low Stock / Reorder Candidates</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Stock</th><th class="py-2 font-medium text-right">Reorder At</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($lowStock as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $p['name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right font-medium text-{{ ($p['current_stock'] ?? 0) <= 0 ? 'danger' : 'warning' }}">{{ number_format($p['current_stock'] ?? 0) }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($p['reorder_level'] ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
