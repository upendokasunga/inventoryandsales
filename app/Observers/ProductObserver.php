<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\PosService;
use App\Services\SkuService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }

        if (empty($product->sku)) {
            $skuService = app(SkuService::class);
            $category = $product->category;
            $product->sku = $skuService->generate($category);
        }

        if (empty($product->barcode)) {
            $barcodeService = app(BarcodeService::class);
            $product->barcode = $barcodeService->generateNumber();
        }

        if (empty($product->barcode_image) && !empty($product->barcode)) {
            $barcodeService = app(BarcodeService::class);
            $path = storage_path('app/public/barcodes');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $product->barcode_image = $barcodeService->generateBarcodeImage($product->barcode, $path);
        }
    }

    public function updating(Product $product): void
    {
        if ($product->isDirty('name') && empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }

        if ($product->isDirty('barcode')) {
            if ($product->getOriginal('barcode_image')) {
                Storage::disk('public')->delete($product->getOriginal('barcode_image'));
            }

            $barcodeService = app(BarcodeService::class);
            $path = storage_path('app/public/barcodes');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $product->barcode_image = $barcodeService->generateBarcodeImage($product->barcode, $path);

            if ($original = $product->getOriginal('barcode')) {
                app(PosService::class)->invalidateBarcodeCache($original);
            }
            app(PosService::class)->invalidateBarcodeCache($product->barcode);
        }

        if ($product->isDirty('sku')) {
            if ($original = $product->getOriginal('sku')) {
                app(PosService::class)->invalidateSkuCache($original);
            }
            app(PosService::class)->invalidateSkuCache($product->sku);
        }
    }

    public function deleted(Product $product): void
    {
        if ($product->barcode_image) {
            Storage::disk('public')->delete($product->barcode_image);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
