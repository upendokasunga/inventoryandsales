<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('products.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'reorder_level' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'weight' => 'nullable|numeric|min:0',

            'product_type' => 'nullable|in:goods,service,fixed_asset',
            'price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'costing_method' => 'nullable|in:fifo,moving_average,standard',
            'income_account_id' => 'nullable|exists:accounts,id',
            'cost_center' => 'nullable|string|max:255',
            'brand_code' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:255',

            'parent_product_id' => 'nullable|exists:products,id',
            'has_variants' => 'boolean',
            'variant_attributes' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:products,id',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.purchase_price' => 'nullable|numeric|min:0',
            'variants.*.is_active' => 'boolean',

            'units' => 'required|array|min:1',
            'units.*.id' => 'nullable|exists:product_units,id',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.conversion_factor' => 'required|numeric|min:0.001',
            'units.*.purchase_price' => 'nullable|numeric|min:0',
            'units.*.selling_price' => 'nullable|numeric|min:0',
            'units.*.wholesale_price' => 'nullable|numeric|min:0',
            'units.*.bulk_price' => 'nullable|numeric|min:0',
            'units.*.is_default_sale' => 'boolean',
            'units.*.is_default_purchase' => 'boolean',
        ];
    }
}
