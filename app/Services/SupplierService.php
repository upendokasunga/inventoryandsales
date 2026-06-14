<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null)
    {
        $query = Supplier::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function search(?string $query, int $perPage = 20)
    {
        if (!$query) {
            return $this->getAllPaginated($perPage);
        }

        return Supplier::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('contact_person', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('city', 'like', "%{$query}%")
              ->orWhere('tax_id', 'like', "%{$query}%");
        })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
