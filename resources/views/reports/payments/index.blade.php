<x-app-layout>
    <x-slot name="header">Payment Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Payment Reports']]" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Cash Payments</p>
            <p class="text-2xl font-bold text-success">{{ number_format($cashPayments['total'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Credit Payments</p>
            <p class="text-2xl font-bold text-warning">{{ number_format($creditPayments['total'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Bank Transfers</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($bankTransfers['total'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Mobile Money</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($mobileMoney['total'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Outstanding Receivables</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Invoice</th><th class="py-2 font-medium text-right">Amount</th><th class="py-2 font-medium text-right">Paid</th><th class="py-2 font-medium text-right">Balance</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($outstandingReceivables['items'] ?? $outstandingReceivables) as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $r['invoice_number'] ?? $r['invoice'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($r['total_amount'] ?? $r['amount'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right text-success">{{ number_format($r['paid'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right font-medium text-danger">{{ number_format(($r['total_amount'] ?? $r['amount'] ?? 0) - ($r['paid'] ?? 0), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Payment Trends</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Period</th><th class="py-2 font-medium text-right">Count</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($paymentTrends['monthly'] ?? $paymentTrends) as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $t['month'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $t['count'] ?? $t['payment_count'] ?? 0 }}</td>
                            <td class="py-2.5 text-right font-medium text-primary">{{ number_format($t['total'] ?? $t['total_amount'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
