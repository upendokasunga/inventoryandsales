<?php

namespace App\Observers;

use App\Models\GoodsReceipt;
use App\Models\GrNumberSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GoodsReceiptObserver
{
    public function creating(GoodsReceipt $receipt): void
    {
        if (empty($receipt->receipt_number)) {
            $receipt->receipt_number = $this->generateReceiptNumber();
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

    protected function generateReceiptNumber(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $sequence = GrNumberSequence::lockForUpdate()->firstOrCreate(
                ['year' => $year],
                ['last_number' => 0]
            );

            $sequence->increment('last_number');

            return 'GR-' . $year . '-' . str_pad($sequence->last_number, 6, '0', STR_PAD_LEFT);
        });
    }
}
