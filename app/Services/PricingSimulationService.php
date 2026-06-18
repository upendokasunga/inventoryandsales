<?php

namespace App\Services;

class PricingSimulationService
{
    protected PricingService $pricingService;
    protected PriceRuleService $priceRuleService;

    public function __construct(PricingService $pricingService, PriceRuleService $priceRuleService)
    {
        $this->pricingService = $pricingService;
        $this->priceRuleService = $priceRuleService;
    }

    public function simulate(?int $customerGroupId, ?int $productId, ?int $unitId, ?float $quantity): array
    {
        if (!$productId || !$unitId || !$quantity) {
            return ['results' => [], 'best' => null];
        }

        $allResults = $this->priceRuleService->findAllApplicable($productId, $unitId, $quantity, $customerGroupId);
        $best = $this->pricingService->getPrice($productId, $unitId, $quantity, $customerGroupId);

        $enriched = array_map(function ($r) use ($quantity) {
            $r['unit_price'] = $r['price'];
            $r['total_amount'] = round($r['price'] * $quantity, 2);
            return $r;
        }, $allResults);

        $bestEnriched = null;
        if ($best) {
            $bestEnriched = $best;
            $bestEnriched['unit_price'] = $best['price'];
            $bestEnriched['total_amount'] = round($best['price'] * $quantity, 2);
        }

        return [
            'results' => $enriched,
            'best' => $bestEnriched,
        ];
    }
}
