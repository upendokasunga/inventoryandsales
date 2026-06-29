<?php

namespace App\Http\Requests\SalesReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('sales-returns.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_unit_id' => 'nullable|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'required|string|in:damaged,wrong_item,expired,customer_dissatisfaction,pricing_error,duplicate_order',
        ];
    }
}
