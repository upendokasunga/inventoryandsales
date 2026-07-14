<?php

namespace App\Http\Requests\CostCenter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('cost-centers.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        $costCenterId = $this->route('costCenter')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('cost_centers')->ignore($costCenterId)],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('cost_centers')->ignore($costCenterId)],
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
}
