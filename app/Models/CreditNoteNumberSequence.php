<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteNumberSequence extends Model
{
    protected $table = 'credit_note_number_sequence';

    protected $fillable = ['year', 'last_number'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
