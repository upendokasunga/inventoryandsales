<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoNumberSequence extends Model
{
    protected $table = 'po_number_sequence';

    protected $fillable = ['year', 'last_number'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
