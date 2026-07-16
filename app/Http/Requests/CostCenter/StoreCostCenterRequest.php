<?php

namespace App\Http\Requests\CostCenter;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('cost-centers.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:cost_centers',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
}
