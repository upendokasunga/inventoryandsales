<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerReportService
{
    public function getOutstandingDebts(): array
    {
        return Cache::remember('report.customers.outstanding_debts', 3600, function () {
            $customers = Customer::where('credit_status', 'active')
                ->where('credit_used', '>', 0)
                ->orderByDesc('credit_used')
                ->take(1000)->get(['id', 'name', 'email', 'phone', 'credit_limit', 'credit_used'])
                ->toArray();

            $totalOutstanding = array_sum(array_column($customers, 'credit_used'));
            $totalCreditLimit = array_sum(array_column($customers, 'credit_limit'));

            return [
                'total_outstanding' => $totalOutstanding,
                'total_credit_limit' => $totalCreditLimit,
                'utilization_rate' => $totalCreditLimit > 0 ? round(($totalOutstanding / $totalCreditLimit) * 100, 2) : 0,
                'customer_count' => count($customers),
                'customers' => $customers,
            ];
        });
    }

    public function getCustomerStatement(int $customerId, string $startDate, string $endDate): array
    {
        $key = "report.customers.statement.{$customerId}.{$startDate}.{$endDate}";
        return Cache::remember($key, 1800, function () use ($customerId, $startDate, $endDate) {
            $customer = Customer::findOrFail($customerId, ['id', 'name', 'email', 'phone', 'credit_limit', 'credit_used']);

            $invoices = Invoice::where('customer_id', $customerId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->take(1000)->get(['id', 'invoice_number', 'total_amount', 'status', 'payment_status', 'created_at'])
                ->toArray();

            $payments = Payment::whereHas('invoice', fn($q) => $q->where('customer_id', $customerId))
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->orderBy('payment_date')
                ->take(1000)->get(['id', 'invoice_id', 'amount', 'payment_method', 'payment_date', 'reference_number'])
                ->toArray();

            $transactions = CustomerCreditTransaction::where('customer_id', $customerId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->take(1000)->get()
                ->toArray();

            return [
                'customer' => $customer->toArray(),
                'invoices' => $invoices,
                'payments' => $payments,
                'credit_transactions' => $transactions,
                'total_invoiced' => array_sum(array_column($invoices, 'total_amount')),
                'total_paid' => array_sum(array_column($payments, 'amount')),
                'balance' => $customer->credit_used,
            ];
        });
    }

    public function getCreditExposure(): array
    {
        return Cache::remember('report.customers.credit_exposure', 3600, function () {
            $customers = Customer::where('credit_status', 'active')
                ->take(1000)->get(['id', 'name', 'credit_limit', 'credit_used']);

            $totalExposure = $customers->sum('credit_used');
            $totalLimit = $customers->sum('credit_limit');

            $atRisk = $customers->filter(fn($c) => $c->credit_limit > 0 && ($c->credit_used / $c->credit_limit) > 0.8);

            return [
                'total_exposure' => $totalExposure,
                'total_credit_limit' => $totalLimit,
                'overall_utilization' => $totalLimit > 0 ? round(($totalExposure / $totalLimit) * 100, 2) : 0,
                'at_risk_customers' => $atRisk->values()->toArray(),
                'at_risk_count' => $atRisk->count(),
            ];
        });
    }

    public function getPaymentBehaviour(?int $customerId = null): array
    {
        $key = $customerId ? "report.customers.payment_behaviour.{$customerId}" : 'report.customers.payment_behaviour';
        return Cache::remember($key, 3600, function () use ($customerId) {
            $query = Payment::select(
                    'payment_method',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total'),
                );

            if ($customerId) {
                $query->whereHas('invoice', fn($q) => $q->where('customer_id', $customerId));
            }

            $byMethod = $query->groupBy('payment_method')->get()->toArray();

            return [
                'total_payments' => array_sum(array_column($byMethod, 'count')),
                'total_amount' => array_sum(array_column($byMethod, 'total')),
                'by_method' => $byMethod,
            ];
        });
    }

    public function getTopCustomers(int $limit = 20): array
    {
        return Cache::remember("report.customers.top.{$limit}", 3600, function () use ($limit) {
            return Customer::withCount(['invoices as total_purchases' => fn($q) => $q
                ->whereIn('status', ['paid', 'approved'])
                ->select(DB::raw('COALESCE(SUM(total_amount), 0)'))])
                ->withCount('invoices as order_count')
                ->orderByDesc('total_purchases')
                ->limit($limit)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'order_count' => $c->order_count,
                    'total_purchases' => (float) $c->total_purchases,
                    'credit_used' => (float) $c->credit_used,
                    'credit_limit' => (float) ($c->credit_limit ?? 0),
                ])->toArray();
        });
    }

    public function getOverdueCustomers(): array
    {
        return Cache::remember('report.customers.overdue', 1800, function () {
            $overdueInvoices = Invoice::whereIn('status', ['approved', 'overdue'])
                ->where('payment_status', '!=', 'paid')
                ->where('created_at', '<', now()->subDays(30))
                ->with('customer:id,name,email,phone')
                ->take(1000)->get()
                ->groupBy('customer_id');

            return $overdueInvoices->map(fn($invoices, $customerId) => [
                'customer_id' => $customerId,
                'customer_name' => $invoices->first()->customer?->name,
                'customer_email' => $invoices->first()->customer?->email,
                'overdue_count' => $invoices->count(),
                'total_overdue' => $invoices->sum('total_amount'),
                'invoices' => $invoices->map(fn($i) => [
                    'id' => $i->id,
                    'invoice_number' => $i->invoice_number,
                    'total_amount' => $i->total_amount,
                    'created_at' => $i->created_at->format('Y-m-d'),
                    'days_overdue' => now()->diffInDays($i->created_at),
                ])->values()->toArray(),
            ])->values()->toArray();
        });
    }

    public function getCustomerPurchaseTrends(int $customerId, int $months = 12): array
    {
        $key = "report.customers.trends.{$customerId}.{$months}";
        return Cache::remember($key, 3600, function () use ($customerId, $months) {
            $start = now()->subMonths($months);
            return Invoice::where('customer_id', $customerId)
                ->whereIn('status', ['paid', 'approved'])
                ->where('created_at', '>=', $start)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total'),
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();
        });
    }

    public function getCustomerProfitability(int $customerId): array
    {
        $key = "report.customers.profitability.{$customerId}";
        return Cache::remember($key, 3600, function () use ($customerId) {
            $data = Invoice::where('customer_id', $customerId)
                ->whereIn('status', ['paid', 'approved'])
                ->select(
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(discount) as total_discount'),
                )->first();

            $cost = InvoiceItem::whereHas('invoice', fn($q) => $q
                ->where('customer_id', $customerId)
                ->whereIn('status', ['paid', 'approved']))
                ->select(DB::raw('COALESCE(SUM(unit_price * quantity), 0) as total_cost'))
                ->value('total_cost');

            $revenue = (float) ($data->total_revenue ?? 0);

            return [
                'customer_id' => $customerId,
                'order_count' => (int) ($data->order_count ?? 0),
                'total_revenue' => $revenue,
                'total_cost' => (float) $cost,
                'total_discounts' => (float) ($data->total_discount ?? 0),
                'gross_profit' => $revenue - (float) $cost,
                'profit_margin' => $revenue > 0 ? round((($revenue - (float) $cost) / $revenue) * 100, 2) : 0,
            ];
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.customers.outstanding_debts');
        Cache::forget('report.customers.statement');
        Cache::forget('report.customers.credit_exposure');
        Cache::forget('report.customers.payment_behaviour');
        Cache::forget('report.customers.top');
        Cache::forget('report.customers.overdue');
        Cache::forget('report.customers.trends');
        Cache::forget('report.customers.profitability');
    }
}
