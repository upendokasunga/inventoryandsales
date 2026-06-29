<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    protected function basePaymentsQuery(string $startDate, string $endDate, string $method)
    {
        return Payment::with('invoice.customer')
            ->where('payment_method', $method)
            ->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function getCashPayments(string $startDate, string $endDate): array
    {
        return Cache::remember("report.payment.cash.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $payments = $this->basePaymentsQuery($startDate, $endDate, 'cash')->latest()->take(1000)->get();

            return [
                'total_count' => $payments->count(),
                'total_amount' => round($payments->sum('amount'), 2),
                'payments' => $payments->toArray(),
            ];
        });
    }

    public function getCreditPayments(string $startDate, string $endDate): array
    {
        return Cache::remember("report.payment.credit.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $payments = $this->basePaymentsQuery($startDate, $endDate, 'credit')->latest()->take(1000)->get();

            return [
                'total_count' => $payments->count(),
                'total_amount' => round($payments->sum('amount'), 2),
                'payments' => $payments->toArray(),
            ];
        });
    }

    public function getBankTransfers(string $startDate, string $endDate): array
    {
        return Cache::remember("report.payment.bank_transfer.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $payments = $this->basePaymentsQuery($startDate, $endDate, 'bank_transfer')->latest()->take(1000)->get();

            return [
                'total_count' => $payments->count(),
                'total_amount' => round($payments->sum('amount'), 2),
                'payments' => $payments->toArray(),
            ];
        });
    }

    public function getMobileMoney(string $startDate, string $endDate): array
    {
        return Cache::remember("report.payment.mobile_money.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $payments = $this->basePaymentsQuery($startDate, $endDate, 'mobile_money')->latest()->take(1000)->get();

            return [
                'total_count' => $payments->count(),
                'total_amount' => round($payments->sum('amount'), 2),
                'payments' => $payments->toArray(),
            ];
        });
    }

    public function getOutstandingReceivables(): array
    {
        return Cache::remember('report.payment.outstanding_receivables', 3600, function () {
            $invoices = Invoice::with('customer')
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->whereIn('status', ['approved', 'completed'])
                ->latest()
                ->take(1000)->get();

            return [
                'total_outstanding' => round($invoices->sum('balance_due'), 2),
                'invoice_count' => $invoices->count(),
                'invoices' => $invoices->toArray(),
            ];
        });
    }

    public function getPartialPayments(): array
    {
        return Cache::remember('report.payment.partial_payments', 3600, function () {
            $invoices = Invoice::with('customer')
                ->where('payment_status', 'partial')
                ->whereIn('status', ['approved', 'completed'])
                ->latest()
                ->take(1000)->get();

            return [
                'total_count' => $invoices->count(),
                'total_outstanding' => round($invoices->sum('balance_due'), 2),
                'invoices' => $invoices->toArray(),
            ];
        });
    }

    public function getOverdueInvoices(): array
    {
        return Cache::remember('report.payment.overdue_invoices', 3600, function () {
            $invoices = Invoice::with('customer')
                ->where('payment_status', 'overdue')
                ->whereIn('status', ['approved', 'completed'])
                ->latest()
                ->take(1000)->get();

            return [
                'total_count' => $invoices->count(),
                'total_due' => round($invoices->sum('balance_due'), 2),
                'invoices' => $invoices->toArray(),
            ];
        });
    }

    public function getPaymentTrends(string $startDate, string $endDate): array
    {
        return Cache::remember("report.payment.trends.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $monthly = Payment::select(
                DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw("SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END) as cash"),
                DB::raw("SUM(CASE WHEN payment_method = 'credit' THEN amount ELSE 0 END) as credit"),
                DB::raw("SUM(CASE WHEN payment_method = 'bank_transfer' THEN amount ELSE 0 END) as bank_transfer"),
                DB::raw("SUM(CASE WHEN payment_method = 'mobile_money' THEN amount ELSE 0 END) as mobile_money")
            )
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $totalPaid = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $expected = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'completed'])
                ->sum('total');

            $collectionRate = $expected > 0 ? round(($totalPaid / $expected) * 100, 2) : 0;

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_collected' => round($totalPaid, 2),
                'total_expected' => round($expected, 2),
                'collection_rate' => $collectionRate,
                'monthly' => $monthly->toArray(),
            ];
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.payment.outstanding_receivables');
        Cache::forget('report.payment.partial_payments');
        Cache::forget('report.payment.overdue_invoices');
    }
}
