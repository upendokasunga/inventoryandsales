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
        $query = Product::with('category', 'productUnits.unit', 'variants.productUnits.unit');

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

        if (isset($filters['type']) && $filters['type'] !== '') {
            if ($filters['type'] === 'parent') {
                $query->whereNull('parent_product_id');
            } elseif ($filters['type'] === 'variant') {
                $query->whereNotNull('parent_product_id');
            }
        } else {
            $query->whereNull('parent_product_id');
        }

        return $query->latest()->paginate($perPage);
    }

    public function search(string $query, int $perPage = 20)
    {
        return Product::with('category', 'productUnits.unit', 'variants')
            ->whereNull('parent_product_id')
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

        $data = $this->processVariantAttributes($data);
        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        $product = Product::create($data);

        if (!empty($variants)) {
            $this->syncVariants($product, $variants);
        }

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

        $data = $this->processVariantAttributes($data);
        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        $product->update($data);

        if (isset($data['has_variants']) && $data['has_variants'] && !empty($variants)) {
            $this->syncVariants($product, $variants);
        } elseif (isset($data['has_variants']) && !$data['has_variants']) {
            $product->variants()->delete();
        }

        return $product->fresh();
    }

    protected function processVariantAttributes(array $data): array
    {
        if (isset($data['variant_attributes']) && is_array($data['variant_attributes'])) {
            $attrs = [];
            foreach ($data['variant_attributes'] as $attr) {
                if (!empty($attr['key'])) {
                    $attrs[$attr['key']] = $attr['value'] ?? '';
                }
            }
            $data['variant_attributes'] = $attrs;
        }
        return $data;
    }

    public function generateVariantCombinations(array $attributes): array
    {
        $normalized = [];
        foreach ($attributes as $attr) {
            if (!empty($attr['key']) && !empty($attr['value'])) {
                $values = array_map('trim', explode(',', $attr['value']));
                $normalized[$attr['key']] = $values;
            }
        }

        if (empty($normalized)) {
            return [];
        }

        $keys = array_keys($normalized);
        $combinations = [[]];

        foreach ($keys as $key) {
            $new = [];
            foreach ($combinations as $combo) {
                foreach ($normalized[$key] as $value) {
                    $new[] = array_merge($combo, [$key => $value]);
                }
            }
            $combinations = $new;
        }

        $results = [];
        foreach ($combinations as $combo) {
            $parts = array_values($combo);
            $name = implode(' / ', $parts);
            $results[] = [
                'name' => $name,
                'attributes' => $combo,
            ];
        }

        return $results;
    }

    protected function syncVariants(Product $parent, array $variants): void
    {
        $existingIds = [];
        foreach ($variants as $v) {
            $variantData = [
                'parent_product_id' => $parent->id,
                'category_id' => $parent->category_id,
                'name' => $v['name'] ?? $parent->name,
                'slug' => Str::slug($v['name'] ?? $parent->name),
                'sku' => $v['sku'] ?? $parent->sku . '-' . Str::random(4),
                'barcode' => $v['barcode'] ?? null,
                'description' => $parent->description,
                'tax_rate' => $parent->tax_rate,
                'tax_inclusive' => $parent->tax_inclusive,
                'is_active' => filter_var($v['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'track_stock' => $parent->track_stock,
                'reorder_level' => $parent->reorder_level,
                'has_variants' => false,
                'variant_attributes' => $v['attributes'] ?? null,
            ];

            if (!empty($v['id']) && $variant = Product::find($v['id'])) {
                $variant->update($variantData);
                $existingIds[] = $variant->id;
            } else {
                $variant = Product::create($variantData);
                $existingIds[] = $variant->id;
            }

            if (!empty($v['units']) && !empty($parent->productUnits)) {
                $this->createVariantProductUnits($variant, $v['units'], $parent);
            } else {
                $this->copyParentUnits($variant, $parent, $v['selling_price'] ?? null, $v['purchase_price'] ?? null);
            }
        }

        $parent->variants()->whereNotIn('id', $existingIds)->each(function ($variant) {
            $variant->productUnits()->delete();
            $variant->delete();
        });
    }

    protected function copyParentUnits(Product $variant, Product $parent, ?float $sellingPrice = null, ?float $purchasePrice = null): void
    {
        foreach ($parent->productUnits as $pu) {
            $variant->productUnits()->create([
                'unit_id' => $pu->unit_id,
                'conversion_factor' => $pu->conversion_factor,
                'purchase_price' => $purchasePrice ?? $pu->purchase_price,
                'selling_price' => $sellingPrice ?? $pu->selling_price,
                'wholesale_price' => $pu->wholesale_price,
                'bulk_price' => $pu->bulk_price,
                'is_default_sale' => $pu->is_default_sale,
                'is_default_purchase' => $pu->is_default_purchase,
            ]);
        }
    }

    protected function createVariantProductUnits(Product $variant, array $unitData, Product $parent): void
    {
        foreach ($unitData as $ud) {
            $parentUnit = $parent->productUnits()->where('unit_id', $ud['unit_id'] ?? 0)->first();
            $variant->productUnits()->create([
                'unit_id' => $ud['unit_id'] ?? ($parentUnit->unit_id ?? null),
                'conversion_factor' => $parentUnit->conversion_factor ?? 1,
                'purchase_price' => $ud['purchase_price'] ?? ($parentUnit->purchase_price ?? 0),
                'selling_price' => $ud['selling_price'] ?? ($parentUnit->selling_price ?? 0),
                'wholesale_price' => $parentUnit->wholesale_price ?? 0,
                'bulk_price' => $parentUnit->bulk_price ?? 0,
                'is_default_sale' => $parentUnit->is_default_sale ?? true,
                'is_default_purchase' => $parentUnit->is_default_purchase ?? true,
            ]);
        }
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
