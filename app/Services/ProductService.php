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
        $query = Product::with('category', 'productUnits.unit');

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

        return $query->latest()->paginate($perPage);
    }

    public function search(string $query, int $perPage = 20)
    {
        return Product::with('category', 'productUnits.unit')
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

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        return Product::create($data);
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
        if ($product->barcode_image) {
            Storage::disk('public')->delete($product->barcode_image);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->productUnits()->delete();
        $product->delete();
    }

    public function exportCsv()
    {
        $products = Product::with('category', 'productUnits.unit')->get();
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['SKU', 'Barcode', 'Name', 'Category', 'Tax Rate', 'Tax Inclusive', 'Status', 'Track Stock', 'Reorder Level', 'Weight']);

        foreach ($products as $product) {
            fputcsv($output, [
                $product->sku,
                $product->barcode,
                $product->name,
                $product->category?->name,
                $product->tax_rate,
                $product->tax_inclusive ? 'Yes' : 'No',
                $product->is_active ? 'Active' : 'Inactive',
                $product->track_stock ? 'Yes' : 'No',
                $product->reorder_level,
                $product->weight,
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }
}
