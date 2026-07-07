<?php

namespace App\Http\Requests\ApprovalConfiguration;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('approval-configurations.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'module_key' => 'required|string|max:50|unique:approval_configurations',
            'module_name' => 'required|string|max:255',
            'approval_level' => 'required|integer|min:0|max:3',
            'is_active' => 'boolean',
            'levels' => 'required|array|min:1',
            'levels.*.level' => 'required|integer|min:0|max:3',
            'levels.*.name' => 'required|string|max:255',
            'levels.*.group_id' => 'required|exists:groups,id',
            'levels.*.sort_order' => 'required|integer|min:0',
        ];
    }
}
