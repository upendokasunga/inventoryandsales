<?php

namespace App\Http\Requests\CustomerAdvance;

use Illuminate\Foundation\Http\FormRequest;

class ApplyAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
        ];
    }
}
