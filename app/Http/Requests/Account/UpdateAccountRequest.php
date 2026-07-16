<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('accounts.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('accounts')->ignore($this->route('account'))],
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'category' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'opening_balance' => 'nullable|numeric|min:0',
            'allow_overdraft' => 'boolean',
            'overdraft_limit' => 'nullable|numeric|min:0',
        ];
    }
}
