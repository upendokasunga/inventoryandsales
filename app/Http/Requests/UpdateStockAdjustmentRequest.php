<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('stock-adjustments.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:positive,negative,transfer',
            'reason' => 'required|string|in:damaged,lost,found,expired,recount,other',
            'description' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.inventory_batch_id' => 'nullable|exists:inventory_batches,id',
            'items.*.expected_quantity' => 'required|numeric|min:0',
            'items.*.actual_quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }
}
