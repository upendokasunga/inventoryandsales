<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CustomerService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = Customer::query()->with('group');

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['credit_status'])) {
            $query->where('credit_status', $filters['credit_status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        $data['available_credit'] = $data['credit_limit'] ?? 0;
        $data['outstanding_balance'] = 0;
        $data['credit_status'] = 'good';

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $oldLimit = $customer->credit_limit;
        $customer->update($data);

        if (isset($data['credit_limit']) && $data['credit_limit'] != $oldLimit) {
            $customer->available_credit = max(0, $customer->credit_limit - $customer->outstanding_balance);
            $customer->saveQuietly();
            Cache::forget("customer.credit.{$customer->id}");
        }

        return $customer->fresh('group');
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return Customer::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->with('group')
            ->latest()
            ->paginate($perPage);
    }

    public function exportCsv(): \Illuminate\Support\LazyCollection
    {
        return Customer::with('group')->lazy();
    }
}
