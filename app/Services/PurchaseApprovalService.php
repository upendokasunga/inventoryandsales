<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;

class PurchaseApprovalService
{
    public function submitForApproval(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, 'draft');

        $purchaseOrder->update(['status' => 'pending_approval']);

        Cache::forget('purchasing.order.stats');

        return $purchaseOrder->fresh();
    }

    public function approve(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, 'pending_approval');

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Cache::forget('purchasing.order.stats');

        return $purchaseOrder->fresh();
    }

    public function reject(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, 'pending_approval');

        $purchaseOrder->update(['status' => 'draft']);

        Cache::forget('purchasing.order.stats');

        return $purchaseOrder->fresh();
    }

    public function send(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, 'approved');

        $purchaseOrder->update(['status' => 'sent']);

        Cache::forget('purchasing.order.stats');

        return $purchaseOrder->fresh();
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            throw new \InvalidArgumentException('Cannot cancel order in status: ' . $purchaseOrder->status);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        Cache::forget('purchasing.order.stats');

        return $purchaseOrder->fresh();
    }

    protected function assertStatus(PurchaseOrder $purchaseOrder, string $expected): void
    {
        if ($purchaseOrder->status !== $expected) {
            throw new \InvalidArgumentException(
                "Expected status '{$expected}', got '{$purchaseOrder->status}'"
            );
        }
    }
}
