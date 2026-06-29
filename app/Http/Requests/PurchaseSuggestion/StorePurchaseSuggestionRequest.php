<?php

namespace App\Http\Requests\PurchaseSuggestion;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('purchasing.suggestions.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'suggested_quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
