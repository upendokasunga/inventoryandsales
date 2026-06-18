<?php

namespace App\Services;

use App\Models\PriceList;

class PriceRuleService
{
    /**
     * Find the cheapest applicable price for given product/unit/quantity.
     *
     * Resolution priority (implicit via query constraints):
     * 1. Active + valid (non-expired) price lists only
     * 2. Customer group: specific group list OR general (null customer_group_id)
     * 3. Product + unit match
     * 4. Quantity tier: min_quantity <= qty <= max_quantity (unlimited if null)
     * 5. Cheapest price wins across all matching lists
     */
    public function findBestPrice(int $productId, int $unitId, float $quantity, ?int $customerGroupId = null): ?array
    {
        $lists = PriceList::with(['items' => function ($q) use ($productId, $unitId, $quantity) {
            $q->where('product_id', $productId)
              ->where('unit_id', $unitId)
              ->where('min_quantity', '<=', $quantity)
              ->where(function ($sub) use ($quantity) {
                  $sub->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
              });
        }])
        ->active()
        ->valid()
        ->where(function ($q) use ($customerGroupId) {
            $q->whereNull('customer_group_id');
            if ($customerGroupId) {
                $q->orWhere('customer_group_id', $customerGroupId);
            }
        })
        ->get();

        $best = null;

        foreach ($lists as $list) {
            foreach ($list->items as $item) {
                $candidate = [
                    'price_list_id' => $list->id,
                    'price_list_name' => $list->name,
                    'item_id' => $item->id,
                    'price' => $item->price,
                    'min_quantity' => $item->min_quantity,
                    'max_quantity' => $item->max_quantity,
                    'matched_tier' => (float) $item->min_quantity . ($item->max_quantity ? ' - ' . (float) $item->max_quantity : '+'),
                ];

                if ($best === null || $item->price < $best['price']) {
                    $best = $candidate;
                }
            }
        }

        return $best;
    }

    public function findAllApplicable(int $productId, int $unitId, float $quantity, ?int $customerGroupId = null): array
    {
        $lists = PriceList::with(['items' => function ($q) use ($productId, $unitId, $quantity) {
            $q->where('product_id', $productId)
              ->where('unit_id', $unitId)
              ->where('min_quantity', '<=', $quantity)
              ->where(function ($sub) use ($quantity) {
                  $sub->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
              });
        }])
        ->active()
        ->valid()
        ->where(function ($q) use ($customerGroupId) {
            $q->whereNull('customer_group_id');
            if ($customerGroupId) {
                $q->orWhere('customer_group_id', $customerGroupId);
            }
        })
        ->get();

        $results = [];

        foreach ($lists as $list) {
            foreach ($list->items as $item) {
                $results[] = [
                    'price_list_id' => $list->id,
                    'price_list_name' => $list->name,
                    'customer_group' => $list->customerGroup?->name,
                    'item_id' => $item->id,
                    'price' => $item->price,
                    'min_quantity' => $item->min_quantity,
                    'max_quantity' => $item->max_quantity,
                    'matched_tier' => (float) $item->min_quantity . ($item->max_quantity ? ' - ' . (float) $item->max_quantity : '+'),
                ];
            }
        }

        usort($results, fn($a, $b) => $a['price'] <=> $b['price']);

        return $results;
    }
}
