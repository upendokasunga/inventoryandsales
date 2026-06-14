<?php

namespace App\Services;

use App\Models\CustomerGroup;

class CustomerGroupService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null)
    {
        $query = CustomerGroup::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): CustomerGroup
    {
        return CustomerGroup::create($data);
    }

    public function update(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        $customerGroup->update($data);

        return $customerGroup->fresh();
    }

    public function delete(CustomerGroup $customerGroup): void
    {
        $customerGroup->delete();
    }
}
