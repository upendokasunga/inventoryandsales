<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('accounts.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:accounts',
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
