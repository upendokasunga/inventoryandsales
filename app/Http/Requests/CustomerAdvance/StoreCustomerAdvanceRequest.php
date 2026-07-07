<?php

namespace App\Http\Requests\CustomerAdvance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'reference_number' => 'nullable|string|max:255',
            'advance_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
