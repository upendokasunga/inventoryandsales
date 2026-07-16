<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('warehouses.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses')->ignore($this->route('warehouse'))],
            'type' => 'required|in:goods',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }
}
