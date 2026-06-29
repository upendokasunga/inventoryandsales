<?php

namespace App\Http\Requests\Refund;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\CreditNote;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()?->hasMenuAccess('refunds.process', 'can_create')) {
            return false;
        }

        if ($this->has('credit_note_id')) {
            $creditNote = CreditNote::find($this->credit_note_id);
            if ($creditNote && $creditNote->status !== 'issued') {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'credit_note_id' => 'required|exists:credit_notes,id',
            'refund_method' => 'required|string|in:cash,store_credit,bank_transfer',
        ];
    }
}
