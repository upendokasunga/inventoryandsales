<?php

namespace App\Services;

use App\Models\Unit;

class UnitService
{
    public function getAllPaginated(int $perPage = 20)
    {
        return Unit::latest()->paginate($perPage);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }
}
