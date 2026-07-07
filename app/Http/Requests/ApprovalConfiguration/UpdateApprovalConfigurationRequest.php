<?php

namespace App\Http\Requests\ApprovalConfiguration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApprovalConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('approval-configurations.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'module_key' => ['required', 'string', 'max:50', Rule::unique('approval_configurations')->ignore($this->route('approval_configuration'))],
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
