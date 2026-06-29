<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(
        protected PricingService $pricingService,
        protected CreditService $creditService,
        protected InventoryService $inventoryService,
        protected InvoiceService $invoiceService,
        protected PaymentService $paymentService,
    ) {}

    public function lookupBarcode(string $barcode): ?Product
    {
        $cacheKey = "barcode.product.{$barcode}";

        return Cache::remember($cacheKey, 86400, function () use ($barcode) {
            return Product::where('barcode', $barcode)
                ->with(['category', 'supplier', 'units.unit'])
                ->first();
        });
    }

    public function lookupSku(string $sku): ?Product
    {
        $cacheKey = "barcode.sku.{$sku}";

        return Cache::remember($cacheKey, 86400, function () use ($sku) {
            return Product::where('sku', $sku)
                ->with(['category', 'supplier', 'units.unit'])
                ->first();
        });
    }

    public function getCustomerWithCredit(Customer $customer): array
    {
        $customer->load(['group', 'priceLists']);
        $credit = $this->creditService->getCachedCredit($customer);

        return [
            'customer' => $customer,
            'credit_limit' => $credit['limit'],
            'outstanding_balance' => $credit['outstanding'],
            'available_credit' => $credit['available'],
            'credit_status' => $credit['status'],
        ];
    }

    public function getProductPrice(Product $product, int $unitId, float $quantity, ?int $customerGroupId): ?array
    {
        return $this->pricingService->getPrice(
            $product->id,
            $unitId,
            $quantity,
            $customerGroupId
        );
    }

    public function validateCreditForTransaction(Customer $customer, float $total): array
    {
        return $this->creditService->validateCredit($customer, $total);
    }

    public function checkout(array $data, Customer $customer): array
    {
        return DB::transaction(function () use ($data, $customer) {
            $invoice = $this->invoiceService->create($data);

            if (($data['payment']['amount'] ?? 0) > 0) {
                $this->paymentService->recordPayment($invoice, $data['payment']);
            }

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $this->inventoryService->issueStock($product, $item['quantity']);
            }

            Cache::forget('pos.dashboard.stats');
            Cache::forget('sales.dashboard');

            return ['invoice' => $invoice];
        });
    }

    public function invalidateBarcodeCache(string $barcode): void
    {
        Cache::forget("barcode.product.{$barcode}");
    }

    public function invalidateSkuCache(string $sku): void
    {
        Cache::forget("barcode.sku.{$sku}");
    }

    public function getDashboardStats(): array
    {
        return Cache::remember('pos.dashboard.stats', 300, function () {
            $today = now()->startOfDay();

            $todayInvoices = \App\Models\Invoice::whereDate('created_at', $today);
            $totalPayments = \App\Models\Payment::whereDate('created_at', $today)->sum('amount');

            return [
                'today_sales' => (float) $todayInvoices->sum('total'),
                'invoices_issued' => $todayInvoices->count(),
                'payments_received' => (float) $totalPayments,
                'outstanding_receivables' => (float) \App\Models\Invoice::whereIn('payment_status', ['pending', 'partial'])->sum('balance_due'),
            ];
        });
    }
}
