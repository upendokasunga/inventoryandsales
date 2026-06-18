<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function calculateAvailableCredit(Customer $customer): float
    {
        return max(0, $customer->credit_limit - $customer->outstanding_balance);
    }

    public function validateCredit(Customer $customer, float $amount): array
    {
        if ($customer->isOnHold()) {
            return [
                'approved' => false,
                'reason' => 'Customer credit is on hold',
                'code' => 'CREDIT_HOLD',
            ];
        }

        if (!$customer->is_active) {
            return [
                'approved' => false,
                'reason' => 'Customer is inactive',
                'code' => 'CUSTOMER_INACTIVE',
            ];
        }

        if ($customer->credit_limit <= 0) {
            return [
                'approved' => false,
                'reason' => 'No credit limit assigned',
                'code' => 'NO_LIMIT',
            ];
        }

        $available = $this->calculateAvailableCredit($customer);

        if ($amount > $available) {
            return [
                'approved' => false,
                'reason' => 'Insufficient credit. Available: ' . number_format($available, 2),
                'code' => 'INSUFFICIENT_CREDIT',
                'available' => $available,
                'requested' => $amount,
            ];
        }

        return ['approved' => true, 'code' => 'OK'];
    }

    public function updateBalance(Customer $customer, float $amount, string $type = 'order'): void
    {
        DB::transaction(function () use ($customer, $amount, $type) {
            $balanceBefore = $customer->outstanding_balance;

            if (in_array($type, ['payment', 'refund', 'reversal', 'adjustment', 'credit_note'])) {
                $newBalance = max(0, $customer->outstanding_balance - abs($amount));
            } else {
                $newBalance = $customer->outstanding_balance + abs($amount);
            }

            $customer->update([
                'outstanding_balance' => $newBalance,
                'available_credit' => $this->calculateAvailableCredit($customer),
            ]);

            $this->invalidateCache($customer);

            $this->recordCreditTransaction(
                $customer,
                $type,
                $amount,
                $balanceBefore,
                $newBalance,
                auth()->user()
            );
        });
    }

    public function recordCreditTransaction(
        Customer $customer,
        string $type,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        ?User $user = null,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): CustomerCreditTransaction {
        return CustomerCreditTransaction::create([
            'customer_id' => $customer->id,
            'user_id' => $user?->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'currency' => 'TZS',
            'description' => $description ?? ucfirst($type) . ' transaction',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
        ]);
    }

    public function getCachedCredit(Customer $customer): array
    {
        $cacheKey = "customer.credit.{$customer->id}";

        return Cache::remember($cacheKey, 300, function () use ($customer) {
            $customer->fresh();
            return [
                'limit' => (float) $customer->credit_limit,
                'available' => (float) $customer->available_credit,
                'outstanding' => (float) $customer->outstanding_balance,
                'status' => $customer->credit_status,
            ];
        });
    }

    public function invalidateCache(Customer $customer): void
    {
        Cache::forget("customer.credit.{$customer->id}");
    }
}
