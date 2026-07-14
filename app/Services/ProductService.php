<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null)
    {
        $query = Product::with('category', 'productUnits.unit', 'incomeAccount');

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['product_type']) && $filters['product_type'] !== '') {
            $query->where('product_type', $filters['product_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function search(string $query, int $perPage = 20)
    {
        return Product::with('category', 'productUnits.unit', 'incomeAccount')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        $data['slug'] = Str::slug($data['name']);

        $existing = Product::where('name', $data['name'])->first();
        if ($existing) {
            throw new \InvalidArgumentException(
                "A product with the name '{$data['name']}' already exists."
            );
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        unset($data['variants']);

        $product = Product::create($data);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        }

        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $onHandStock = \App\Models\InventoryTransaction::where('product_id', $product->id)
            ->selectRaw('COALESCE(SUM(CASE WHEN type IN (\'in\',\'receive\',\'received\',\'adjust_in\',\'adjustment_in\',\'transfer_in\',\'opening\') THEN quantity ELSE 0 END) - SUM(CASE WHEN type IN (\'out\',\'issue\',\'issued\',\'adjust_out\',\'adjustment_out\',\'transfer_out\',\'consumption\') THEN quantity ELSE 0 END), 0) as balance')
            ->value('balance');

        if ($onHandStock > 0) {
            throw new \InvalidArgumentException(
                "Cannot delete product '{$product->name}'. It still has {$onHandStock} units in stock."
            );
        }

        if ($product->barcode_image) {
            Storage::disk('public')->delete($product->barcode_image);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->productUnits()->delete();
        $product->forceDelete();
    }
}
