<?php

namespace App\Observers;

use App\Models\GoodsReceipt;
use App\Services\DocumentNumberingService;
use Illuminate\Support\Facades\Cache;

class GoodsReceiptObserver
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function creating(GoodsReceipt $receipt): void
    {
        if (empty($receipt->receipt_number)) {
            $receipt->receipt_number = $this->numberingService->generateNumber('goods_receipt');
        }
    }

    public function updated(GoodsReceipt $receipt): void
    {
        Cache::forget('purchasing.receipt.stats');
    }

    public function deleted(GoodsReceipt $receipt): void
    {
        Cache::forget('purchasing.receipt.stats');
    }
}
