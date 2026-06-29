<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnNumberSequence extends Model
{
    protected $table = 'return_number_sequence';

    protected $fillable = ['year', 'last_number', 'type'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
