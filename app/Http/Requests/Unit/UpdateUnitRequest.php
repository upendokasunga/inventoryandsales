<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unit = $this->route('unit');

        return [
            'name' => 'required|string|max:100|unique:units,name,' . $unit->id,
            'short_code' => 'required|string|max:10|unique:units,short_code,' . $unit->id,
            'is_base' => 'boolean',
        ];
    }
}
