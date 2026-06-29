<?php

namespace App\Http\Requests\CustomerGroup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('customer-groups.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        $customerGroup = $this->route('customerGroup');

        return [
            'name' => 'required|string|max:100|unique:customer_groups,name,' . $customerGroup->id,
            'description' => 'nullable|string|max:500',
            'default_credit_limit' => 'numeric|min:0',
            'default_payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
