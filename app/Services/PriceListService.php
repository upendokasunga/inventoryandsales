<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\PriceListItem;
use Illuminate\Support\Facades\DB;

class PriceListService
{
    protected function validateTierOverlapWithinItems(array $items): void
    {
        for ($i = 0; $i < count($items); $i++) {
            for ($j = $i + 1; $j < count($items); $j++) {
                if ($items[$i]['product_id'] !== $items[$j]['product_id']) continue;
                if ($items[$i]['unit_id'] !== $items[$j]['unit_id']) continue;

                $aMin = (float) ($items[$i]['min_quantity'] ?? 0);
                $aMax = isset($items[$i]['max_quantity']) ? (float) $items[$i]['max_quantity'] : null;
                $bMin = (float) ($items[$j]['min_quantity'] ?? 0);
                $bMax = isset($items[$j]['max_quantity']) ? (float) $items[$j]['max_quantity'] : null;

                $aStart = $aMin;
                $aEnd = $aMax ?? PHP_FLOAT_MAX;
                $bStart = $bMin;
                $bEnd = $bMax ?? PHP_FLOAT_MAX;

                if ($aStart <= $bEnd && $bStart <= $aEnd) {
                    throw new \InvalidArgumentException(
                        "Tier overlap between items at positions " . ($i + 1) . " and " . ($j + 1)
                        . " for product ID {$items[$i]['product_id']}, unit ID {$items[$i]['unit_id']}."
                    );
                }
            }
        }
    }

    protected function validateNoTierOverlapWithExisting(int $priceListId, array $items): void
    {
        foreach ($items as $item) {
            $newMin = (float) ($item['min_quantity'] ?? 0);
            $newMax = isset($item['max_quantity']) ? (float) $item['max_quantity'] : null;

            $query = PriceListItem::where('price_list_id', $priceListId)
                ->where('product_id', $item['product_id'])
                ->where('unit_id', $item['unit_id']);

            if (isset($item['id'])) {
                $query->where('id', '!=', $item['id']);
            }

            $query->where(function ($q) use ($newMin, $newMax) {
                if ($newMax !== null) {
                    $q->where('min_quantity', '<=', $newMax);
                }
                $q->where(function ($sub) use ($newMin) {
                    $sub->whereNull('max_quantity')
                        ->orWhere('max_quantity', '>=', $newMin);
                });
            });

            if ($query->exists()) {
                $existing = $query->first();
                $existingRange = $existing->min_quantity . ($existing->max_quantity ? ' - ' . $existing->max_quantity : '+');
                $newRange = $newMin . ($newMax !== null ? " - {$newMax}" : '+');
                throw new \InvalidArgumentException(
                    "Tier overlap: range {$newRange} conflicts with existing {$existingRange} "
                    . "for product ID {$item['product_id']}, unit ID {$item['unit_id']}."
                );
            }
        }
    }
    public function getAllPaginated(int $perPage = 20, ?array $filters = null)
    {
        $query = PriceList::with('customerGroup', 'items');

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if (isset($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function search(string $query, int $perPage = 20)
    {
        return PriceList::with('customerGroup', 'items')
            ->where('name', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): PriceList
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $priceList = PriceList::create($data);
            $this->validateTierOverlapWithinItems($items);
            $this->validateNoTierOverlapWithExisting($priceList->id, $items);

            foreach ($items as $item) {
                $priceList->items()->create($item);
            }

            return $priceList->fresh('items.product', 'items.unit', 'customerGroup');
        });
    }

    public function update(PriceList $priceList, array $data): PriceList
    {
        return DB::transaction(function () use ($priceList, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $this->validateTierOverlapWithinItems($items);
            $this->validateNoTierOverlapWithExisting($priceList->id, $items);

            $priceList->update($data);

            $keepIds = [];
            foreach ($items as $item) {
                if (isset($item['id'])) {
                    $keepIds[] = $item['id'];
                    $priceList->items()->where('id', $item['id'])->update($item);
                } else {
                    $new = $priceList->items()->create($item);
                    $keepIds[] = $new->id;
                }
            }

            $priceList->items()->whereNotIn('id', $keepIds)->delete();

            return $priceList->fresh('items.product', 'items.unit', 'customerGroup');
        });
    }

    public function delete(PriceList $priceList): void
    {
        $priceList->items()->delete();
        $priceList->delete();
    }

    public function getActiveLists(?int $customerGroupId = null)
    {
        $query = PriceList::with('items')->active()->valid();

        if ($customerGroupId) {
            $query->where(function ($q) use ($customerGroupId) {
                $q->whereNull('customer_group_id')
                  ->orWhere('customer_group_id', $customerGroupId);
            });
        }

        return $query->get();
    }

    public function getDashboardStats(): array
    {
        $total = PriceList::count();
        $active = PriceList::where('is_active', true)->count();
        $expired = PriceList::where('is_active', true)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->count();
        $withGroup = PriceList::whereNotNull('customer_group_id')->count();

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'with_group' => $withGroup,
        ];
    }
}
