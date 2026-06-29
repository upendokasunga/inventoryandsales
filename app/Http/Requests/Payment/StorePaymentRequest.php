<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Invoice;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()?->hasMenuAccess('payments.store', 'can_create')) {
            return false;
        }

        $invoice = $this->route('invoice');
        if ($invoice instanceof Invoice && $invoice->payment_status === 'paid') {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit,bank_transfer,mobile_money,cheque',
            'reference_number' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
