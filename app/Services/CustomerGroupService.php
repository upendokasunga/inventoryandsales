<?php

namespace App\Services;

use App\Models\CustomerGroup;

class CustomerGroupService
{
    public function getAllPaginated(int $perPage = 20)
    {
        return CustomerGroup::latest()->paginate($perPage);
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
