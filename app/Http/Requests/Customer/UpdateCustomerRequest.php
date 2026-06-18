<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:200',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|in:' . implode(',', \App\Models\Customer::PAYMENT_TERMS),
            'is_active' => 'boolean',
        ];
    }
}
