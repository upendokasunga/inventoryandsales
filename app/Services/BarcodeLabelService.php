<?php

namespace App\Services;

use App\Models\Product;

class BarcodeLabelService
{
    public function generateLabels(Product $product, string $format = '2x1'): array
    {
        $units = $product->productUnits()->with('unit')->get();
        $labels = [];

        foreach ($units as $unit) {
            $barcodeData = $unit->barcode ?? $product->barcode;
            if (!$barcodeData) continue;

            $labels[] = [
                'product_name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $barcodeData,
                'unit' => $unit->unit->short_code ?? $unit->unit->name,
                'price' => $unit->selling_price,
                'barcode_svg' => app(BarcodeService::class)->getBarcodeSvg($barcodeData, 2, 40),
            ];
        }

        return [
            'format' => $format,
            'labels' => $labels,
            'columns' => $format === '4x2' ? 4 : 2,
            'rows' => $format === '4x2' ? 2 : 1,
        ];
    }

    public function generateBulkLabels(array $productIds, string $format = '2x1'): array
    {
        $products = Product::whereIn('id', $productIds)->with('productUnits.unit')->get();
        $allLabels = [];

        foreach ($products as $product) {
            $result = $this->generateLabels($product, $format);
            $allLabels = array_merge($allLabels, $result['labels']);
        }

        return [
            'format' => $format,
            'labels' => $allLabels,
            'columns' => $format === '4x2' ? 4 : 2,
            'rows' => $format === '4x2' ? 2 : 1,
        ];
    }
}
