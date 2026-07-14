<?php

namespace App\Http\Requests\BankTransaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('bank-transactions.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:255',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
