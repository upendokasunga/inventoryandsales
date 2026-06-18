<?php

namespace App\Http\Requests\PriceList;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('price-lists.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',

            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:price_list_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.min_quantity' => 'required|numeric|min:0.001',
            'items.*.max_quantity' => 'nullable|numeric|gt:items.*.min_quantity',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
}
