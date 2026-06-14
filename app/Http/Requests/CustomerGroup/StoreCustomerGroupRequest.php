<?php

namespace App\Http\Requests\CustomerGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:customer_groups',
            'description' => 'nullable|string|max:500',
            'default_credit_limit' => 'numeric|min:0',
            'default_payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
