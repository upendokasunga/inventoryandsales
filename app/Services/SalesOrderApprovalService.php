<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\Cache;

class SalesOrderApprovalService
{
    public function __construct(
        protected CreditService $creditService,
    ) {}

    public function submitForApproval(SalesOrder $salesOrder): SalesOrder
    {
        $this->assertStatus($salesOrder, 'draft');

        $creditCheck = $this->creditService->validateCredit(
            $salesOrder->customer,
            $salesOrder->total
        );

        if (!$creditCheck['approved']) {
            throw new \InvalidArgumentException(
                'Credit check failed: ' . ($creditCheck['reason'] ?? 'Unknown reason')
            );
        }

        $salesOrder->update(['status' => 'pending_approval']);
        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    public function approve(SalesOrder $salesOrder): SalesOrder
    {
        $this->assertStatus($salesOrder, 'pending_approval');

        $salesOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    public function reject(SalesOrder $salesOrder): SalesOrder
    {
        $this->assertStatus($salesOrder, 'pending_approval');

        $salesOrder->update(['status' => 'draft']);
        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    public function cancel(SalesOrder $salesOrder): SalesOrder
    {
        if (!in_array($salesOrder->status, ['draft', 'pending_approval', 'approved'])) {
            throw new \InvalidArgumentException('Cannot cancel order in status: ' . $salesOrder->status);
        }

        $salesOrder->update(['status' => 'cancelled']);
        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    protected function assertStatus(SalesOrder $salesOrder, string $expected): void
    {
        if ($salesOrder->status !== $expected) {
            throw new \InvalidArgumentException(
                "Expected status '{$expected}', got '{$salesOrder->status}'"
            );
        }
    }
}
