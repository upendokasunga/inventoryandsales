<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsReceipt\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected GoodsReceiptService $receiptService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $filters = $request->only(['purchase_order_id']);

        if ($tab !== 'all') {
            $filters['status'] = $tab;
        }

        $receipts = $this->receiptService->getAllPaginated(20, $filters);
        $stats = $this->receiptService->getStats();

        return view('purchasing.receipts.index', compact('receipts', 'stats', 'tab'));
    }

    public function create(Request $request): View
    {
        $purchaseOrder = null;
        $orders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['sent', 'partially_received'])
            ->latest()
            ->get();

        if ($request->filled('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])
                ->findOrFail($request->purchase_order_id);
        }

        return view('purchasing.receipts.create', compact('orders', 'purchaseOrder'));
    }

    public function store(StoreGoodsReceiptRequest $request): RedirectResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
        $data = $request->safe()->except('items');
        $items = $request->input('items', []);

        $this->receiptService->createFromPO($purchaseOrder, $data, $items);

        return redirect()->route('purchasing.receipts.index')
            ->with('success', 'Goods receipt created.');
    }

    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load(['purchaseOrder.supplier', 'items.product', 'creator']);
        return view('purchasing.receipts.show', compact('goodsReceipt'));
    }

    public function print(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load(['purchaseOrder.supplier', 'items.product', 'creator']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['goodsReceipt'] = $goodsReceipt;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.goods-receipt', $data, "grn-{$goodsReceipt->receipt_number}.pdf");
    }

    public function complete(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $this->receiptService->complete($goodsReceipt);

        return redirect()->route('purchasing.receipts.show', $goodsReceipt)
            ->with('success', 'Goods receipt completed.');
    }
}
