<x-app-layout>
    <x-slot name="header">Tax Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Tax Reports']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('reports.tax.pdf', request()->query()) }}" class="px-4 py-2 bg-danger text-white text-sm rounded-lg">PDF</a>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Tax Collected</p>
            <p class="text-2xl font-bold text-success">{{ number_format($taxCollected ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Tax Paid</p>
            <p class="text-2xl font-bold text-danger">{{ number_format($taxPayable ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Net VAT</p>
            <p class="text-2xl font-bold text-primary">{{ number_format(($taxCollected ?? 0) - ($taxPayable ?? 0), 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Sales Invoices</p>
            <p class="text-2xl font-bold text-slate-800">{{ $salesTax['invoice_count'] ?? $salesTax['count'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">VAT Summary</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Period</th><th class="py-2 font-medium text-right">Output VAT</th><th class="py-2 font-medium text-right">Input VAT</th><th class="py-2 font-medium text-right">Net</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($vatSummary['monthly'] ?? $vatSummary) as $v)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $v['month'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-success">{{ number_format($v['output_vat'] ?? $v['sales_tax'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right text-danger">{{ number_format($v['input_vat'] ?? $v['purchase_tax'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right font-medium text-primary">{{ number_format(($v['output_vat'] ?? $v['sales_tax'] ?? 0) - ($v['input_vat'] ?? $v['purchase_tax'] ?? 0), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Tax Collected vs Paid</h3>
            <div class="space-y-4">
                <div class="p-4 bg-success-50 rounded-lg border border-success-100">
                    <p class="text-xs text-slate-500 uppercase">Tax Collected (Sales)</p>
                    <p class="text-xl font-bold text-success">{{ number_format($taxCollected ?? 0, 2) }}</p>
                </div>
                <div class="p-4 bg-danger-50 rounded-lg border border-danger-100">
                    <p class="text-xs text-slate-500 uppercase">Tax Paid (Purchases)</p>
                    <p class="text-xl font-bold text-danger">{{ number_format($taxPayable ?? 0, 2) }}</p>
                </div>
                <div class="p-4 bg-primary-50 rounded-lg border border-primary-100">
                    <p class="text-xs text-slate-500 uppercase">Net VAT Payable</p>
                    <p class="text-xl font-bold text-primary">{{ number_format(max(0, ($taxCollected ?? 0) - ($taxPayable ?? 0)), 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
