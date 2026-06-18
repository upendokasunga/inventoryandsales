<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;

class ProductUnitService
{
    public function getUnitsForProduct(Product $product)
    {
        return $product->productUnits()->with('unit')->get();
    }

    public function sync(Product $product, array $units): void
    {
        $unitIds = [];

        foreach ($units as $unitData) {
            if (isset($unitData['id'])) {
                $unit = ProductUnit::find($unitData['id']);
                if ($unit && $unit->product_id === $product->id) {
                    $unit->update($unitData);
                    $unitIds[] = $unit->id;
                    continue;
                }
            }

            $unitData['product_id'] = $product->id;
            $newUnit = ProductUnit::create($unitData);
            $unitIds[] = $newUnit->id;
        }

        $product->productUnits()
            ->whereNotIn('id', $unitIds)
            ->delete();
    }

    public function setDefaultSale(Product $product, int $unitId): void
    {
        $product->productUnits()->update(['is_default_sale' => false]);
        $product->productUnits()->where('id', $unitId)->update(['is_default_sale' => true]);
    }

    public function setDefaultPurchase(Product $product, int $unitId): void
    {
        $product->productUnits()->update(['is_default_purchase' => false]);
        $product->productUnits()->where('id', $unitId)->update(['is_default_purchase' => true]);
    }

    public function delete(ProductUnit $unit): void
    {
        $unit->delete();
    }
}
