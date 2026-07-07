<?php

namespace App\Http\Requests\JournalEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasMenuAccess('journal-entries.store', 'can_create') ?? false;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'entry_date' => 'required|date',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            foreach ($lines as $index => $line) {
                $hasDebit = isset($line['debit']) && is_numeric($line['debit']) && $line['debit'] > 0;
                $hasCredit = isset($line['credit']) && is_numeric($line['credit']) && $line['credit'] > 0;

                if ($hasDebit && $hasCredit) {
                    $validator->errors()->add(
                        "lines.{$index}.debit",
                        "Each line must have only a debit or a credit, not both."
                    );
                }

                if (!$hasDebit && !$hasCredit) {
                    $validator->errors()->add(
                        "lines.{$index}.debit",
                        "Each line must have either a debit or a credit."
                    );
                }
            }
        });
    }
}
