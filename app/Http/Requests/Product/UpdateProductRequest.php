<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
