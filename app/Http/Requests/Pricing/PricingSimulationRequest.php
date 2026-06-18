<?php

namespace App\Http\Requests\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class PricingSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('price-lists.simulate', 'can_view') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'product_id' => 'required|exists:products,id',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.001',
        ];
    }
}
