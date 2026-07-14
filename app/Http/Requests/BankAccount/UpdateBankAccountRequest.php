<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('bank-accounts.update', 'can_edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'account_number' => ['required', 'string', 'max:255', Rule::unique('bank_accounts', 'account_number')->ignore($this->bankAccount)],
            'bank_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_type' => 'required|in:checking,savings,fixed_deposit',
            'account_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
