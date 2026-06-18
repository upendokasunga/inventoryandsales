<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PricingService
{
    protected PriceRuleService $priceRuleService;

    public function __construct(PriceRuleService $priceRuleService)
    {
        $this->priceRuleService = $priceRuleService;
    }

    public function getPrice(int $productId, int $unitId, float $quantity, ?int $customerGroupId = null): ?array
    {
        try {
            $version = $this->getCacheVersion();
            $groupKey = $customerGroupId ?? 'all';
            $qtyKey = (string) $quantity;
            $cacheKey = "pricing.v{$version}.{$groupKey}.{$productId}.{$unitId}.{$qtyKey}";

            return Cache::remember($cacheKey, 3600, function () use ($productId, $unitId, $quantity, $customerGroupId) {
                return $this->priceRuleService->findBestPrice($productId, $unitId, $quantity, $customerGroupId);
            });
        } catch (\Throwable $e) {
            report($e);
            return $this->priceRuleService->findBestPrice($productId, $unitId, $quantity, $customerGroupId);
        }
    }

    public function getOrFail(int $productId, int $unitId, float $quantity, ?int $customerGroupId = null): array
    {
        $result = $this->getPrice($productId, $unitId, $quantity, $customerGroupId);

        if ($result === null) {
            throw new \RuntimeException('No applicable price found for the given product, unit, and quantity.');
        }

        return $result;
    }

    public function invalidateCache(): void
    {
        $version = $this->getCacheVersion();
        Cache::forever('pricing.cache_version', $version + 1);
    }

    protected function getCacheVersion(): int
    {
        return (int) Cache::get('pricing.cache_version', 1);
    }
}
