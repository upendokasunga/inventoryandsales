<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected PricingService $pricingService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = Invoice::with(['customer', 'creator', 'items.product']);

        $tab = $filters['tab'] ?? 'all';

        if ($tab === 'draft') {
            $query->where('status', 'draft');
        } elseif ($tab === 'proforma') {
            $query->where('status', 'proforma');
        } elseif ($tab === 'pending_approval') {
            $query->where('status', 'pending_approval');
        } elseif ($tab === 'posted' || $tab === 'approved') {
            $query->whereIn('status', ['approved', 'posted']);
        } elseif ($tab === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($tab === 'partial') {
            $query->where('payment_status', 'partial');
        } elseif ($tab === 'overdue') {
            $query->whereIn('payment_status', ['pending', 'partial'])
                ->where('invoice_date', '<', now());
        } elseif ($tab === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($tab === 'reversed') {
            $query->where('status', 'reversed');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('invoice_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$filters['search']}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            $invoiceItems = [];
            $soItems = [];
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $storeId = $item['store_id'] ?? $data['store_id'] ?? null;

                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0) + ($item['tax'] ?? 0);
                $subtotal += $item['unit_price'] * $item['quantity'];
                $totalTax += $item['tax'] ?? 0;
                $totalDiscount += $item['discount'] ?? 0;

                $invoiceItems[] = new InvoiceItem([
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'product_unit_id' => $item['product_unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'line_total' => $lineTotal,
                ]);

                $soItems[] = new SalesOrderItem([
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'subtotal' => $item['unit_price'] * $item['quantity'],
                    'total' => $lineTotal,
                    'fulfilled_quantity' => $item['quantity'],
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['discount'] = $data['discount'] ?? 0;
            $data['tax'] = $data['tax'] ?? $totalTax;
            $data['total'] = $subtotal - $data['discount'] + $data['tax'];
            $data['balance_due'] = $data['total'] - ($data['amount_paid'] ?? 0);
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $data['invoice_date'] = $data['invoice_date'] ?? now();
            $amountPaid = $data['amount_paid'] ?? 0;
            $data['payment_status'] = $amountPaid >= $data['total'] ? 'paid' : ($amountPaid > 0 ? 'partial' : 'pending');

            $so = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'so_number' => 'SO-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'order_date' => $data['invoice_date'],
                'status' => 'invoiced',
                'subtotal' => $subtotal,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? $totalTax,
                'total' => $data['total'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'invoiced_by' => auth()->id(),
                'invoiced_at' => now(),
            ]);
            $so->items()->saveMany($soItems);
            $data['sales_order_id'] = $so->id;

            $invoice = Invoice::create($data);
            $invoice->items()->saveMany($invoiceItems);

            Cache::forget('pos.dashboard.stats');
            Cache::forget('sales.dashboard');

            return $invoice->load(['items.product', 'customer']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            if (isset($data['items'])) {
                $items = $data['items'];
                unset($data['items']);

                $subtotal = 0;
                $totalTax = 0;
                $totalDiscount = 0;

                $invoice->items()->delete();

                foreach ($items as $item) {
                    $productId = $item['product_id'];
                    $storeId = $item['store_id'] ?? null;

                    $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0) + ($item['tax'] ?? 0);
                    $subtotal += $item['unit_price'] * $item['quantity'];
                    $totalTax += $item['tax'] ?? 0;
                    $totalDiscount += $item['discount'] ?? 0;

                    $invoice->items()->create([
                        'product_id' => $productId,
                        'store_id' => $storeId,
                        'product_unit_id' => $item['product_unit_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'line_total' => $lineTotal,
                    ]);
                }

                $data['subtotal'] = $subtotal;
                $data['tax'] = $data['tax'] ?? $totalTax;
                $data['total'] = $subtotal - ($data['discount'] ?? $invoice->discount) + ($data['tax'] ?? $invoice->tax);
            }

            $data['balance_due'] = max(0, ($data['total'] ?? $invoice->total) - ($data['amount_paid'] ?? $invoice->amount_paid));
            $data['payment_status'] = ($data['amount_paid'] ?? $invoice->amount_paid) >= ($data['total'] ?? $invoice->total) ? 'paid' : 'partial';

            $invoice->update($data);

            Cache::forget('pos.dashboard.stats');

            return $invoice->load(['items.product', 'customer']);
        });
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
        Cache::forget('pos.dashboard.stats');
    }

    public function approve(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function computeIssueCost(Product $product, float $quantity, string $method = 'fifo'): float
    {
        if ($quantity <= 0) {
            return 0;
        }

        return match ($method) {
            'fifo' => $this->computeFifoCost($product->id, $quantity),
            'average' => $this->computeAverageCost($product->id, $quantity),
            'standard' => $this->computeStandardCost($product->id, $quantity),
            default => throw new \InvalidArgumentException("Unknown costing method: {$method}"),
        };
    }

    protected function computeFifoCost(int $productId, float $quantity): float
    {
        $receipts = InventoryTransaction::where('product_id', $productId)
            ->where('type', 'purchase_receipt')
            ->where('quantity', '>', 0)
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get();

        $totalCost = 0;
        $remaining = $quantity;

        foreach ($receipts as $receipt) {
            if ($remaining <= 0) {
                break;
            }
            $available = (float) $receipt->quantity;
            $used = min($available, $remaining);
            $totalCost += $used * (float) ($receipt->unit_cost ?: 0);
            $remaining -= $used;
        }

        return $totalCost;
    }

    protected function computeAverageCost(int $productId, float $quantity): float
    {
        $balance = InventoryBalance::where('product_id', $productId)->first();
        if (!$balance || $balance->quantity_on_hand <= 0) {
            return 0;
        }
        return $quantity * (float) $balance->average_cost;
    }

    protected function computeStandardCost(int $productId, float $quantity): float
    {
        $pu = ProductUnit::where('product_id', $productId)
            ->where('is_default_purchase', true)
            ->first();

        if (!$pu || !$pu->purchase_price) {
            $pu = ProductUnit::where('product_id', $productId)->first();
        }

        $unitCost = (float) ($pu->purchase_price ?? 0);
        return $quantity * $unitCost;
    }

    public function getStats(): array
    {
        return Cache::remember('invoices.stats', 300, function () {
            return [
                'total_invoices' => Invoice::count(),
                'total_paid' => (float) Invoice::where('payment_status', 'paid')->sum('total'),
                'total_pending' => (float) Invoice::whereIn('payment_status', ['pending', 'partial'])->sum('balance_due'),
                'total_cancelled' => (float) Invoice::where('status', 'cancelled')->sum('total'),
                'monthly_total' => (float) Invoice::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total'),
            ];
        });
    }
}
