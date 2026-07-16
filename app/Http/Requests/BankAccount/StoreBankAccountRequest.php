<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('bank-accounts.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'bank_id' => 'required|exists:banks,id',
            'branch' => 'nullable|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'account_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'allow_overdraft' => 'boolean',
            'overdraft_limit' => 'nullable|numeric|min:0',
        ];
    }
}
