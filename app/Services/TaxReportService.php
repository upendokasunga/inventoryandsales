<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaxReportService
{
    public function getSalesTaxReport(string $startDate, string $endDate): array
    {
        return Cache::remember("report.tax.sales.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $monthly = Invoice::select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(tax) as total_tax'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_invoices' => $monthly->sum('invoice_count'),
                'total_subtotal' => round($monthly->sum('total_subtotal'), 2),
                'total_tax' => round($monthly->sum('total_tax'), 2),
                'total_amount' => round($monthly->sum('total_amount'), 2),
                'monthly' => $monthly->toArray(),
            ];
        });
    }

    public function getPurchaseTaxReport(string $startDate, string $endDate): array
    {
        return Cache::remember("report.tax.purchase.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $monthly = PurchaseOrder::select(
                DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(tax) as total_tax'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'partially_received', 'sent'])
                ->groupBy(DB::raw("DATE_FORMAT(order_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_orders' => $monthly->sum('order_count'),
                'total_subtotal' => round($monthly->sum('total_subtotal'), 2),
                'total_tax' => round($monthly->sum('total_tax'), 2),
                'total_amount' => round($monthly->sum('total_amount'), 2),
                'monthly' => $monthly->toArray(),
            ];
        });
    }

    public function getVatSummary(string $startDate, string $endDate): array
    {
        return Cache::remember("report.tax.vat_summary.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $salesTax = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'completed'])
                ->sum('tax');

            $purchaseTax = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'partially_received', 'sent'])
                ->sum('tax');

            $netVat = $salesTax - $purchaseTax;

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sales_tax_collected' => round($salesTax, 2),
                'purchase_tax_paid' => round($purchaseTax, 2),
                'net_vat_payable' => round(max($netVat, 0), 2),
                'net_vat_refundable' => round(abs(min($netVat, 0)), 2),
                'vat_rate' => $salesTax > 0 && ($salesTax + $purchaseTax) > 0
                    ? round(($netVat / ($salesTax + $purchaseTax)) * 100, 2) : 0,
            ];
        });
    }

    public function getTaxPayable(string $startDate, string $endDate): float
    {
        $key = "report.tax.payable.{$startDate}.{$endDate}";

        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            $purchaseTax = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'partially_received', 'sent'])
                ->sum('tax');

            return round($purchaseTax, 2);
        });
    }

    public function getTaxCollected(string $startDate, string $endDate): float
    {
        $key = "report.tax.collected.{$startDate}.{$endDate}";

        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            $salesTax = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'completed'])
                ->sum('tax');

            return round($salesTax, 2);
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.tax.sales');
        Cache::forget('report.tax.purchase');
        Cache::forget('report.tax.vat_summary');
        Cache::forget('report.tax.payable');
        Cache::forget('report.tax.collected');
    }
}
